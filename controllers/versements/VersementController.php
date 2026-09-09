<?php

class VersementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelVersement();
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $this->loadView('../views/versements/list.php');
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT v.*, c.id_caisse,
                   uc.nom_user as nom_commercial, uc.prenom_user as prenom_commercial,
                   uv.nom_user as nom_validator, uv.prenom_user as prenom_validator,
                   z.libelle_zone
            FROM versements_commerciaux v
            LEFT JOIN caisses c ON c.code_caisse = v.caisse_code
            LEFT JOIN users uc ON uc.code_user = v.commercial_code
            LEFT JOIN users uv ON uv.code_user = v.user_validate
            LEFT JOIN zones z ON z.code_zone = v.zone_code
            WHERE v.etablissement_code = ? AND v.zone_code = ? AND v.annee_code = ?
        ";
        $params = [$etabCode, $zoneCode, $anneeCode];

        // RÈGLE RBAC : Le commercial ne voit que ses propres versements
        if (Context::isCommercial()) {
            $sql .= " AND (v.commercial_code = ? OR v.user_code = ?)";
            $params[] = Context::user();
            $params[] = Context::user();
        }

        $sql .= " ORDER BY v.created_at_versement DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        foreach ($items as $v) {
            $id = $v['id_versement'];
            $idCrypte = $this->validator->crypter($id);
            $caisseId = $v['id_caisse'] ?? null;
            $caisseIdCrypte = $caisseId ? $this->validator->crypter($caisseId) : $idCrypte;

            $data[] = array_merge($v, [
                'id' => $id,
                'editId' => $idCrypte,
                'caisseIdCrypte' => $caisseIdCrypte,
                'nom_commercial_complet' => trim(($v['nom_commercial'] ?? '') . ' ' . ($v['prenom_commercial'] ?? '')),
                'nom_validator_complet' => trim(($v['nom_validator'] ?? '') . ' ' . ($v['prenom_validator'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function apiCommercialCaisseHistory()
    {
        $this->requirePermission(['FINANCE_VALIDATE_VERSEMENT', 'COMMERCIAL_MAKE_VERSEMENT']);
        $idVersement = (int)($this->get('id_versement') ?? $this->post('id_versement'));
        if (!$idVersement) {
            $this->error('Identifiant versement requis');
            return;
        }

        $versement = $this->model->getById($idVersement);
        if (!$versement) {
            $this->error('Versement introuvable');
            return;
        }

        $commCode = $versement['commercial_code'];
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();
        $db = $this->model->getCon();

        // 1. Stats des cotisations du commercial
        $stmtC = $db->prepare("
            SELECT 
                SUM(montant_cautisation_client) as total_collecte,
                SUM(CASE WHEN LOWER(mode_paiement) IN ('espece', 'especes', 'cash') THEN montant_cautisation_client ELSE 0 END) as total_especes,
                SUM(CASE WHEN LOWER(mode_paiement) IN ('mobile_money', 'wave', 'orange', 'mtn', 'moov') THEN montant_cautisation_client ELSE 0 END) as total_momo,
                SUM(CASE WHEN LOWER(mode_paiement) NOT IN ('espece', 'especes', 'cash', 'mobile_money', 'wave', 'orange', 'mtn', 'moov') THEN montant_cautisation_client ELSE 0 END) as total_autre,
                SUM(CASE WHEN statut_cautisation_client = 'valide' THEN montant_cautisation_client ELSE 0 END) as total_valide,
                SUM(CASE WHEN statut_cautisation_client = 'en_attente' THEN montant_cautisation_client ELSE 0 END) as total_attente,
                COUNT(*) as total_nb_cotis
            FROM cautisation_clients
            WHERE (commercial_code = ? OR user_code = ?) 
              AND statut_cautisation_client != 'annule'
              AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtC->execute([$commCode, $commCode, $etabCode, $zoneCode, $anneeCode]);
        $cotisStats = $stmtC->fetch(PDO::FETCH_ASSOC) ?: [];

        // 2. Stats des versements du commercial
        $stmtVStats = $db->prepare("
            SELECT 
                SUM(CASE WHEN statut_versement = 'valide' THEN montant_versement ELSE 0 END) as total_versements_valides,
                SUM(CASE WHEN statut_versement = 'En attente' OR statut_versement = 'attente' THEN montant_versement ELSE 0 END) as total_versements_attente,
                COUNT(*) as nb_versements
            FROM versements_commerciaux
            WHERE commercial_code = ?
              AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtVStats->execute([$commCode, $etabCode, $zoneCode, $anneeCode]);
        $versStats = $stmtVStats->fetch(PDO::FETCH_ASSOC) ?: [];

        // 3. Sessions de caisse récentes (5 dernières)
        $stmtSessions = $db->prepare("
            SELECT code_caisse, date_ouverture, date_cloture, montant_total_depot, montant_total_attendu, statut_caisse, decission_caisse
            FROM caisses
            WHERE user_code = ?
              AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
            ORDER BY date_ouverture DESC, id_caisse DESC
            LIMIT 5
        ");
        $stmtSessions->execute([$commCode, $etabCode, $zoneCode, $anneeCode]);
        $sessionsList = $stmtSessions->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // 4. Cotisations récentes (10 plus récentes)
        $stmtRecentCotis = $db->prepare("
            SELECT c.code_cautisation_client, c.montant_cautisation_client, c.mode_paiement, c.statut_cautisation_client, c.date_cautisation, cli.nom_client
            FROM cautisation_clients c
            LEFT JOIN clients cli ON cli.code_client = c.client_code
            WHERE (c.commercial_code = ? OR c.user_code = ?)
              AND c.etablissement_code = ? AND c.zone_code = ? AND c.annee_code = ?
            ORDER BY c.date_cautisation DESC, c.id_cautisation_client DESC
            LIMIT 10
        ");
        $stmtRecentCotis->execute([$commCode, $commCode, $etabCode, $zoneCode, $anneeCode]);
        $recentCotis = $stmtRecentCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalCollecte = (float)($cotisStats['total_collecte'] ?? 0);
        $totalVersementsValides = (float)($versStats['total_versements_valides'] ?? 0);
        $soldeResteAVerser = max(0, $totalCollecte - $totalVersementsValides);
        $montantVersement = (float)$versement['montant_versement'];
        $ecartComparaison = $montantVersement - $soldeResteAVerser;

        // Informations spécifiques sur la caisse liée à ce versement
        $caisseCode = $versement['caisse_code'] ?? null;
        $caisseDetails = null;
        $caisseCotisations = [];
        $totalCaisseAttendu = 0;

        if (!empty($caisseCode)) {
            $stmtCaisse = $db->prepare("
                SELECT * FROM caisses 
                WHERE code_caisse = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                LIMIT 1
            ");
            $stmtCaisse->execute([$caisseCode, $etabCode, $zoneCode, $anneeCode]);
            $caisseDetails = $stmtCaisse->fetch(PDO::FETCH_ASSOC);

            $stmtCCotis = $db->prepare("
                SELECT c.code_cautisation_client, c.montant_cautisation_client, c.mode_paiement, c.statut_cautisation_client, c.date_cautisation, c.souscription_code, cli.nom_client, cli.telephone_client
                FROM cautisation_clients c
                LEFT JOIN clients cli ON cli.code_client = c.client_code
                WHERE (c.caisse_code = ? OR (c.commercial_code = ? AND DATE(c.date_cautisation) = ?))
                  AND c.statut_cautisation_client != 'annule'
                  AND c.etablissement_code = ? AND c.zone_code = ? AND c.annee_code = ?
                ORDER BY c.date_cautisation DESC
            ");
            $stmtCCotis->execute([
                $caisseCode, 
                $commCode, 
                $versement['periode_versement'],
                $etabCode, 
                $zoneCode, 
                $anneeCode
            ]);
            $caisseCotisations = $stmtCCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($caisseCotisations as $cc) {
                $totalCaisseAttendu += (float)($cc['montant_cautisation_client'] ?? 0);
            }
        }

        $caisseAttenduDef = $totalCaisseAttendu ?: (float)($caisseDetails['montant_total_attendu'] ?? 0);
        $caisseEcart = $montantVersement - $caisseAttenduDef;

        $this->json([
            'status' => 1,
            'data' => [
                'commercial_code' => $commCode,
                'versement_actuel' => $montantVersement,
                'versement_actuel_fmt' => number_format($montantVersement, 0, ',', ' ') . ' FCFA',
                'total_collecte' => $totalCollecte,
                'total_collecte_fmt' => number_format($totalCollecte, 0, ',', ' ') . ' FCFA',
                'total_especes' => (float)($cotisStats['total_especes'] ?? 0),
                'total_especes_fmt' => number_format((float)($cotisStats['total_especes'] ?? 0), 0, ',', ' ') . ' FCFA',
                'total_momo' => (float)($cotisStats['total_momo'] ?? 0),
                'total_momo_fmt' => number_format((float)($cotisStats['total_momo'] ?? 0), 0, ',', ' ') . ' FCFA',
                'total_autre' => (float)($cotisStats['total_autre'] ?? 0),
                'total_valide' => (float)($cotisStats['total_valide'] ?? 0),
                'total_attente' => (float)($cotisStats['total_attente'] ?? 0),
                'total_attente_fmt' => number_format((float)($cotisStats['total_attente'] ?? 0), 0, ',', ' ') . ' FCFA',
                'total_nb_cotis' => (int)($cotisStats['total_nb_cotis'] ?? 0),
                'total_versements_valides' => $totalVersementsValides,
                'total_versements_valides_fmt' => number_format($totalVersementsValides, 0, ',', ' ') . ' FCFA',
                'total_versements_attente' => (float)($versStats['total_versements_attente'] ?? 0),
                'solde_reste_a_verser' => $soldeResteAVerser,
                'solde_reste_a_verser_fmt' => number_format($soldeResteAVerser, 0, ',', ' ') . ' FCFA',
                'ecart_comparaison' => $ecartComparaison,
                'ecart_comparaison_fmt' => number_format(abs($ecartComparaison), 0, ',', ' ') . ' FCFA',
                // Données de la caisse spécifique
                'has_linked_caisse' => !empty($caisseDetails),
                'linked_caisse_code' => $caisseCode ?: '-',
                'caisse_attendu' => $caisseAttenduDef,
                'caisse_attendu_fmt' => number_format($caisseAttenduDef, 0, ',', ' ') . ' FCFA',
                'caisse_ecart' => $caisseEcart,
                'caisse_ecart_fmt' => number_format(abs($caisseEcart), 0, ',', ' ') . ' FCFA',
                'caisse_cotisations' => array_map(function($c) {
                    return [
                        'code' => $c['code_cautisation_client'],
                        'souscription' => $c['souscription_code'] ?? '-',
                        'montant' => (float)$c['montant_cautisation_client'],
                        'montant_fmt' => number_format((float)$c['montant_cautisation_client'], 0, ',', ' ') . ' FCFA',
                        'mode' => strtoupper($c['mode_paiement'] ?? 'ESPECES'),
                        'statut' => $c['statut_cautisation_client'],
                        'date' => $c['date_cautisation'] ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : '-',
                        'client' => $c['nom_client'] ?? 'Client',
                        'telephone' => $c['telephone_client'] ?? '-'
                    ];
                }, $caisseCotisations),
                'sessions' => array_map(function($s) {
                    return [
                        'code_caisse' => $s['code_caisse'],
                        'date_ouverture' => $s['date_ouverture'] ? date('d/m/Y H:i', strtotime($s['date_ouverture'])) : '-',
                        'date_cloture' => $s['date_cloture'] ? date('d/m/Y H:i', strtotime($s['date_cloture'])) : 'Non clôturée',
                        'statut_caisse' => $s['statut_caisse'],
                        'montant_depot' => (float)($s['montant_total_depot'] ?? 0),
                        'montant_depot_fmt' => number_format((float)($s['montant_total_depot'] ?? 0), 0, ',', ' ') . ' FCFA'
                    ];
                }, $sessionsList),
                'recent_cotisations' => array_map(function($c) {
                    return [
                        'code' => $c['code_cautisation_client'],
                        'montant' => (float)$c['montant_cautisation_client'],
                        'montant_fmt' => number_format((float)$c['montant_cautisation_client'], 0, ',', ' ') . ' FCFA',
                        'mode' => strtoupper($c['mode_paiement'] ?? 'ESPECES'),
                        'statut' => $c['statut_cautisation_client'],
                        'date' => $c['date_cautisation'] ? date('d/m/Y H:i', strtotime($c['date_cautisation'])) : '-',
                        'client' => $c['nom_client'] ?? 'Client'
                    ];
                }, $recentCotis)
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_MAKE_VERSEMENT');
        $data = $_POST;
        unset($data['csrf_token']);

        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $anneeCode = Context::annee();
        $zoneCode = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        if (empty($userCode) || empty($anneeCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale, l'année d'exercice et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $codeVersement = $this->validator->generateCode('versements_commerciaux', 'code_versement_commercial', 'VRS-', 8);

        $commercialCode = !empty($data['commercial_code']) ? $data['commercial_code'] : $userCode;
        if (empty($commercialCode) || empty($data['montant_versement'])) {
            $this->error('Veuillez renseigner le commercial et le montant versé !');
            return;
        }

        $periodeDebut = !empty($data['periode_versement_debut']) ? $data['periode_versement_debut'] : (!empty($data['periode_versement']) ? $data['periode_versement'] : date('Y-m-d'));

        $versementData = [
            'code_versement_commercial' => $codeVersement,
            'caisse_code' => $data['caisse_code'] ?? ($data['reference_versement'] ?? $codeVersement),
            'montant_versement' => (int)$data['montant_versement'],
            'commercial_code' => $commercialCode,
            'periode_versement' => $periodeDebut,
            'periode_versement_debut' => $periodeDebut,
            'periode_versement_fin' => !empty($data['periode_versement_fin']) ? $data['periode_versement_fin'] : date('Y-m-d'),
            'zone_code' => $zoneCode,
            'statut_versement' => 'En attente',
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'user_code' => $userCode,
            'created_at_versement' => date('Y-m-d H:i:s'),
            'user_validate' => '',
            'date_validation' => '1000-01-01 00:00:00',
            'commentaire_validation' => ''
        ];

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($versementData, array_flip($cols));

        if ($this->model->create($filteredData)) {
            // Notification pour le service Finance
            try {
                $userModel = new ModelUser();
                $commercial = $userModel->getByCode($commercialCode);
                $commNom = $commercial ? trim(($commercial['nom_user'] ?? '') . ' ' . ($commercial['prenom_user'] ?? '')) : 'Un agent commercial';

                NotificationService::notifyVersementSoumis([
                    'reference_code'     => $codeVersement,
                    'montant'            => (float)$versementData['montant_versement'],
                    'commercial_nom'     => $commNom,
                    'etablissement_code' => $etabCode,
                    'zone_code'          => $zoneCode,
                    'annee_code'         => $anneeCode
                ]);
            } catch (\Throwable $ne) {
                error_log('[VersementController] Notification error on submit: ' . $ne->getMessage());
            }

            $this->success('Versement de caisse transmis avec succès (En attente de validation finance) !', ['code' => $codeVersement]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du versement');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas modifier un versement transmis.');
            return;
        }

        $id = (int)$this->post('id_versement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Versement introuvable ou non autorisé');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Versement modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function valider()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        // RÈGLE RBAC : Seul le profil Finance / Admin peut valider ou rejeter un versement
        if (!Context::isFinance() && !Context::isAdmin()) {
            $this->error('Action non autorisée. Seul le service Comptabilité / Finance ou l\'Administration peut valider un versement.');
            return;
        }

        $id = (int)$this->post('id_versement');
        $statutRaw = strtolower(trim($this->post('statut_versement') ?? 'valide'));
        $statut = in_array($statutRaw, ['valide', 'validé'], true) ? 'valide' : (in_array($statutRaw, ['annule', 'annulé', 'rejete', 'rejeté'], true) ? 'annule' : 'valide');
        $commentaire = trim($this->post('commentaire_validation') ?? '');
        if (empty($commentaire)) {
            $commentaire = ($statut === 'valide') ? 'Validé par la comptabilité' : 'Rejeté / Annulé par la comptabilité';
        }
        $userValidateCode = Context::user() ?? '';

        if (!$id) {
            $this->error('Identifiant de versement invalide');
            return;
        }

        $versement = $this->model->getById($id);
        if (!$versement || $versement['etablissement_code'] !== Context::etablissement() || $versement['zone_code'] !== Context::zone() || $versement['annee_code'] !== Context::annee()) {
            $this->error('Versement introuvable.');
            return;
        }

        // Valider le versement et basculer les cotisations associées du commercial en 'valide'
        if ($this->model->validateVersement($id, $userValidateCode, $commentaire, $statut)) {
            $caisseCode = $versement['caisse_code'] ?? null;
            $db = $this->model->getCon();

            // 1. Synchronisation avec la table caisses
            if (!empty($caisseCode)) {
                $caisseDecision = ($statut === 'valide') ? 'valide' : 'rejete';
                $stmtCaisseUp = $db->prepare("
                    UPDATE caisses 
                    SET decission_caisse = ?, date_validation = NOW(), user_confirm = ?, date_confirm = NOW()
                    WHERE code_caisse = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                ");
                $stmtCaisseUp->execute([
                    $caisseDecision,
                    $userValidateCode,
                    $caisseCode,
                    Context::etablissement(),
                    Context::zone(),
                    Context::annee()
                ]);
            }

            // 2. Basculement des cotisations en 'valide' si versement validé
            if ($statut === 'valide') {
                if (!empty($caisseCode)) {
                    $stmtCotis = $db->prepare("
                        UPDATE cautisation_clients 
                        SET statut_cautisation_client = 'valide', updated_at_cautisation_client = NOW()
                        WHERE (caisse_code = ? OR (commercial_code = ? AND DATE(date_cautisation) = ?))
                          AND statut_cautisation_client = 'en_attente'
                          AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                    ");
                    $stmtCotis->execute([
                        $caisseCode,
                        $versement['commercial_code'],
                        $versement['periode_versement'],
                        Context::etablissement(),
                        Context::zone(),
                        Context::annee()
                    ]);
                } else {
                    $stmtCotis = $db->prepare("
                        UPDATE cautisation_clients 
                        SET statut_cautisation_client = 'valide', updated_at_cautisation_client = NOW()
                        WHERE commercial_code = ? AND statut_cautisation_client = 'en_attente'
                          AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
                    ");
                    $stmtCotis->execute([
                        $versement['commercial_code'],
                        Context::etablissement(),
                        Context::zone(),
                        Context::annee()
                    ]);
                }
            }

            // Notification pour le commercial ayant émis le versement
            try {
                NotificationService::notifyVersementValide([
                    'reference_code'     => $versement['code_versement'] ?? ('VER-' . $id),
                    'commercial_code'    => $versement['commercial_code'],
                    'montant'            => (float)($versement['montant_versement'] ?? 0),
                    'statut'             => $statut,
                    'etablissement_code' => Context::etablissement(),
                    'zone_code'          => Context::zone(),
                    'annee_code'         => Context::annee()
                ]);
            } catch (\Throwable $ne) {
                error_log('[VersementController] Notification error on validate: ' . $ne->getMessage());
            }

            $msg = ($statut === 'valide') 
                ? 'Versement validé, caisse clôturée et cotisations du commercial actualisées avec succès !' 
                : 'Versement et caisse rejetés / annulés avec succès !';
            $this->success($msg, ['reload' => true]);
        } else {
            $this->error('Erreur lors de la validation du versement');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');

        if (!Context::isFinance() && !Context::isAdmin()) {
            $this->error('Action non autorisée. Seul le service Comptabilité / Finance peut modifier le statut d\'un versement.');
            return;
        }

        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Versement introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Versement introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("Le versement demandé est introuvable.");
                return;
            }

            // Un commercial ne peut consulter que ses propres versements
            if (Context::isCommercial() && $item['commercial_code'] !== Context::user() && $item['user_code'] !== Context::user()) {
                $this->renderForbidden("Vous n'êtes pas autorisé à consulter ce versement.");
                return;
            }

            $stmtU = $this->model->getCon()->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtU->execute([$item['commercial_code']]);
            $commercial = $stmtU->fetch(PDO::FETCH_ASSOC);

            $stmtZ = $this->model->getCon()->prepare("SELECT * FROM zones WHERE code_zone = ?");
            $stmtZ->execute([$item['zone_code']]);
            $zone = $stmtZ->fetch(PDO::FETCH_ASSOC);

            $validatorUser = null;
            if (!empty($item['user_validate'])) {
                $stmtV = $this->model->getCon()->prepare("SELECT code_user, nom_user, prenom_user FROM users WHERE code_user = ?");
                $stmtV->execute([$item['user_validate']]);
                $validatorUser = $stmtV->fetch(PDO::FETCH_ASSOC);
            }

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le versement demandé est introuvable.");
            return;
        }
        $canValidate = Context::isFinance() || Context::isAdmin();
        $this->loadView('../views/versements/details.php', [
            'item' => $item,
            'commercial' => $commercial,
            'zone' => $zone,
            'validatorUser' => $validatorUser,
            'canValidate' => $canValidate,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('FINANCE_VALIDATE_VERSEMENT');
        if (Context::isCommercial()) {
            header('Location: ' . RACINE . 'versement/list');
            exit();
        }

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) { 
                header('Location: ' . RACINE . 'versement/list'); exit(); 
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'versement/list'); exit();
        }
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => $item,
            'commerciaux' => $commerciaux,
            'zones' => $zones,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('COMMERCIAL_MAKE_VERSEMENT');
        if (Context::isCommercial()) {
            header('Location: ' . RACINE . 'caisse_commercial/formulaire');
            exit();
        }

        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => [],
            'commerciaux' => $commerciaux,
            'zones' => $zones
        ]);
    }

    public function commissions()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $this->loadView('../views/versements/commissions.php');
    }

    public function apiCommissions()
    {
        $this->requirePermission(['COMMERCIAL_MAKE_VERSEMENT', 'FINANCE_VALIDATE_VERSEMENT']);
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT v.*, c.id_caisse,
                   uc.nom_user as nom_commercial, uc.prenom_user as prenom_commercial, uc.commission as commission_user,
                   uv.nom_user as nom_validator, uv.prenom_user as prenom_validator,
                   z.libelle_zone
            FROM versements_commerciaux v
            LEFT JOIN caisses c ON c.code_caisse = v.caisse_code
            LEFT JOIN users uc ON uc.code_user = v.commercial_code
            LEFT JOIN users uv ON uv.code_user = v.user_validate
            LEFT JOIN zones z ON z.code_zone = v.zone_code
            WHERE v.etablissement_code = ? AND v.zone_code = ? AND v.annee_code = ?
              AND LOWER(v.statut_versement) = 'valide'
        ";
        $params = [$etabCode, $zoneCode, $anneeCode];

        // RÈGLE RBAC : Le commercial ne voit que ses propres versements
        if (Context::isCommercial()) {
            $sql .= " AND (v.commercial_code = ? OR v.user_code = ?)";
            $params[] = Context::user();
            $params[] = Context::user();
        }

        $sql .= " ORDER BY v.date_validation DESC, v.created_at_versement DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        $totalVersementsValides = 0;
        $totalCommissions = 0;

        foreach ($items as $v) {
            $id = $v['id_versement'];
            $idCrypte = $this->validator->crypter($id);
            $caisseId = $v['id_caisse'] ?? null;
            $caisseIdCrypte = $caisseId ? $this->validator->crypter($caisseId) : $idCrypte;

            $montantVersement = (float)$v['montant_versement'];
            $tauxCommission = floatval($v['commission_user'] ?? 0);
            $montantCommission = round(($montantVersement * $tauxCommission) / 100, 2);

            $totalVersementsValides += $montantVersement;
            $totalCommissions += $montantCommission;

            $data[] = array_merge($v, [
                'id' => $id,
                'editId' => $idCrypte,
                'caisseIdCrypte' => $caisseIdCrypte,
                'nom_commercial_complet' => trim(($v['nom_commercial'] ?? '') . ' ' . ($v['prenom_commercial'] ?? '')),
                'nom_validator_complet' => trim(($v['nom_validator'] ?? '') . ' ' . ($v['prenom_validator'] ?? '')),
                'taux_commission' => $tauxCommission,
                'taux_commission_fmt' => number_format($tauxCommission, 2, ',', ' ') . ' %',
                'montant_commission' => $montantCommission,
                'montant_commission_fmt' => number_format($montantCommission, 0, ',', ' ') . ' FCFA',
                'montant_versement_fmt' => number_format($montantVersement, 0, ',', ' ') . ' FCFA'
            ]);
        }

        $userCommissionRate = 0;
        if (Context::isCommercial()) {
            $stmtU = $this->model->getCon()->prepare("SELECT commission FROM users WHERE code_user = ?");
            $stmtU->execute([Context::user()]);
            $uRow = $stmtU->fetch(PDO::FETCH_ASSOC);
            $userCommissionRate = floatval($uRow['commission'] ?? 0);
        } else {
            $userCommissionRate = $totalVersementsValides > 0 ? round(($totalCommissions / $totalVersementsValides) * 100, 2) : 0;
        }

        $this->json([
            'data' => $data,
            'summary' => [
                'count_versements' => count($data),
                'total_versements' => $totalVersementsValides,
                'total_versements_fmt' => number_format($totalVersementsValides, 0, ',', ' ') . ' FCFA',
                'total_commissions' => $totalCommissions,
                'total_commissions_fmt' => number_format($totalCommissions, 0, ',', ' ') . ' FCFA',
                'taux_commission' => $userCommissionRate,
                'taux_commission_fmt' => number_format($userCommissionRate, 2, ',', ' ') . ' %'
            ]
        ]);
    }
}
