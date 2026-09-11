<?php

class CaisseCommercialController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCaisse();
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_MANAGE_OWN_CAISSE', 'FINANCE_VIEW_CLOTURES_CAISSE']);
        $this->loadView('../views/caisse_commercial/list.php');
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_MANAGE_OWN_CAISSE', 'FINANCE_VIEW_CLOTURES_CAISSE']);
        $sql = "
            SELECT c.*, u.nom_user, u.prenom_user, val.nom_user as nom_validator, val.prenom_user as prenom_validator
            FROM caisses c
            LEFT JOIN users u ON u.code_user = c.user_code
            LEFT JOIN users val ON val.code_user = c.user_confirm
            WHERE 1=1
        ";
        $params = [];
        $conds = [];
        Context::applyTripleFilter('c', $conds, $params, true);
        if (!empty($conds)) {
            $sql .= " AND " . implode(' AND ', $conds);
        }
        $sql .= " ORDER BY c.date_ouverture DESC, c.id_caisse DESC";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_caisse'];
            $idCrypte = $this->validator->crypter($id);
            $totalDepot = (float)($i['montant_total_depot'] ?? 0);
            $data[] = array_merge($i, [
                'id_cloture' => $id,
                'code_cloture' => $i['code_caisse'],
                'date_cloture' => $i['date_cloture'] ? date('d/m/Y H:i', strtotime($i['date_cloture'])) : date('d/m/Y H:i', strtotime($i['date_ouverture'])),
                'total_especes' => $totalDepot,
                'total_mobile_money' => 0,
                'total_cheque_virement' => 0,
                'total_general' => $totalDepot,
                'statut_cloture' => $i['decission_caisse'] ?: 'attente',
                'editId' => $idCrypte,
                'nom_auteur_complet' => trim(($i['nom_user'] ?? '') . ' ' . ($i['prenom_user'] ?? '')),
                'nom_validator_complet' => trim(($i['nom_validator'] ?? '') . ' ' . ($i['prenom_validator'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getDailyTotals()
    {
        $this->requirePermission(['COMMERCIAL_MANAGE_OWN_CAISSE', 'FINANCE_VIEW_CLOTURES_CAISSE']);
        $date = $_GET['date'] ?? ($_POST['date'] ?? date('Y-m-d'));
        $db = $this->model->getCon();

        $stmt = $db->prepare("
            SELECT mode_paiement, SUM(montant_paiement) as sum_mode, COUNT(*) as count_mode
            FROM paiements
            WHERE DATE(date_paiement) = ? AND statut_paiement != 'annule'
            GROUP BY mode_paiement
        ");
        $stmt->execute([$date]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalEspeces = 0;
        $totalMobileMoney = 0;
        $totalChequeVirement = 0;
        $nbEncaissements = 0;

        foreach ($rows as $r) {
            $mode = strtolower($r['mode_paiement'] ?? '');
            $sum = (float)($r['sum_mode'] ?? 0);
            $cnt = (int)($r['count_mode'] ?? 0);

            $nbEncaissements += $cnt;

            if ($mode === 'espece' || $mode === 'especes') {
                $totalEspeces += $sum;
            } elseif ($mode === 'mobile_money' || $mode === 'wave' || $mode === 'orange' || $mode === 'mtn' || $mode === 'moov') {
                $totalMobileMoney += $sum;
            } else {
                $totalChequeVirement += $sum;
            }
        }

        $totalGeneral = $totalEspeces + $totalMobileMoney + $totalChequeVirement;

        $sqlCheck = "SELECT * FROM caisses WHERE DATE(date_ouverture) = ? AND statut_caisse = 'cloture'";
        $pCheck = [$date];
        $cCheck = [];
        Context::applyTripleFilter('', $cCheck, $pCheck, false);
        if (!empty($cCheck)) $sqlCheck .= " AND " . implode(' AND ', $cCheck);
        $sqlCheck .= " LIMIT 1";
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute($pCheck);
        $alreadyClosed = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        $this->json([
            'status' => 1,
            'data' => [
                'date' => $date,
                'is_already_closed' => !empty($alreadyClosed),
                'existing_code' => $alreadyClosed['code_caisse'] ?? null,
                'total_especes' => $totalEspeces,
                'total_mobile_money' => $totalMobileMoney,
                'total_cheque_virement' => $totalChequeVirement,
                'total_general' => $totalGeneral,
                'nb_encaissements' => $nbEncaissements,
                'total_especes_fmt' => number_format($totalEspeces, 0, ',', ' ') . ' FCFA',
                'total_mobile_money_fmt' => number_format($totalMobileMoney, 0, ',', ' ') . ' FCFA',
                'total_cheque_virement_fmt' => number_format($totalChequeVirement, 0, ',', ' ') . ' FCFA',
                'total_general_fmt' => number_format($totalGeneral, 0, ',', ' ') . ' FCFA'
            ]
        ]);
    }

    public function apiGetCommercialSession()
    {
        $this->requirePermission('COMMERCIAL_MANAGE_OWN_CAISSE');
        $userCode = Context::user() ?? '';
        $dateToday = date('Y-m-d');
        $db = $this->model->getCon();

        // 1. Chercher s'il y a une caisse OUVERTE aujourd'hui
        $sqlOuv = "
            SELECT * FROM caisses 
            WHERE user_code = ? AND DATE(date_ouverture) = ? AND statut_caisse = 'ouverte'
        ";
        $pOuv = [$userCode, $dateToday];
        $cOuv = [];
        Context::applyTripleFilter('', $cOuv, $pOuv, false);
        if (!empty($cOuv)) $sqlOuv .= " AND " . implode(' AND ', $cOuv);
        $sqlOuv .= " ORDER BY id_caisse DESC LIMIT 1";
        $stmtOuv = $db->prepare($sqlOuv);
        $stmtOuv->execute($pOuv);
        $activeSession = $stmtOuv->fetch(PDO::FETCH_ASSOC);

        if ($activeSession) {
            $codeCaisse = $activeSession['code_caisse'];

            // Calculer les totaux réels des cotisations rattachées à cette caisse ou faites aujourd'hui
            $sqlCotis = "
                SELECT c.*, cli.nom_client, cli.telephone_client
                FROM cautisation_clients c
                LEFT JOIN clients cli ON cli.code_client = c.client_code
                WHERE (c.commercial_code = ? OR c.user_code = ?) 
                  AND (c.caisse_code = ? OR ((c.caisse_code IS NULL OR c.caisse_code = '') AND DATE(c.date_cautisation) = ?))
                  AND c.statut_cautisation_client != 'annule'
            ";
            $pCot = [$userCode, $userCode, $codeCaisse, $dateToday];
            $cCot = [];
            Context::applyTripleFilter('c', $cCot, $pCot, false);
            if (!empty($cCot)) $sqlCotis .= " AND " . implode(' AND ', $cCot);
            $sqlCotis .= " ORDER BY c.date_cautisation DESC";
            $stmtCotis = $db->prepare($sqlCotis);
            $stmtCotis->execute($pCot);
            $cotisations = $stmtCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalEspeces = 0;
            $totalMobileMoney = 0;
            $totalChequeVirement = 0;
            $cotisationList = [];

            foreach ($cotisations as $cot) {
                $m = (float)($cot['montant_cautisation_client'] ?? 0);
                $mode = strtolower($cot['mode_paiement'] ?? 'espece');
                if (in_array($mode, ['espece', 'especes', 'cash'])) {
                    $totalEspeces += $m;
                } elseif (in_array($mode, ['mobile_money', 'wave', 'orange', 'mtn', 'moov'])) {
                    $totalMobileMoney += $m;
                } else {
                    $totalChequeVirement += $m;
                }

                $nbJ = (int)($cot['nombre_jour'] ?? 1);
                $dateCot = $cot['date_cautisation'];
                $prochainRdv = date('Y-m-d', strtotime($dateCot . " + $nbJ days"));

                $cotisationList[] = [
                    'code_cautisation' => $cot['code_cautisation_client'],
                    'code_souscription' => $cot['souscription_code'],
                    'nom_client' => $cot['nom_client'] ?? 'Client Inconnu',
                    'telephone_client' => $cot['telephone_client'] ?? '-',
                    'montant' => $m,
                    'montant_fmt' => number_format($m, 0, ',', ' ') . ' FCFA',
                    'mode_paiement' => strtoupper($cot['mode_paiement'] ?? 'ESPECES'),
                    'date_cautisation' => date('d/m/Y H:i', strtotime($dateCot)),
                    'date_prochain_rdv' => date('d/m/Y', strtotime($prochainRdv)),
                    'statut' => $cot['statut_cautisation_client'] ?? 'en_attente'
                ];
            }

            $totalGeneral = $totalEspeces + $totalMobileMoney + $totalChequeVirement;
            $fondInitial = (float)($activeSession['montant_total_attendu'] ?? 0);

            $this->json([
                'status' => 1,
                'has_active_session' => true,
                'session' => [
                    'code_caisse' => $codeCaisse,
                    'date_caisse' => date('d/m/Y', strtotime($activeSession['date_ouverture'])),
                    'heure_ouverture' => date('H:i', strtotime($activeSession['date_ouverture'])),
                    'fond_initial' => $fondInitial,
                    'fond_initial_fmt' => number_format($fondInitial, 0, ',', ' ') . ' FCFA',
                    'total_especes' => $totalEspeces,
                    'total_mobile_money' => $totalMobileMoney,
                    'total_cheque_virement' => $totalChequeVirement,
                    'total_general' => $totalGeneral,
                    'total_general_fmt' => number_format($totalGeneral, 0, ',', ' ') . ' FCFA',
                    'nb_cotisations' => count($cotisations),
                    'cotisations' => $cotisationList
                ]
            ]);
            return;
        }

        // Aucune session active aujourd'hui : Récupérer le bilan de la dernière clôture
        $sqlLast = "
            SELECT c.*, val.nom_user as nom_validator, val.prenom_user as prenom_validator
            FROM caisses c
            LEFT JOIN users val ON val.code_user = c.user_confirm
            WHERE c.user_code = ? AND c.statut_caisse = 'cloture'
        ";
        $pLast = [$userCode];
        $cLast = [];
        Context::applyTripleFilter('c', $cLast, $pLast, false);
        if (!empty($cLast)) $sqlLast .= " AND " . implode(' AND ', $cLast);
        $sqlLast .= " ORDER BY c.date_cloture DESC, c.id_caisse DESC LIMIT 1";
        $stmtLast = $db->prepare($sqlLast);
        $stmtLast->execute($pLast);
        $lastCloture = $stmtLast->fetch(PDO::FETCH_ASSOC);

        $lastCotisations = [];
        if ($lastCloture) {
            $dateLast = $lastCloture['date_cloture'] ? date('Y-m-d', strtotime($lastCloture['date_cloture'])) : date('Y-m-d', strtotime($lastCloture['date_ouverture']));
            $sqlCotis = "
                SELECT c.*, cli.nom_client, cli.telephone_client
                FROM cautisation_clients c
                LEFT JOIN clients cli ON cli.code_client = c.client_code
                WHERE (c.commercial_code = ? OR c.user_code = ?) 
                  AND (c.caisse_code = ? OR ((c.caisse_code IS NULL OR c.caisse_code = '') AND DATE(c.date_cautisation) = ?))
                  AND c.statut_cautisation_client != 'annule'
            ";
            $pCotis = [$userCode, $userCode, $lastCloture['code_caisse'] ?? '', $dateLast];
            $cCotis = [];
            Context::applyTripleFilter('c', $cCotis, $pCotis, false);
            if (!empty($cCotis)) $sqlCotis .= " AND " . implode(' AND ', $cCotis);
            $sqlCotis .= " ORDER BY c.date_cautisation DESC";
            $stmtCotis = $db->prepare($sqlCotis);
            $stmtCotis->execute($pCotis);
            $rawCotis = $stmtCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];
            foreach ($rawCotis as $cot) {
                $m = (float)($cot['montant_cautisation_client'] ?? 0);
                $nbJ = (int)($cot['nombre_jour'] ?? 1);
                $dateCot = $cot['date_cautisation'];
                $prochainRdv = date('Y-m-d', strtotime($dateCot . " + $nbJ days"));
                $lastCotisations[] = [
                    'code_cautisation' => $cot['code_cautisation_client'],
                    'code_souscription' => $cot['souscription_code'],
                    'nom_client' => $cot['nom_client'] ?? 'Client Inconnu',
                    'telephone_client' => $cot['telephone_client'] ?? '-',
                    'montant' => $m,
                    'montant_fmt' => number_format($m, 0, ',', ' ') . ' FCFA',
                    'mode_paiement' => strtoupper($cot['mode_paiement'] ?? 'ESPECES'),
                    'date_cautisation' => date('d/m/Y H:i', strtotime($dateCot)),
                    'date_prochain_rdv' => date('d/m/Y', strtotime($prochainRdv)),
                    'statut' => $cot['statut_cautisation_client'] ?? 'en_attente'
                ];
            }
        }

        $totalLast = (float)($lastCloture['montant_total_depot'] ?? 0);

        $this->json([
            'status' => 1,
            'has_active_session' => false,
            'last_cloture' => $lastCloture ? [
                'code_cloture' => $lastCloture['code_caisse'],
                'date_cloture' => date('d/m/Y', strtotime($lastCloture['date_cloture'] ?? $lastCloture['date_ouverture'])),
                'total_general' => $totalLast,
                'total_general_fmt' => number_format($totalLast, 0, ',', ' ') . ' FCFA',
                'statut_cloture' => $lastCloture['decission_caisse'] ?? 'attente',
                'validator_nom' => trim(($lastCloture['nom_validator'] ?? '') . ' ' . ($lastCloture['prenom_validator'] ?? '')),
                'cotisations' => $lastCotisations
            ] : null
        ]);
    }

    public function ouvrirMaCaisse()
    {
        $this->requirePost(false);
        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();

        // Priorité : annee_code soumis > Context::annee()
        $anneeCode = !empty($this->post('annee_code')) ? trim($this->post('annee_code')) : Context::annee();

        if (empty($anneeCode)) {
            $this->error("L'année d'exercice est obligatoire pour ouvrir une caisse. Veuillez configurer une année active.");
            return;
        }

        // Vérification dans annees
        $stmtAnneeCheck = $this->model->getCon()->prepare("SELECT code_annee FROM annees WHERE code_annee = ? LIMIT 1");
        $stmtAnneeCheck->execute([$anneeCode]);
        if (!$stmtAnneeCheck->fetch()) {
            $this->error("L'année d'activité associée à la caisse est invalide ou introuvable.");
            return;
        }

        if (empty($userCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $dateToday = date('Y-m-d');
        $fondInitial = (float)($this->post('fond_initial') ?? 0);
        $db = $this->model->getCon();

        // Vérifier s'il y a déjà une caisse ouverte aujourd'hui
        $sqlCheck = "SELECT id_caisse FROM caisses WHERE user_code = ? AND DATE(date_ouverture) = ? AND statut_caisse = 'ouverte'";
        $pCheck = [$userCode, $dateToday];
        $cCheck = [];
        Context::applyTripleFilter('', $cCheck, $pCheck, false);
        if (!empty($cCheck)) $sqlCheck .= " AND " . implode(' AND ', $cCheck);
        $stmtCheck = $db->prepare($sqlCheck);
        $stmtCheck->execute($pCheck);
        if ($stmtCheck->fetch()) {
            $this->error('Vous avez déjà une caisse OUVERTE aujourd\'hui !');
            return;
        }

        $codeCaisse = $this->validator->generateCode('caisses', 'code_caisse', 'CAISSE-', 8);
        $data = [
            'code_caisse' => $codeCaisse,
            'date_ouverture' => date('Y-m-d H:i:s'),
            'montant_total_attendu' => $fondInitial,
            'montant_total_depot' => 0,
            'decission_caisse' => 'attente',
            'statut_caisse' => 'ouverte',
            'user_code' => $userCode,
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'zone_code' => Context::zone()
        ];

        $modelOuv = new ModelCaisse();
        if ($modelOuv->create($data)) {
            $this->success('Votre caisse du jour est maintenant OUVERTE ! Vous pouvez démarrer vos encaissements.', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'ouverture de caisse.');
        }
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_MANAGE_OWN_CAISSE');
        $userCode = Context::user() ?? '';
        $db = $this->model->getCon();

        // Chercher la caisse ouverte active pour l'utilisateur
        $sqlOuv = "SELECT * FROM caisses WHERE user_code = ? AND statut_caisse = 'ouverte'";
        $pOuv = [$userCode];
        $cOuv = [];
        Context::applyTripleFilter('', $cOuv, $pOuv, false);
        if (!empty($cOuv)) $sqlOuv .= " AND " . implode(' AND ', $cOuv);
        $sqlOuv .= " ORDER BY id_caisse DESC LIMIT 1";
        $stmtOuv = $db->prepare($sqlOuv);
        $stmtOuv->execute($pOuv);
        $activeCaisse = $stmtOuv->fetch(PDO::FETCH_ASSOC);

        if (!$activeCaisse) {
            $this->error("Aucune caisse ouverte à clôturer.");
            return;
        }

        $codeCaisse = $activeCaisse['code_caisse'];
        $dateOpening = date('Y-m-d', strtotime($activeCaisse['date_ouverture']));

        // Calculer les totaux réels des encaissements
        $sqlCot = "
            SELECT c.* 
            FROM cautisation_clients c
            WHERE (c.commercial_code = ? OR c.user_code = ?) 
              AND (c.caisse_code = ? OR ((c.caisse_code IS NULL OR c.caisse_code = '') AND DATE(c.date_cautisation) = ?))
              AND c.statut_cautisation_client != 'annule'
        ";
        $pCot = [$userCode, $userCode, $codeCaisse, $dateOpening];
        $cCot = [];
        Context::applyTripleFilter('c', $cCot, $pCot, false);
        if (!empty($cCot)) $sqlCot .= " AND " . implode(' AND ', $cCot);
        $stmtCotis = $db->prepare($sqlCot);
        $stmtCotis->execute($pCot);
        $cotisations = $stmtCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalGeneral = 0;
        foreach ($cotisations as $cot) {
            $totalGeneral += (float)($cot['montant_cautisation_client'] ?? 0);
        }

        // Vérification du Montant du Pot saisi par le commercial
        $valPot = $_POST['montant_pot'] ?? null;
        if ($valPot === null || $valPot === '') {
            $this->error("Veuillez renseigner le montant physique de votre pot.");
            return;
        }

        $montantPot = (float)$valPot;

        // RÈGLE STRICTE : Blocage absolu en cas d'écart de caisse
        if (abs($montantPot - $totalGeneral) > 0.01) {
            $ecart = $montantPot - $totalGeneral;
            $ecartFmt = number_format(abs($ecart), 0, ',', ' ') . ' FCFA';
            $typeEcart = ($ecart < 0) ? "un manquant" : "un excédent";
            $this->error("Clôture strictement bloquée : $typeEcart de $ecartFmt a été constaté. Le montant du pot déclaré (" . number_format($montantPot, 0, ',', ' ') . " FCFA) doit être exactement égal au montant des encaissements enregistrés (" . number_format($totalGeneral, 0, ',', ' ') . " FCFA).");
            return;
        }

        $observations = trim($_POST['observations'] ?? '');

        $updateData = [
            'date_cloture' => date('Y-m-d H:i:s'),
            'montant_total_attendu' => $totalGeneral,
            'montant_total_depot' => $montantPot,
            'statut_caisse' => 'cloture',
            'decission_caisse' => 'attente'
        ];

        if ($this->model->update($updateData, (int)$activeCaisse['id_caisse'])) {
            // SYNCHRONISATION AUTOMATIQUE AVEC VERSEMENTS_COMMERCIAUX
            $etabCode = !empty($activeCaisse['etablissement_code']) ? $activeCaisse['etablissement_code'] : Context::etablissement();
            $zoneCode = !empty($activeCaisse['zone_code']) ? $activeCaisse['zone_code'] : Context::zone();
            $anneeCode = !empty($activeCaisse['annee_code']) ? $activeCaisse['annee_code'] : Context::annee();
            $userCode = Context::user();
            $dateToday = date('Y-m-d');

            if (empty($etabCode) || empty($zoneCode) || empty($anneeCode) || empty($userCode)) {
                $this->error("Erreur de transmission du versement : L'établissement, la zone commerciale, l'année d'exercice et l'utilisateur sont obligatoires.");
                return;
            }

            $stmtVCheck = $db->prepare("SELECT id_versement, code_versement_commercial FROM versements_commerciaux WHERE caisse_code = ? LIMIT 1");
            $stmtVCheck->execute([$codeCaisse]);
            $existingV = $stmtVCheck->fetch(PDO::FETCH_ASSOC);

            if ($existingV) {
                $codeVersement = $existingV['code_versement_commercial'];
                $stmtUpV = $db->prepare("
                    UPDATE versements_commerciaux 
                    SET montant_versement = ?, statut_versement = 'En attente', periode_versement = ?, commentaire_validation = ?
                    WHERE id_versement = ?
                ");
                $stmtUpV->execute([(int)$montantPot, $dateToday, $observations, $existingV['id_versement']]);
            } else {
                $codeVersement = $this->validator->generateCode('versements_commerciaux', 'code_versement_commercial', 'VRS-', 8);
                $stmtInsV = $db->prepare("
                    INSERT INTO versements_commerciaux (
                        code_versement_commercial, caisse_code, montant_versement, commercial_code,
                        periode_versement, statut_versement, etablissement_code, user_code,
                        created_at_versement, zone_code, user_validate, date_validation, commentaire_validation, annee_code
                    ) VALUES (
                        ?, ?, ?, ?,
                        ?, 'En attente', ?, ?,
                        NOW(), ?, '', '1000-01-01 00:00:00', ?, ?
                    )
                ");
                $stmtInsV->execute([
                    $codeVersement, $codeCaisse, (int)$montantPot, $userCode,
                    $dateToday, $etabCode, $userCode,
                    $zoneCode, $observations, $anneeCode
                ]);
            }

            // Notification pour le service Finance / Comptabilité
            try {
                $stmtComm = $db->prepare("SELECT nom_user, prenom_user FROM users WHERE code_user = ?");
                $stmtComm->execute([$userCode]);
                $commercial = $stmtComm->fetch(PDO::FETCH_ASSOC);
                $commNom = $commercial ? trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? '')) : 'Agent Commercial';

                NotificationService::notifyVersementSoumis([
                    'reference_code'     => $codeVersement,
                    'montant'            => (float)$montantPot,
                    'commercial_nom'     => $commNom,
                    'etablissement_code' => $etabCode,
                    'zone_code'          => $zoneCode,
                    'annee_code'         => $anneeCode
                ]);
            } catch (\Throwable $ne) {
                error_log('[CaisseCommercialController] Notification error on submit: ' . $ne->getMessage());
            }

            $this->success('Clôture de caisse et versement de ' . number_format($montantPot, 0, ',', ' ') . ' FCFA transmis avec succès à la comptabilité pour validation !', [
                'reload' => true,
                'code_versement' => $codeVersement
            ]);
        } else {
            $this->error('Erreur lors de l\'enregistrement de la clôture de caisse.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_CLOTURES_CAISSE');
        $id = (int)$this->post('id_caisse');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE caisses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_CLOTURES_CAISSE');
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['attente', 'valide', 'rejete'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $userCode = $_SESSION[USERS_AUTH]['code_user'] ?? null;
                $extraData = [
                    'decission_caisse' => $statut,
                    'date_validation' => date('Y-m-d H:i:s'),
                    'user_confirm' => $userCode,
                    'date_confirm' => date('Y-m-d H:i:s')
                ];
                $success = $this->model->update($extraData, $id);
            } else {
                $success = false;
            }
            if ($success) {
                $caisseItem = $this->model->getById($id);
                if ($caisseItem) {
                    $db = $this->model->getCon();
                    $versStatut = ($statut === 'valide') ? 'valide' : (($statut === 'rejete') ? 'annule' : 'En attente');
                    $stmtV = $db->prepare("
                        UPDATE versements_commerciaux 
                        SET statut_versement = ?, user_validate = ?, date_validation = NOW()
                        WHERE caisse_code = ?
                    ");
                    $stmtV->execute([$versStatut, $userCode, $caisseItem['code_caisse']]);

                    // Si validée, basculer les cotisations associées de cette caisse
                    if ($statut === 'valide') {
                        $stmtCot = $db->prepare("
                            UPDATE cautisation_clients 
                            SET statut_cautisation_client = 'valide', updated_at_cautisation_client = NOW()
                            WHERE caisse_code = ? AND statut_cautisation_client = 'en_attente'
                        ");
                        $stmtCot->execute([$caisseItem['code_caisse']]);
                    }
                }
                $this->success('Statut de la caisse et du versement mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Caisse introuvable');
        }
    }

    public function details($param)
    {
        $this->requirePermission(['COMMERCIAL_MANAGE_OWN_CAISSE', 'FINANCE_VIEW_CLOTURES_CAISSE']);
        try {
            $id = null;
            try {
                $id = $this->validator->decrypter($param);
            } catch (Exception $e) {
                $id = null;
            }

            $db = $this->model->getCon();
            $item = null;

            if ($id) {
                $sqlCaisse = "
                    SELECT c.*, u.nom_user, u.prenom_user
                    FROM caisses c
                    LEFT JOIN users u ON u.code_user = c.user_code
                    WHERE c.id_caisse = ?
                ";
                $pCaisse = [$id];
                $cCaisse = [];
                Context::applyTripleFilter('c', $cCaisse, $pCaisse, false);
                if (!empty($cCaisse)) $sqlCaisse .= " AND " . implode(' AND ', $cCaisse);
                $stmt = $db->prepare($sqlCaisse);
                $stmt->execute($pCaisse);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

                // Si non trouvé par id_caisse, vérifier si $id est l'id_versement dans versements_commerciaux
                if (!$item) {
                    $stmtV = $db->prepare("SELECT caisse_code FROM versements_commerciaux WHERE id_versement = ?");
                    $stmtV->execute([$id]);
                    $vItem = $stmtV->fetch(PDO::FETCH_ASSOC);
                    if ($vItem && !empty($vItem['caisse_code'])) {
                        $sqlCaisse = "
                            SELECT c.*, u.nom_user, u.prenom_user
                            FROM caisses c
                            LEFT JOIN users u ON u.code_user = c.user_code
                            WHERE c.code_caisse = ?
                        ";
                        $pCaisse = [$vItem['caisse_code']];
                        $cCaisse = [];
                        Context::applyTripleFilter('c', $cCaisse, $pCaisse, false);
                        if (!empty($cCaisse)) $sqlCaisse .= " AND " . implode(' AND ', $cCaisse);
                        $stmt = $db->prepare($sqlCaisse);
                        $stmt->execute($pCaisse);
                        $item = $stmt->fetch(PDO::FETCH_ASSOC);
                    }
                }
            }

            if (!$item && $param) {
                $sqlCaisse = "
                    SELECT c.*, u.nom_user, u.prenom_user
                    FROM caisses c
                    LEFT JOIN users u ON u.code_user = c.user_code
                    WHERE c.code_caisse = ?
                ";
                $pCaisse = [$param];
                $cCaisse = [];
                Context::applyTripleFilter('c', $cCaisse, $pCaisse, false);
                if (!empty($cCaisse)) $sqlCaisse .= " AND " . implode(' AND ', $cCaisse);
                $stmt = $db->prepare($sqlCaisse);
                $stmt->execute($pCaisse);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
            }

            if (!$item) {
                $this->renderNotFound("Le procès-verbal de clôture de caisse demandé est introuvable.");
                return;
            }

            $encryptedId = $this->validator->crypter($item['id_caisse']);
            
            $sqlP = "
                SELECT cc.*, cl.nom_client, cl.telephone_client
                FROM cautisation_clients cc
                LEFT JOIN clients cl ON cl.code_client = cc.client_code
                WHERE (cc.caisse_code = ? OR ((cc.caisse_code IS NULL OR cc.caisse_code = '') AND DATE(cc.date_cautisation) = DATE(?)))
            ";
            $pP = [$item['code_caisse'] ?? '', $item['date_ouverture'] ?? ''];
            $cP = [];
            Context::applyTripleFilter('cc', $cP, $pP, false);
            if (!empty($cP)) $sqlP .= " AND " . implode(' AND ', $cP);
            $sqlP .= " ORDER BY cc.date_cautisation DESC";
            $stmtP = $this->model->getCon()->prepare($sqlP);
            $stmtP->execute($pP);
            $paiements = $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $totalEspeces = 0;
            $totalMobileMoney = 0;
            $totalChequeVirement = 0;
            foreach ($paiements as $p) {
                $m = (float)($p['montant_cautisation_client'] ?? 0);
                $mode = strtolower(trim($p['mode_paiement'] ?? 'espece'));
                if (in_array($mode, ['espece', 'especes', 'cash'])) {
                    $totalEspeces += $m;
                } elseif (in_array($mode, ['mobile_money', 'wave', 'orange', 'mtn', 'moov'])) {
                    $totalMobileMoney += $m;
                } else {
                    $totalChequeVirement += $m;
                }
            }
            $item['total_especes'] = $totalEspeces;
            $item['total_mobile_money'] = $totalMobileMoney;
            $item['total_cheque_virement'] = $totalChequeVirement;
            $item['total_general'] = (float)($item['montant_total_depot'] ?? 0);
            if ($item['total_general'] <= 0) {
                $item['total_general'] = $totalEspeces + $totalMobileMoney + $totalChequeVirement;
            }
            $item['fond_initial'] = (float)($item['montant_total_attendu'] ?? 0);

            $stmtV = $db->prepare("
                SELECT id_versement, code_versement_commercial, montant_versement, statut_versement, periode_versement, caisse_code
                FROM versements_commerciaux
                WHERE caisse_code = ? OR (commercial_code = ? AND periode_versement = DATE(?))
                ORDER BY id_versement DESC
                LIMIT 1
            ");
            $stmtV->execute([
                $item['code_caisse'] ?? '',
                $item['user_code'] ?? '',
                $item['date_ouverture'] ?? date('Y-m-d')
            ]);
            $versementLinked = $stmtV->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("CaisseCommercialController::details error: " . $e->getMessage());
            $this->renderNotFound("Le procès-verbal de clôture de caisse demandé est introuvable.");
            return;
        }
        $this->loadView('../views/caisse_commercial/details.php', [
            'item' => $item, 
            'paiements' => $paiements,
            'encryptedId' => $encryptedId,
            'versementLinked' => $versementLinked
        ]);
    }

    public function edition($param)
    {
        $this->requirePermission('FINANCE_MANAGE_CLOTURES_CAISSE');
        try {
            $id = $this->validator->decrypter($param);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'caisse_commercial/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'caisse_commercial/list'); exit();
        }
        $this->loadView('../views/caisse_commercial/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('COMMERCIAL_MANAGE_OWN_CAISSE');
        $this->loadView('../views/caisse_commercial/edit.php', ['item' => []]);
    }
}
