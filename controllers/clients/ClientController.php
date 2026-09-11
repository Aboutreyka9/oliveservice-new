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

        $whereClause = "WHERE c.etablissement_code = ?";
        $params = [$etabCode];

        if (Context::isCommercial()) {
            $whereClause .= " AND (c.user_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.user_code = ? AND sub.etablissement_code = ?))";
            $params[] = $userCode;
            $params[] = $userCode;
            $params[] = $etabCode;
        } elseif (Context::isGestionnaire() && !empty($zoneCode)) {
            $whereClause .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.zone_code = ? AND sub.etablissement_code = ?))";
            $params[] = $zoneCode;
            $params[] = $zoneCode;
            $params[] = $etabCode;
        }

        $subSousExists = "SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.etablissement_code = c.etablissement_code";
        $subExistsParams = [];
        if (Context::isCommercial()) {
            $subSousExists .= " AND sub.user_code = ?";
            $subExistsParams[] = $userCode;
        }

        $sql = "
            SELECT 
                COUNT(*) as total_clients,
                COUNT(CASE WHEN c.statut_client = 'actif' THEN 1 END) as clients_actifs,
                COUNT(CASE WHEN c.statut_client != 'actif' OR c.statut_client IS NULL THEN 1 END) as clients_inactifs,
                COUNT(CASE WHEN EXISTS ({$subSousExists}) THEN 1 END) as clients_souscripteurs,
                COUNT(CASE WHEN c.created_at_client >= DATE_FORMAT(CURRENT_DATE(), '%Y-%m-01') THEN 1 END) as nouveaux_ce_mois
            FROM clients c
            {$whereClause}
        ";
        $statsParams = array_merge($subExistsParams, $params);

        // Cumul des cotisations encaissées pour les clients du périmètre
        $sqlCot = "
            SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) as total_cotise
            FROM cautisation_clients cc
            WHERE cc.etablissement_code = ?
              AND (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)
        ";
        $cotParams = [$etabCode];
        if (!empty($zoneCode)) {
            $sqlCot .= " AND cc.zone_code = ?";
            $cotParams[] = $zoneCode;
        }
        if (!empty($anneeCode)) {
            $sqlCot .= " AND cc.annee_code = ?";
            $cotParams[] = $anneeCode;
        }
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

        $subSousSql = "SELECT COUNT(*) FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.etablissement_code = c.etablissement_code";
        $subSousParams = [];
        if (Context::isCommercial()) {
            $subSousSql .= " AND sub.user_code = ?";
            $subSousParams[] = $userCode;
        }

        $subCotSql = "SELECT COALESCE(SUM(montant_cautisation_client), 0) FROM cautisation_clients cc 
                      WHERE cc.client_code = c.code_client 
                        AND cc.etablissement_code = c.etablissement_code 
                        AND (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)";
        $subCotParams = [];
        if (Context::isCommercial()) {
            $subCotSql .= " AND (cc.user_code = ? OR cc.commercial_code = ?)";
            $subCotParams[] = $userCode;
            $subCotParams[] = $userCode;
        }

        $sql = "
            SELECT c.*, z.libelle_zone,
                   ({$subSousSql}) as nb_souscriptions,
                   ({$subCotSql}) as total_cotise
            FROM clients c
            LEFT JOIN zones z ON z.code_zone = c.zone_code
            WHERE c.etablissement_code = ?
        ";
        $params = array_merge($subSousParams, $subCotParams, [$etabCode]);

        // Application du filtrage strict selon le rôle RBAC (Context)
        if (Context::isCommercial()) {
            $sql .= " AND (c.user_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.user_code = ? AND sub2.etablissement_code = ?))";
            $params[] = $userCode;
            $params[] = $userCode;
            $params[] = $etabCode;
        } elseif (Context::isGestionnaire() && !empty($zoneCode)) {
            $sql .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.zone_code = ? AND sub2.etablissement_code = ?))";
            $params[] = $zoneCode;
            $params[] = $zoneCode;
            $params[] = $etabCode;
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
            $nomComplet = trim($c['nom_client'] ?? '');
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

        $zoneCodeCheck = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        // 1. Contrôle par Téléphone Principal + zone_code (anti-doublon par zone)
        if (!empty($data['telephone_client'])) {
            $telClean = trim($data['telephone_client']);
            if (!empty($zoneCodeCheck)) {
                $stmtCheck = $this->model->getCon()->prepare("SELECT code_client, nom_client FROM clients WHERE telephone_client = ? AND zone_code = ? LIMIT 1");
                $stmtCheck->execute([$telClean, $zoneCodeCheck]);
            } else {
                $stmtCheck = $this->model->getCon()->prepare("SELECT code_client, nom_client FROM clients WHERE telephone_client = ? LIMIT 1");
                $stmtCheck->execute([$telClean]);
            }
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $nomClient = trim($existing['nom_client'] ?? '');
                $this->error("Un client existe déjà avec le numéro de téléphone ({$telClean}) dans cette zone : {$nomClient} (Code: {$existing['code_client']}).");
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

        // 3. Contrôle anti-doublon par Nom complet + Lieu de résidence dans la même zone
        if (!empty($data['nom_client']) && !empty($data['lieu_residence_client'])) {
            $nom = trim($data['nom_client']);
            $residence = trim($data['lieu_residence_client']);

            if (!empty($zoneCodeCheck)) {
                $stmtCheckNom = $this->model->getCon()->prepare("SELECT code_client, nom_client, telephone_client FROM clients WHERE LOWER(nom_client) = LOWER(?) AND LOWER(lieu_residence_client) = LOWER(?) AND zone_code = ? LIMIT 1");
                $stmtCheckNom->execute([$nom, $residence, $zoneCodeCheck]);
            } else {
                $stmtCheckNom = $this->model->getCon()->prepare("SELECT code_client, nom_client, telephone_client FROM clients WHERE LOWER(nom_client) = LOWER(?) AND LOWER(lieu_residence_client) = LOWER(?) LIMIT 1");
                $stmtCheckNom->execute([$nom, $residence]);
            }
            $existingNom = $stmtCheckNom->fetch(PDO::FETCH_ASSOC);

            if ($existingNom) {
                $this->error("Un client nommé '$nom' résidant à '$residence' existe déjà dans cette zone (Contact: {$existingNom['telephone_client']}, Code: {$existingNom['code_client']}).");
                return;
            }
        }

        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $zoneCode = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        if (empty($userCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
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

        $this->cleanPhoneFields($data);

        $clientExist = $this->model->getById($id);
        $zoneCodeCheck = !empty($data['zone_code']) ? $data['zone_code'] : ($clientExist['zone_code'] ?? Context::zone());

        if (!empty($data['telephone_client'])) {
            $telClean = trim($data['telephone_client']);
            if (!empty($zoneCodeCheck)) {
                $stmtCheck = $this->model->getCon()->prepare("SELECT id_client, code_client, nom_client FROM clients WHERE telephone_client = ? AND zone_code = ? AND id_client != ? LIMIT 1");
                $stmtCheck->execute([$telClean, $zoneCodeCheck, $id]);
            } else {
                $stmtCheck = $this->model->getCon()->prepare("SELECT id_client, code_client, nom_client FROM clients WHERE telephone_client = ? AND id_client != ? LIMIT 1");
                $stmtCheck->execute([$telClean, $id]);
            }
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($existing) {
                $nomClient = trim($existing['nom_client'] ?? '');
                $this->error("Un autre client existe déjà avec ce numéro de téléphone ({$telClean}) dans cette zone : {$nomClient} (Code: {$existing['code_client']}).");
                return;
            }
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
            $stmtClient = $this->model->getCon()->prepare("
                SELECT c.*, 
                       z.libelle_zone,
                       u.nom_user as commercial_nom,
                       u.prenom_user as commercial_prenom,
                       u.matricule_user as commercial_matricule
                FROM clients c
                LEFT JOIN zones z ON z.code_zone = c.zone_code
                LEFT JOIN users u ON u.code_user = c.user_code
                WHERE c.id_client = ?
                LIMIT 1
            ");
            $stmtClient->execute([$id]);
            $item = $stmtClient->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $this->renderNotFound("Le client demandé est introuvable.");
                return;
            }

            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();
            $anneeCode = Context::annee();

            // Récupérer les souscriptions de ce client avec filtrage par rôle
            $sql = "
                SELECT s.*, 
                       z.libelle_zone,
                       sess.libelle_session,
                       sess.nombre_jour_session,
                       (SELECT GROUP_CONCAT(DISTINCT p2.libelle_pack SEPARATOR ', ') FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as libelle_pack,
                       (SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as sum_prix_cotisation_pack,
                       ((SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) * COALESCE(sess.nombre_jour_session, 0)) as totale_souscription,
                       (SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as montant_total_cotise,
                       (SELECT COALESCE(SUM(mc.nombre_jour), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as nombre_jour_cotise,
                       sess.nombre_jour_session as nombre_jour_total
                FROM souscriptions s
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                LEFT JOIN sessions sess ON sess.code_session = s.session_code
                WHERE s.client_code = ? AND s.etablissement_code = ?
            ";
            $params = [$item['code_client'], $etabCode];

            if (Context::isCommercial()) {
                $sql .= " AND s.user_code = ?";
                $params[] = Context::user();
            }

            $sql .= " ORDER BY s.created_at_souscription DESC";

            $stmtSous = $this->model->getCon()->prepare($sql);
            $stmtSous->execute($params);
            $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($souscriptions as &$s) {
                $sId = $s['id_souscription'];
                $s['encrypted_id'] = $this->validator->crypter($sId);

                $sumPrixCotisation = (float)($s['sum_prix_cotisation_pack'] ?? 0);
                if ($sumPrixCotisation <= 0 && !empty($s['montant_cotisation_journaliere'])) {
                    $sumPrixCotisation = (float)$s['montant_cotisation_journaliere'];
                }
                $s['calculated_prix_cotisation'] = $sumPrixCotisation;

                $nombreJourSession = (int)($s['nombre_jour_session'] ?? ($s['nombre_jour_total'] ?? 0));
                $s['calculated_nb_jours'] = $nombreJourSession;

                $totaleSouscription = (float)($s['totale_souscription'] ?? 0);
                if ($totaleSouscription <= 0) {
                    $totaleSouscription = !empty($s['montant_total_prevu']) ? (float)$s['montant_total_prevu'] : ($sumPrixCotisation * $nombreJourSession);
                }
                $s['calculated_total_souscription'] = $totaleSouscription;

                $montantCotise = (float)($s['montant_total_cotise'] ?? 0);
                $s['calculated_total_cotise'] = $montantCotise;

                $soldeRestant = max(0, $totaleSouscription - $montantCotise);
                $s['calculated_solde_restant'] = $soldeRestant;

                $joursCotises = (int)($s['nombre_jour_cotise'] ?? 0);
                $s['calculated_jours_cotises'] = $joursCotises;

                $progression = $nombreJourSession > 0 ? min(100, round(($joursCotises / $nombreJourSession) * 100)) : 0;
                $s['calculated_progression'] = $progression;
            }
            unset($s);

            // Récupérer la liste des cotisations (versements) effectuées par ce client
            $sqlCot = "
                SELECT cc.*, s.code_souscription
                FROM cautisation_clients cc
                LEFT JOIN souscriptions s ON s.code_souscription = cc.souscription_code AND s.etablissement_code = ?
                WHERE (cc.client_code = ? OR s.client_code = ?)
                  AND cc.etablissement_code = ?
            ";
            $paramsCot = [
                $etabCode,
                $item['code_client'], $item['code_client'],
                $etabCode
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