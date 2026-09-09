<?php

class CotisationController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCotisation();
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_COTISATIONS', 'FINANCE_VIEW_ALL_COTISATIONS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        $this->loadView('../views/cotisations/list.php');
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_COTISATIONS', 'FINANCE_VIEW_ALL_COTISATIONS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        
        $sql = "
            SELECT c.*, 
                   cl.nom_client, cl.telephone_client,
                   u.nom_user as nom_commercial, u.prenom_user as prenom_commercial,
                   (
                       SELECT cs.statut_caisse 
                       FROM caisses cs 
                       WHERE (
                           (c.caisse_code IS NOT NULL AND c.caisse_code != '' AND cs.code_caisse = c.caisse_code)
                           OR (cs.user_code = c.commercial_code AND DATE(cs.date_ouverture) = DATE(c.date_cautisation))
                       )
                       ORDER BY cs.id_caisse DESC 
                       LIMIT 1
                   ) as statut_caisse_commercial
            FROM cautisation_clients c
            LEFT JOIN clients cl ON cl.code_client = c.client_code
            LEFT JOIN users u ON u.code_user = c.commercial_code
            WHERE 1=1
        ";
        $params = [];
        $conds = [];
        Context::applyTripleFilter('c', $conds, $params, true, false);
        if (!empty($conds)) {
            $sql .= " AND " . implode(' AND ', $conds);
        }

        $sql .= " ORDER BY c.date_cautisation DESC, c.id_cautisation_client DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        foreach ($items as $c) {
            $id = $c['id_cautisation_client'];
            $idCrypte = $this->validator->crypter($id);
            $caisseCloturee = (($c['statut_caisse_commercial'] ?? '') === 'cloture');
            $data[] = array_merge($c, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_client_complet' => trim(($c['nom_client'] ?? '')),
                'nom_commercial_complet' => trim(($c['nom_commercial'] ?? '') . ' ' . ($c['prenom_commercial'] ?? '')),
                'caisse_cloturee' => $caisseCloturee
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_COLLECT_COTISATION');
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['souscription_code']) || empty($data['montant_cautisation'])) {
            $this->error('Veuillez renseigner la souscription et le montant versé !');
            return;
        }

        $stmtSous = $this->model->getCon()->prepare("SELECT * FROM souscriptions WHERE code_souscription = ?");
        $stmtSous->execute([$data['souscription_code']]);
        $sous = $stmtSous->fetch(PDO::FETCH_ASSOC);

        if (!$sous) {
            $this->error('Souscription introuvable !');
            return;
        }

        if ($sous['statut_souscription'] === 'solde') {
            $this->error('Cette souscription est déjà soldée, aucune cotisation supplémentaire possible.');
            return;
        }

        if ($sous['statut_souscription'] === 'annule') {
            $this->error('Cette souscription est annulée, impossible d\'ajouter une cotisation.');
            return;
        }

        $userCode = Context::user();
        $anneeCode = Context::annee();
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone() ?: ($sous['zone_code'] ?? '');

        if (empty($userCode) || empty($anneeCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale, l'année d'exercice et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $codeCotisation = $this->validator->generateCode('cautisation_clients', 'code_cautisation_client', 'COT-', 8);

        $cotisJour = (float)($sous['montant_cotisation_journaliere'] ?? 0);
        if ($cotisJour <= 0) {
            $this->error("Erreur d'insertion : La cotisation journalière configurée sur cette souscription est invalide ou égale à 0.");
            return;
        }
        $montant = (float)$data['montant_cautisation'];
        $nbJours = (int)($data['nombre_jour_paye'] ?: ($cotisJour > 0 ? ceil($montant / $cotisJour) : 1));

        $filename = null;
        if (!empty($_FILES['photo_recu']['name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/images/recus/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo_recu']['name'], PATHINFO_EXTENSION);
            $filename = 'recu_' . time() . '_' . uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['photo_recu']['tmp_name'], $uploadDir . $filename);
        }

        // RÈGLE STRICTE RBAC : Les cotisations saisies par un commercial restent 'en_attente' jusqu'à validation caisse
        $statutInitial = Context::isCommercial() ? 'en_attente' : 'valide';

        $cotisationData = [
            'code_cautisation_client' => $codeCotisation,
            'souscription_code' => $data['souscription_code'],
            'client_code' => $sous['client_code'],
            'montant_cautisation_client' => $montant,
            'nombre_jour' => $nbJours,
            'mode_paiement' => $data['mode_paiement'] ?? 'espece',
            'date_cautisation' => $data['date_cautisation'] ?: date('Y-m-d'),
            'commercial_code' => $userCode,
            'reference_paiement' => $data['reference_paiement'] ?? '',
            'recu_numero' => $data['recu_numero'] ?? $codeCotisation,
            'photo_recu' => $filename,
            'statut_cautisation_client' => $statutInitial,
            'annee_code' => $anneeCode,
            'etablissement_code' => $etabCode,
            'zone_code' => Context::zone(),
            'user_code' => $userCode,
            'created_at_cautisation_client' => date('Y-m-d H:i:s'),
            'updated_at_cautisation_client' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createCotisation($cotisationData)) {
            if ($statutInitial === 'valide') {
                $modelSouscription = new ModelSouscription();
                $modelSouscription->updateTotals($data['souscription_code'], $montant, $nbJours);
            }

            // Notification In-App
            try {
                $stmtClient = $this->model->getCon()->prepare("SELECT nom_client FROM clients WHERE code_client = ?");
                $stmtClient->execute([$sous['client_code']]);
                $clientNom = $stmtClient->fetchColumn() ?: 'Client';

                NotificationService::notifyCotisationClient([
                    'reference_code'    => $codeCotisation,
                    'souscription_code' => $data['souscription_code'],
                    'montant'           => $montant,
                    'client_nom'        => $clientNom,
                    'client_code'       => $sous['client_code'] ?? '',
                    'user_code'         => $userCode,
                    'etablissement_code'=> $etabCode,
                    'zone_code'         => $zoneCode,
                    'annee_code'        => $anneeCode
                ]);

                // Vérifier si la souscription est 100% soldée
                $nouveauSolde = max(0, (float)($sous['solde_restant'] ?? 0) - $montant);
                if ($nouveauSolde <= 0) {
                    NotificationService::notifySouscriptionSoldee([
                        'reference_code'    => $data['souscription_code'],
                        'client_nom'        => $clientNom,
                        'montant_total'     => (float)($sous['montant_total'] ?? $montant),
                        'etablissement_code'=> $etabCode,
                        'zone_code'         => $zoneCode,
                        'annee_code'        => $anneeCode
                    ]);
                }
            } catch (\Throwable $ne) {
                error_log('[CotisationController] Notification error: ' . $ne->getMessage());
            }

            $msg = Context::isCommercial() 
                ? 'Cotisation enregistrée avec succès (En attente de validation de la caisse/comptabilité).' 
                : 'Cotisation enregistrée et validée avec succès !';
            $this->success($msg, ['code' => $codeCotisation]);
        } else {
            $this->error('Erreur lors de l\'enregistrement de la cotisation');
        }
    }

    private function isCaisseClotureeForItem(array $item): bool
    {
        $caisseCode = $item['caisse_code'] ?? '';
        $commCode = $item['commercial_code'] ?? '';
        $dateCotis = !empty($item['date_cautisation']) ? date('Y-m-d', strtotime($item['date_cautisation'])) : date('Y-m-d');

        $stmt = $this->model->getCon()->prepare("
            SELECT cs.statut_caisse 
            FROM caisses cs 
            WHERE (
                (? != '' AND cs.code_caisse = ?)
                OR (cs.user_code = ? AND DATE(cs.date_ouverture) = ?)
            )
            ORDER BY cs.id_caisse DESC 
            LIMIT 1
        ");
        $stmt->execute([$caisseCode, $caisseCode, $commCode, $dateCotis]);
        $statut = $stmt->fetchColumn();
        return ($statut === 'cloture');
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_EDIT_COTISATION');

        // RÈGLE STRICTE RBAC : Un commercial ne peut PAS modifier les cotisations
        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas modifier les cotisations.');
            return;
        }

        $id = (int)$this->post('id_cautisation_client');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $item = $this->model->getById($id);
        if ($item && $this->isCaisseClotureeForItem($item)) {
            $this->error('Modification impossible : la caisse du commercial pour cette cotisation est déjà clôturée.');
            return;
        }
        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_cautisation_client'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE cautisation_clients")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Cotisation modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_EDIT_COTISATION');

        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas changer le statut d\'une cotisation.');
            return;
        }

        $id = $this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            if ($this->isCaisseClotureeForItem($item)) {
                $this->error('Action impossible : la caisse du commercial pour cette cotisation est déjà clôturée.');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Cotisation introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_COTISATIONS', 'FINANCE_VIEW_ALL_COTISATIONS']);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("La cotisation demandée est introuvable.");
                return;
            }

            if (Context::isCommercial() && ($item['commercial_code'] ?? '') !== Context::user() && ($item['user_code'] ?? '') !== Context::user()) {
                $this->renderForbidden("Vous n'êtes pas autorisé à consulter cette cotisation.");
                return;
            }

            $sqlSous = "
                SELECT s.*, c.nom_client, p.libelle_pack 
                FROM souscriptions s 
                LEFT JOIN clients c ON c.code_client = s.client_code 
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription 
                LEFT JOIN packs p ON p.code_pack = ps.pack_code 
                WHERE s.code_souscription = ?
            ";
            $pSous = [$item['souscription_code']];
            $cSous = [];
            Context::applyTripleFilter('s', $cSous, $pSous, false);
            if (!empty($cSous)) $sqlSous .= " AND " . implode(' AND ', $cSous);
            $stmtSous = $this->model->getCon()->prepare($sqlSous);
            $stmtSous->execute($pSous);
            $souscription = $stmtSous->fetch(PDO::FETCH_ASSOC);

            $stmtCommercial = $this->model->getCon()->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtCommercial->execute([$item['commercial_code'] ?? '']);
            $commercial = $stmtCommercial->fetch(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La cotisation demandée est introuvable.");
            return;
        }
        $this->loadView('../views/cotisations/details.php', [
            'item' => $item,
            'souscription' => $souscription,
            'commercial' => $commercial,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('FINANCE_EDIT_COTISATION');
        if (Context::isCommercial()) {
            header('Location: ' . RACINE . 'cotisation/list');
            exit();
        }

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'cotisation/list'); exit(); }
            if ($this->isCaisseClotureeForItem($item)) {
                header('Location: ' . RACINE . 'cotisation/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cotisation/list'); exit();
        }
        $sqlSous = "
            SELECT s.code_souscription, c.nom_client, p.libelle_pack 
            FROM souscriptions s 
            LEFT JOIN clients c ON c.code_client = s.client_code 
            LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription 
            LEFT JOIN packs p ON p.code_pack = ps.pack_code 
            WHERE s.statut_souscription IN ('valide', 'reconduite')
        ";
        $pS = [];
        $cS = [];
        Context::applyTripleFilter('s', $cS, $pS, false);
        if (!empty($cS)) $sqlSous .= " AND " . implode(' AND ', $cS);
        $stmtS = $this->model->getCon()->prepare($sqlSous);
        $stmtS->execute($pS);
        $souscriptions = $stmtS->fetchAll(PDO::FETCH_ASSOC);
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/cotisations/edit.php', [
            'item' => $item,
            'souscriptions' => $souscriptions,
            'commerciaux' => $commerciaux,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('COMMERCIAL_COLLECT_COTISATION');
        $sqlSous = "
            SELECT s.code_souscription, s.montant_cotisation_journaliere, s.montant_total_cotise, s.montant_total_prevu, s.nombre_jour_total, s.nombre_jour_cotise, c.nom_client, p.libelle_pack 
            FROM souscriptions s 
            LEFT JOIN clients c ON c.code_client = s.client_code 
            LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription 
            LEFT JOIN packs p ON p.code_pack = ps.pack_code 
            WHERE s.statut_souscription IN ('valide', 'reconduite')
        ";
        $pS = [];
        $cS = [];
        Context::applyTripleFilter('s', $cS, $pS, false);
        if (!empty($cS)) $sqlSous .= " AND " . implode(' AND ', $cS);
        $stmtS = $this->model->getCon()->prepare($sqlSous);
        $stmtS->execute($pS);
        $souscriptions = $stmtS->fetchAll(PDO::FETCH_ASSOC);
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $selectedSouscription = $_GET['souscription'] ?? '';

        $this->loadView('../views/cotisations/edit.php', [
            'item' => ['souscription_code' => $selectedSouscription],
            'souscriptions' => $souscriptions,
            'commerciaux' => $commerciaux
        ]);
    }
}
