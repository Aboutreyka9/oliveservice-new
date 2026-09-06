<?php

class ClientController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClient();
    }

    public function getStats(): array
    {
        $anneeCode = Context::annee();
        $zoneCode = Context::zone();
        $userCode = Context::user();
        $etabCode = Context::etablissement();

        $whereClause = "WHERE (c.etablissement_code = ? OR c.etablissement_code IS NULL)";
        $params = [$etabCode];

        if (Context::isCommercial()) {
            $whereClause .= " AND (c.user_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.user_code = ? AND sub.etablissement_code = ? AND sub.annee_code = ?))";
            $params[] = $userCode;
            $params[] = $userCode;
            $params[] = $etabCode;
            $params[] = $anneeCode;
        } elseif (Context::isGestionnaire() && !empty($zoneCode)) {
            $whereClause .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.zone_code = ? AND sub.etablissement_code = ? AND sub.annee_code = ?))";
            $params[] = $zoneCode;
            $params[] = $zoneCode;
            $params[] = $etabCode;
            $params[] = $anneeCode;
        }

        $sql = "
            SELECT 
                COUNT(*) as total_clients,
                COUNT(CASE WHEN c.statut_client = 'actif' THEN 1 END) as clients_actifs,
                COUNT(CASE WHEN c.statut_client != 'actif' OR c.statut_client IS NULL THEN 1 END) as clients_inactifs,
                COUNT(CASE WHEN EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.etablissement_code = ? AND sub.zone_code = ? AND sub.annee_code = ?) THEN 1 END) as clients_souscripteurs,
                COUNT(CASE WHEN c.created_at_client >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 END) as nouveaux_ce_mois
            FROM clients c
            {$whereClause}
        ";
        $statsParams = array_merge([$etabCode, $zoneCode, $anneeCode], $params);

        // Cumul des cotisations encaissées pour les clients du périmètre
        $sqlCot = "
            SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) as total_cotise
            FROM cautisation_clients cc
            WHERE cc.etablissement_code = ? AND cc.zone_code = ? AND cc.annee_code = ?
              AND (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)
        ";
        $cotParams = [$etabCode, $zoneCode, $anneeCode];
        if (Context::isCommercial()) {
            $sqlCot .= " AND (cc.user_code = ? OR cc.commercial_code = ?)";
            $cotParams[] = $userCode;
            $cotParams[] = $userCode;
        }

        try {
            $stmt = $this->model->getCon()->prepare($sql);
            $stmt->execute($statsParams);
            $res = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            $stmtCot = $this->model->getCon()->prepare($sqlCot);
            $stmtCot->execute($cotParams);
            $resCot = $stmtCot->fetch(PDO::FETCH_ASSOC) ?: [];

            $total = (int)($res['total_clients'] ?? 0);
            $actifs = (int)($res['clients_actifs'] ?? 0);
            $inactifs = (int)($res['clients_inactifs'] ?? 0);
            $souscripteurs = (int)($res['clients_souscripteurs'] ?? 0);
            $nouveaux = (int)($res['nouveaux_ce_mois'] ?? 0);
            $totalCotise = (float)($resCot['total_cotise'] ?? 0);
            $tauxEngagement = $total > 0 ? round(($souscripteurs / $total) * 100, 1) : 0;
            $tauxActifs = $total > 0 ? round(($actifs / $total) * 100, 1) : 0;

            return [
                'total_clients' => $total,
                'clients_actifs' => $actifs,
                'clients_inactifs' => $inactifs,
                'clients_souscripteurs' => $souscripteurs,
                'nouveaux_ce_mois' => $nouveaux,
                'total_cotise' => $totalCotise,
                'taux_engagement' => $tauxEngagement,
                'taux_actifs' => $tauxActifs
            ];
        } catch (Exception $e) {
            error_log("ClientController::getStats error: " . $e->getMessage());
            return [
                'total_clients' => 0,
                'clients_actifs' => 0,
                'clients_inactifs' => 0,
                'clients_souscripteurs' => 0,
                'nouveaux_ce_mois' => 0,
                'total_cotise' => 0,
                'taux_engagement' => 0,
                'taux_actifs' => 0
            ];
        }
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_CLIENTS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        $stats = $this->getStats();
        $this->loadView('../views/clients/list.php', [
            'stats' => $stats
        ]);
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_CLIENTS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        $anneeCode = Context::annee();
        $zoneCode = Context::zone();
        $userCode = Context::user();
        $etabCode = Context::etablissement();

        $sql = "
            SELECT c.*, z.libelle_zone,
                   (SELECT COUNT(*) FROM souscriptions sub 
                    WHERE sub.client_code = c.code_client 
                      AND sub.etablissement_code = ? AND sub.zone_code = ? AND sub.annee_code = ?) as nb_souscriptions,
                   (SELECT COALESCE(SUM(montant_cautisation_client), 0) FROM cautisation_clients cc 
                    WHERE cc.client_code = c.code_client 
                      AND cc.etablissement_code = ? AND cc.zone_code = ? AND cc.annee_code = ? 
                      AND (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)) as total_cotise
            FROM clients c
            LEFT JOIN zones z ON z.code_zone = c.zone_code
            WHERE (c.etablissement_code = ? OR c.etablissement_code IS NULL)
        ";
        $params = [
            $etabCode, $zoneCode, $anneeCode,
            $etabCode, $zoneCode, $anneeCode,
            $etabCode
        ];

        // Application du filtrage strict selon le rôle RBAC (Context)
        if (Context::isCommercial()) {
            $sql .= " AND (c.user_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.user_code = ? AND sub2.etablissement_code = ? AND sub2.annee_code = ?))";
            $params[] = $userCode;
            $params[] = $userCode;
            $params[] = $etabCode;
            $params[] = $anneeCode;
        } elseif (Context::isGestionnaire() && !empty($zoneCode)) {
            $sql .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.zone_code = ? AND sub2.etablissement_code = ? AND sub2.annee_code = ?))";
            $params[] = $zoneCode;
            $params[] = $zoneCode;
            $params[] = $etabCode;
            $params[] = $anneeCode;
        }

        $sql .= " ORDER BY c.created_at_client DESC, c.id_client DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        $totalClients = count($clients);
        $totalActifs = 0;
        $totalInactifs = 0;
        $totalSouscripteurs = 0;
        $cumulCotise = 0;

        foreach ($clients as $c) {
            $id = $c['id_client'];
            $idCrypte = $this->validator->crypter($id);
            $nomComplet = trim(($c['nom_client'] ?? '') . ' ' . ($c['prenom_client'] ?? ''));
            if (empty($nomComplet)) $nomComplet = 'Client Sans Nom';

            $isActif = (($c['statut_client'] ?? 'actif') === 'actif');
            if ($isActif) $totalActifs++; else $totalInactifs++;

            $nbSous = (int)($c['nb_souscriptions'] ?? 0);
            if ($nbSous > 0) $totalSouscripteurs++;

            $cotise = (float)($c['total_cotise'] ?? 0);
            $cumulCotise += $cotise;

            // Dérivation des initiales
            $words = explode(' ', $nomComplet);
            $inits = '';
            foreach ($words as $w) {
                if (!empty($w)) $inits .= mb_substr($w, 0, 1, 'UTF-8');
            }
            $inits = mb_strtoupper(mb_substr($inits, 0, 2, 'UTF-8'), 'UTF-8');
            if (empty($inits)) $inits = 'CL';

            $data[] = array_merge($c, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_complet' => $nomComplet,
                'initiales' => $inits,
                'date_creation' => !empty($c['created_at_client']) ? date('d/m/Y', strtotime($c['created_at_client'])) : '-',
                'nb_souscriptions' => $nbSous,
                'total_cotise' => $cotise,
                'total_cotise_fmt' => number_format($cotise, 0, ',', ' ') . ' F',
                'statut_client' => $c['statut_client'] ?? 'actif'
            ]);
        }

        $this->json([
            'data' => $data,
            'stats' => [
                'total_clients' => $totalClients,
                'clients_actifs' => $totalActifs,
                'clients_inactifs' => $totalInactifs,
                'clients_souscripteurs' => $totalSouscripteurs,
                'total_cotise' => $cumulCotise,
                'taux_engagement' => $totalClients > 0 ? round(($totalSouscripteurs / $totalClients) * 100, 1) : 0,
                'taux_actifs' => $totalClients > 0 ? round(($totalActifs / $totalClients) * 100, 1) : 0
            ]
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_ADD_CLIENT');
        $data = $_POST;
        unset($data['csrf_token']);

        // Nettoyage préalable des formats téléphoniques (+225 / 225)
        $this->cleanPhoneFields($data);

        // 1. Contrôle par téléphone (si renseigné)
        if (!empty($data['telephone_client'])) {
            $telClean = $data['telephone_client'];
            $stmtCheck = $this->model->getCon()->prepare("SELECT code_client, nom_client FROM clients WHERE telephone_client = ? LIMIT 1");
            $stmtCheck->execute([$telClean]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $this->error("Un client existe déjà avec ce numéro de téléphone ({$telClean}) : {$existing['nom_client']} (Code: {$existing['code_client']}).");
                return;
            }
        }

        // 2. Contrôle par numéro CNI (si renseigné)
        $cniVal = trim($data['numero_cni'] ?? ($data['cni_client'] ?? ''));
        if (!empty($cniVal)) {
            $stmtCheckCni = $this->model->getCon()->prepare("SELECT code_client, nom_client FROM clients WHERE numero_cni = ? LIMIT 1");
            $stmtCheckCni->execute([$cniVal]);
            $existingCni = $stmtCheckCni->fetch(PDO::FETCH_ASSOC);

            if ($existingCni) {
                $this->error("Un client existe déjà avec ce numéro de CNI ({$cniVal}) : {$existingCni['nom_client']} (Code: {$existingCni['code_client']}).");
                return;
            }
        }

        // 3. Contrôle anti-doublon par Nom complet + Lieu de résidence
        if (!empty($data['nom_client']) && !empty($data['lieu_residence_client'])) {
            $nom = trim($data['nom_client']);
            $residence = trim($data['lieu_residence_client']);

            $stmtCheckNom = $this->model->getCon()->prepare("SELECT code_client, nom_client, telephone_client FROM clients WHERE LOWER(nom_client) = LOWER(?) AND LOWER(lieu_residence_client) = LOWER(?) LIMIT 1");
            $stmtCheckNom->execute([$nom, $residence]);
            $existingNom = $stmtCheckNom->fetch(PDO::FETCH_ASSOC);

            if ($existingNom) {
                $this->error("Un client nommé '$nom' résidant à '$residence' existe déjà (Contact: {$existingNom['telephone_client']}, Code: {$existingNom['code_client']}).");
                return;
            }
        }

        $userCode = Context::user() ?? '';
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        if (empty($zoneCode)) {
            $stmtDefaultZone = $this->model->getCon()->query("SELECT code_zone FROM zones LIMIT 1");
            $defaultZone = $stmtDefaultZone->fetch(PDO::FETCH_ASSOC);
            $zoneCode = $defaultZone['code_zone'] ?? '6QIlVfXP0LiXE9tBzHownYLAAqDi2';
        }

        if (empty($data['code_client'])) {
            $data['code_client'] = $this->validator->generateCode('clients', 'code_client', 'CLI-', 8);
        }
        $data['statut_client'] = $data['statut_client'] ?? 'actif';
        $data['created_at_client'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE clients")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('zone_code', $cols) && empty($data['zone_code'])) $data['zone_code'] = $zoneCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Client créé avec succès!', ['code_client' => $data['code_client']]);
        } else {
            $this->error('Erreur lors de la création du client');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_EDIT_CLIENT');

        // RÈGLE STRICTE RBAC : Les commerciaux ne peuvent pas modifier les fiches clients
        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas modifier les fiches clients.');
            return;
        }

        $id = (int)$this->post('id_client');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['telephone_client'])) {
            if (!$this->checkUnique('clients', 'telephone_client', $data['telephone_client'], 'Téléphone client', 'id_client', $id)) return;
        }

        $data['updated_at_client'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE clients")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Client modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_EDIT_CLIENT');

        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas changer le statut d\'un client.');
            return;
        }

        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Client introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_CLIENTS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("Le client demandé est introuvable.");
                return;
            }

            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();
            $anneeCode = Context::annee();

            // Récupérer les souscriptions de ce client avec filtrage par rôle
            $sql = "
                SELECT s.*, p.libelle_pack, z.libelle_zone
                FROM souscriptions s
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription AND ps.etablissement_code = ? AND ps.zone_code = ? AND ps.annee_code = ?
                LEFT JOIN packs p ON p.code_pack = ps.pack_code AND p.etablissement_code = ? AND p.zone_code = ? AND p.annee_code = ?
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                WHERE s.client_code = ? AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            ";
            $params = [
                $etabCode, $zoneCode, $anneeCode,
                $etabCode, $zoneCode, $anneeCode,
                $item['code_client'],
                $etabCode, $zoneCode, $anneeCode
            ];

            if (Context::isCommercial()) {
                $sql .= " AND s.user_code = ?";
                $params[] = Context::user();
            }

            $sql .= " ORDER BY s.created_at_souscription DESC";

            $stmtSous = $this->model->getCon()->prepare($sql);
            $stmtSous->execute($params);
            $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC) ?: [];

            // Récupérer la liste des cotisations (versements) effectuées par ce client
            $sqlCot = "
                SELECT cc.*, s.code_souscription
                FROM cautisation_clients cc
                LEFT JOIN souscriptions s ON s.code_souscription = cc.souscription_code AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
                WHERE (cc.client_code = ? OR s.client_code = ?)
                  AND cc.etablissement_code = ? AND cc.zone_code = ? AND cc.annee_code = ?
            ";
            $paramsCot = [
                $etabCode, $zoneCode, $anneeCode,
                $item['code_client'], $item['code_client'],
                $etabCode, $zoneCode, $anneeCode
            ];

            if (Context::isCommercial()) {
                $sqlCot .= " AND (cc.user_code = ? OR cc.commercial_code = ?)";
                $paramsCot[] = Context::user();
                $paramsCot[] = Context::user();
            }

            $sqlCot .= " ORDER BY cc.created_at_cautisation_client DESC";

            $stmtCot = $this->model->getCon()->prepare($sqlCot);
            $stmtCot->execute($paramsCot);
            $cotisations = $stmtCot->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le client demandé est introuvable.");
            return;
        }
        $this->loadView('../views/clients/details.php', [
            'item' => $item,
            'souscriptions' => $souscriptions,
            'cotisations' => $cotisations,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_EDIT_CLIENT');
        if (Context::isCommercial()) {
            header('Location: ' . RACINE . 'client/list');
            exit();
        }

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'client/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'client/list'); exit();
        }
        $this->loadView('../views/clients/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('COMMERCIAL_ADD_CLIENT');
        $this->loadView('../views/clients/edit.php', ['item' => []]);
    }
}