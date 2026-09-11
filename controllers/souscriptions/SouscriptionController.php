<?php

class SouscriptionController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSouscription();
    }

    public function list()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_SOUSCRIPTIONS', 'GESTIONNAIRE_VIEW_ALL_SOUSCRIPTIONS']);

        $userScopeFilter = Context::isCommercial() ? Context::user() : null;
        $zoneScopeFilter = Context::isGestionnaire() ? Context::zone() : null;
        $anneeScopeFilter = Context::annee();

        $items = $this->model->getAllWithDetails($userScopeFilter, $zoneScopeFilter, $anneeScopeFilter);

        $totalSouscriptions = count($items);
        $totalEngage = 0;
        $totalCotise = 0;
        $totalSoldeCount = 0;
        $totalEnCoursCount = 0;

        foreach ($items as $s) {
            $sumPrixCotisation = (float)($s['sum_prix_cotisation_pack'] ?? 0);
            $nombreJourSession = (int)($s['nombre_jour_session'] ?? 0);
            $totaleSouscription = (float)($s['totale_souscription'] ?? ($sumPrixCotisation * $nombreJourSession));
            $montantCotise = (float)($s['montant_total_cotise'] ?? 0);

            $totalEngage += $totaleSouscription;
            $totalCotise += $montantCotise;

            if (($s['statut_souscription'] ?? '') === 'solde') {
                $totalSoldeCount++;
            } else {
                $totalEnCoursCount++;
            }
        }

        $resteARecouvrer = max(0, $totalEngage - $totalCotise);
        $tauxRecouvrement = $totalEngage > 0 ? round(($totalCotise / $totalEngage) * 100, 1) : 0;

        $stats = [
            'total_souscriptions' => $totalSouscriptions,
            'total_engage' => $totalEngage,
            'total_cotise' => $totalCotise,
            'reste_a_recouvrer' => $resteARecouvrer,
            'taux_recouvrement' => $tauxRecouvrement,
            'total_encours_count' => $totalEnCoursCount,
            'total_solde_count' => $totalSoldeCount,
        ];

        $this->loadView('../views/souscriptions/list.php', [
            'stats' => $stats
        ]);
    }

    public function apiList()
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_SOUSCRIPTIONS', 'GESTIONNAIRE_VIEW_ALL_SOUSCRIPTIONS']);

        // Récupération sécurisée du périmètre d'accès selon le rôle connecté
        $userScopeFilter = Context::isCommercial() ? Context::user() : null;
        $zoneScopeFilter = Context::isGestionnaire() ? Context::zone() : null;
        $anneeScopeFilter = Context::annee();

        $items = $this->model->getAllWithDetails($userScopeFilter, $zoneScopeFilter, $anneeScopeFilter);

        $grouped = [];
        foreach ($items as $s) {
            $code = $s['code_souscription'];
            if (!isset($grouped[$code])) {
                $grouped[$code] = $s;
                $grouped[$code]['packs'] = [];
            }
            if (!empty($s['libelle_pack'])) {
                $grouped[$code]['packs'][] = $s['libelle_pack'];
            }
        }

        $data = [];
        foreach ($grouped as $s) {
            $id = $s['id_souscription'];
            $idCrypte = $this->validator->crypter($id);
            
            $sumPrixCotisation = (float)($s['sum_prix_cotisation_pack'] ?? 0);
            $nombreJourSession = (int)($s['nombre_jour_session'] ?? 0);
            $totaleSouscription = (float)($s['totale_souscription'] ?? ($sumPrixCotisation * $nombreJourSession));
            $montantCotise = (float)($s['montant_total_cotise'] ?? 0);
            $soldeRestant = max(0, $totaleSouscription - $montantCotise);
            $joursRestants = max(0, $nombreJourSession - (int)($s['nombre_jour_cotise'] ?? 0));

            $nbPacks = count($s['packs'] ?? []);
            if ($nbPacks > 1) {
                $packLabel = $s['packs'][0] . ' <small style="color:#64748B;">+' . ($nbPacks - 1) . ' autre(s)</small>';
            } elseif ($nbPacks === 1) {
                $packLabel = $s['packs'][0];
            } else {
                $packLabel = '-';
            }
            $data[] = array_merge($s, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_client_complet' => trim($s['nom_client'] ?? ''),
                'date_souscription' => isset($s['created_at_souscription']) && !empty($s['created_at_souscription'])
                    ? date('d-m-Y', strtotime($s['created_at_souscription']))
                    : '-',
                'libelle_pack' => $packLabel,
                'nombre_packs' => $nbPacks,
                'sum_prix_cotisation_pack' => $sumPrixCotisation,
                'totale_souscription' => $totaleSouscription,
                'solde_restant' => $soldeRestant,
                'jours_restants' => $joursRestants,
                'progression' => $nombreJourSession > 0 ? round((($s['nombre_jour_cotise'] ?? 0) / $nombreJourSession) * 100) : 0
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_ADD_SOUSCRIPTION');
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['client_code']) || empty($data['pack_code'])) {
            $this->error('Veuillez sélectionner un client et un pack !');
            return;
        }

        $etabCode = Context::etablissement();
        $userCode = Context::user();
        $zoneCode = $data['zone_code'] ?? Context::zone();

        // Priorité : annee_code soumis > Context::annee()
        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : Context::annee();

        if (empty($anneeCode)) {
            $this->error("L'année d'activité est obligatoire pour enregistrer une souscription. Veuillez sélectionner une année valide ou configurer une année active.");
            return;
        }

        // Vérification de l'existence dans annees
        $stmtAnneeCheck = $this->model->getCon()->prepare("SELECT code_annee FROM annees WHERE code_annee = ? LIMIT 1");
        $stmtAnneeCheck->execute([$anneeCode]);
        if (!$stmtAnneeCheck->fetch()) {
            $this->error("L'année d'activité sélectionnée pour la souscription est invalide ou introuvable.");
            return;
        }

        if (empty($userCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $stmtPack = $this->model->getCon()->prepare("
            SELECT * FROM packs 
            WHERE code_pack = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtPack->execute([$data['pack_code'], $etabCode, $zoneCode, $anneeCode]);
        $pack = $stmtPack->fetch(PDO::FETCH_ASSOC);

        if (!$pack) {
            $this->error('Pack sélectionné introuvable !');
            return;
        }

        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);

        $nbJours = (int)($data['nombre_jour_total'] ?: ($pack['nombre_jour_pack'] ?: 170));
        $cotisJour = (float)($data['montant_cotisation_journaliere'] ?: ($pack['prix_cotisation_pack'] ?: 0));
        $montantTotal = $nbJours * $cotisJour;

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $data['client_code'],
            'session_code' => $data['session_code'] ?: ($pack['session_code'] ?: null),
            'zone_code' => $zoneCode,
            'date_debut_souscription' => $data['date_debut_souscription'] ?: date('Y-m-d'),
            'montant_total_prevu' => $montantTotal,
            'montant_cotisation_journaliere' => $cotisJour,
            'nombre_jour_total' => $nbJours,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createSouscriptionWithPack($souscriptionData, $data['pack_code'], 1, $cotisJour)) {
            $this->success('Souscription créée avec succès !', ['code_souscription' => $codeSouscription]);
        } else {
            $this->error('Erreur lors de la création de la souscription');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_EDIT_SOUSCRIPTION');

        // RÈGLE STRICTE RBAC : Les commerciaux ne peuvent pas modifier les souscriptions
        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas modifier une souscription.');
            return;
        }

        $id = (int)$this->post('id_souscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $existing = $this->model->getById($id);
        if (!$existing || $existing['etablissement_code'] !== Context::etablissement() || $existing['zone_code'] !== Context::zone() || $existing['annee_code'] !== Context::annee()) {
            $this->error('Souscription introuvable ou non autorisée');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $data['updated_at_souscription'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE souscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Souscription modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_EDIT_SOUSCRIPTION');

        if (Context::isCommercial()) {
            $this->error('Action non autorisée. Les commerciaux ne peuvent pas changer le statut d\'une souscription.');
            return;
        }

        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Souscription introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Souscription introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission(['COMMERCIAL_VIEW_OWN_SOUSCRIPTIONS', 'GESTIONNAIRE_VIEW_ALL_SOUSCRIPTIONS']);
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getByIdWithDetails($id);
            if (!$item) {
                $this->renderNotFound("La souscription demandée est introuvable.");
                return;
            }

            if (Context::isCommercial() && $item['user_code'] !== Context::user()) {
                $this->renderForbidden("Vous n'êtes pas autorisé à consulter cette souscription.");
                return;
            }

            $packSouscrit = $this->model->getPackSouscrit($item['code_souscription']);
            $allPacks = $this->model->getPacksSouscrits($item['code_souscription']);
            $soldeRestant = $this->model->getSoldeRestant($item['code_souscription']);
            $joursRestants = $this->model->getJoursRestants($item['code_souscription']);

            $stmtClient = $this->model->getCon()->prepare("SELECT * FROM clients WHERE code_client = ?");
            $stmtClient->execute([$item['client_code']]);
            $client = $stmtClient->fetch(PDO::FETCH_ASSOC);

            $stmtCotis = $this->model->getCon()->prepare("
                SELECT c.*, u.nom_user, u.prenom_user
                FROM cautisation_clients c
                LEFT JOIN users u ON u.code_user = c.commercial_code
                WHERE c.souscription_code = ?
                  AND c.etablissement_code = ? AND c.zone_code = ? AND c.annee_code = ?
                ORDER BY c.date_cautisation DESC
            ");
            $stmtCotis->execute([$item['code_souscription'], Context::etablissement(), Context::zone(), Context::annee()]);
            $cotisations = $stmtCotis->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La souscription demandée est introuvable.");
            return;
        }
        $this->loadView('../views/souscriptions/details.php', [
            'item' => $item,
            'client' => $client,
            'packSouscrit' => $packSouscrit,
            'allPacks' => $allPacks,
            'soldeRestant' => $soldeRestant,
            'joursRestants' => $joursRestants,
            'cotisations' => $cotisations,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($edition)
    {
        $this->requirePermission('GESTIONNAIRE_EDIT_SOUSCRIPTION');

        if (Context::isCommercial()) {
            $this->renderNotFound("Action non autorisée. Les commerciaux ne peuvent pas modifier les souscriptions.");
            return;
        }

        try {
            $id = $this->validator->decrypter($edition);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("La souscription demandée est introuvable.");
                return;
            }

            $modelClient = new ModelClient();
            $clients = $modelClient->getAll();

            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();
            $anneeCode = Context::annee();

            $stmtP = $this->model->getCon()->prepare("SELECT * FROM packs WHERE statut_pack='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?");
            $stmtP->execute([$etabCode, $zoneCode, $anneeCode]);
            $packs = $stmtP->fetchAll(PDO::FETCH_ASSOC);

            $stmtS = $this->model->getCon()->prepare("SELECT * FROM sessions WHERE statut_session='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?");
            $stmtS->execute([$etabCode, $zoneCode, $anneeCode]);
            $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        } catch (Exception $e) {
            $this->renderNotFound("La souscription demandée est introuvable.");
            return;
        }

        $this->loadView('../views/souscriptions/edit.php', [
            'item' => $item,
            'clients' => $clients,
            'packs' => $packs,
            'sessions' => $sessions
        ]);
    }

    public function wizard()
    {
        $this->requirePermission('COMMERCIAL_ADD_SOUSCRIPTION');
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $stmtS = $this->model->getCon()->prepare("SELECT * FROM sessions WHERE statut_session='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?");
        $stmtS->execute([$etabCode, $zoneCode, $anneeCode]);
        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $modelCat = new ModelCategoriePack();
        $categories = $modelCat->getAll();

        $this->loadView('../views/souscriptions/wizard.php', [
            'sessions' => $sessions,
            'categories' => $categories
        ]);
    }

    public function wizardData()
    {
        $this->requirePermission(['COMMERCIAL_ADD_SOUSCRIPTION', 'GESTIONNAIRE_ADD_SOUSCRIPTION']);
        $sessionCode = $_GET['session_code'] ?? '';
        $categorieCode = $_GET['categorie_code'] ?? '';

        if (empty($sessionCode)) {
            $this->json(['status' => 0, 'data' => []]);
            return;
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT p.*, c.libelle_categorie_pack, s.nombre_jour_session,
                   (SELECT COUNT(*) FROM pack_articles pa WHERE pa.pack_code = p.code_pack AND pa.etablissement_code = ? AND pa.zone_code = ? AND pa.annee_code = ?) as nombre_articles,
                   (SELECT COUNT(*) FROM pack_souscriptions ps WHERE ps.pack_code = p.code_pack AND ps.etablissement_code = ? AND ps.zone_code = ? AND ps.annee_code = ?) as nombre_souscriptions
            FROM packs p
            LEFT JOIN categorie_packs c ON c.code_categorie_pack = p.categorie_pack_code
            LEFT JOIN sessions s ON s.code_session = p.session_code AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            WHERE p.session_code = ? AND p.statut_pack = 'actif'
              AND p.etablissement_code = ? AND p.zone_code = ? AND p.annee_code = ?
        ";
        $params = [
            $etabCode, $zoneCode, $anneeCode,
            $etabCode, $zoneCode, $anneeCode,
            $etabCode, $zoneCode, $anneeCode,
            $sessionCode,
            $etabCode, $zoneCode, $anneeCode
        ];

        if (!empty($categorieCode)) {
            $sql .= " AND p.categorie_pack_code = ?";
            $params[] = $categorieCode;
        }

        $sql .= " ORDER BY p.libelle_pack ASC";

        $db = $this->model->getCon();
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $packs = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->json(['status' => 1, 'data' => $packs]);
    }

    public function createWizard()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_ADD_SOUSCRIPTION');

        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (!$data || empty($data['client_code']) || empty($data['pack_codes']) || !is_array($data['pack_codes'])) {
            $this->error('Données de souscription incomplètes (client ou packs manquants)');
            return;
        }

        $userCode = Context::user() ?? '';
        $etabCode = Context::etablissement();
        $zoneCode = $data['zone_code'] ?? Context::zone();
        
        // Priorité : annee_code soumis > Context::annee()
        $anneeCode = !empty($data['annee_code']) ? trim($data['annee_code']) : Context::annee();

        if (empty($anneeCode)) {
            $this->error("L'année d'activité est obligatoire pour finaliser la souscription. Veuillez configurer une année active.");
            return;
        }

        $stmtAnneeCheck = $this->model->getCon()->prepare("SELECT code_annee FROM annees WHERE code_annee = ? LIMIT 1");
        $stmtAnneeCheck->execute([$anneeCode]);
        if (!$stmtAnneeCheck->fetch()) {
            $this->error("L'année d'activité associée est invalide ou introuvable.");
            return;
        }

        $sessionCode = $data['session_code'] ?? '';
        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);

        $packCodes = $data['pack_codes'];

        $inClause = implode(',', array_fill(0, count($packCodes), '?'));
        $stmtP = $this->model->getCon()->prepare("
            SELECT SUM(prix_cotisation_pack) as total_prix 
            FROM packs 
            WHERE code_pack IN ($inClause) AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtP->execute(array_merge($packCodes, [$etabCode, $zoneCode, $anneeCode]));
        $resP = $stmtP->fetch(PDO::FETCH_ASSOC);
        $cotisJour = (float)($resP['total_prix'] ?? 0);

        $stmtS = $this->model->getCon()->prepare("
            SELECT nombre_jour_session 
            FROM sessions 
            WHERE code_session = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtS->execute([$sessionCode, $etabCode, $zoneCode, $anneeCode]);
        $resS = $stmtS->fetch(PDO::FETCH_ASSOC);
        $nbJours = (int)($resS['nombre_jour_session'] ?? 0);
        if ($nbJours <= 0) {
            $this->error("Erreur d'insertion : La session sélectionnée est invalide ou son nombre de jours n'est pas configuré.");
            return;
        }

        $montantTotalPrevu = $cotisJour * $nbJours;

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $data['client_code'],
            'session_code' => $sessionCode,
            'zone_code' => $zoneCode,
            'date_debut_souscription' => date('Y-m-d'),
            'montant_total_prevu' => $montantTotalPrevu,
            'montant_cotisation_journaliere' => $cotisJour,
            'nombre_jour_total' => $nbJours,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createSouscriptionWithMultiplePacks($souscriptionData, $packCodes)) {
            $this->success('Souscription enregistrée avec succès !', [
                'code_souscription' => $codeSouscription,
                'redirect' => RACINE . 'souscription/list'
            ]);
        } else {
            $this->error('Erreur lors de la validation de la souscription.');
        }
    }

    public function wizardSubmit()
    {
        $this->requirePost(false);
        $this->requirePermission('COMMERCIAL_ADD_SOUSCRIPTION');
        $data = $_POST;
        unset($data['csrf_token']);

        $nomClient = trim($data['nom_client'] ?? '');
        $telClient = Validator::cleanPhone($data['telephone_client'] ?? '');
        $emailClient = trim($data['email_client'] ?? '');
        $sexeClient = trim($data['sexe_client'] ?? '');
        $lieuClient = trim($data['lieu_residence_client'] ?? '');
        $sessionCode = $data['session_code'] ?? '';
        $zoneCode = $data['zone_code'] ?? '';

        // Détermination fiable de la zone à partir de la session sélectionnée
        if (!empty($sessionCode)) {
            $stmtSessZ = $this->model->getCon()->prepare("SELECT zone_code FROM sessions WHERE code_session = ? LIMIT 1");
            $stmtSessZ->execute([$sessionCode]);
            $sessZ = $stmtSessZ->fetchColumn();
            if (!empty($sessZ)) {
                $zoneCode = $sessZ;
            }
        }
        if (empty($zoneCode)) {
            $zoneCode = Context::zone();
        }

        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $anneeCode = Context::annee();

        if (empty($userCode) || empty($zoneCode) || empty($etabCode) || empty($anneeCode)) {
            $this->error("Erreur d'enregistrement : La zone, l'année d'exercice, l'utilisateur connecté et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        $rawPacks = $data['packs'] ?? '[]';
        $packCodes = is_array($rawPacks) ? $rawPacks : json_decode($rawPacks, true);

        if (empty($nomClient) || empty($telClient) || empty($sexeClient) || empty($lieuClient)) {
            $this->error('Veuillez remplir toutes les informations du client (Nom, Téléphone, Genre, Lieu de résidence).');
            return;
        }

        if (empty($sessionCode)) {
            $this->error('Veuillez sélectionner une session d\'activité.');
            return;
        }

        if (empty($packCodes) || !is_array($packCodes)) {
            $this->error('Veuillez sélectionner au moins un pack.');
            return;
        }

        $db = $this->model->getCon();

        // 1. DÉTECTION ET ANTI-DOUBLON CLIENT : Vérification si le client existe déjà
        $existingClient = null;
        if (!empty($telClient)) {
            $stmtCheck = $db->prepare("SELECT * FROM clients WHERE telephone_client = ? LIMIT 1");
            $stmtCheck->execute([$telClient]);
            $existingClient = $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        if (!$existingClient && !empty($nomClient) && !empty($lieuClient)) {
            $stmtCheckNom = $db->prepare("SELECT * FROM clients WHERE LOWER(nom_client) = LOWER(?) AND LOWER(lieu_residence_client) = LOWER(?) LIMIT 1");
            $stmtCheckNom->execute([$nomClient, $lieuClient]);
            $existingClient = $stmtCheckNom->fetch(PDO::FETCH_ASSOC);
        }

        if ($existingClient) {
            // REUTILISATION DU CLIENT EXISTANT (Pas de création de doublon)
            $clientCode = $existingClient['code_client'];

            // Mettre à jour les informations secondaires si manquantes
            $updateFields = [];
            if (empty($existingClient['email_client']) && !empty($emailClient)) $updateFields['email_client'] = $emailClient;
            if (empty($existingClient['profession_client']) && !empty($professionClient)) $updateFields['profession_client'] = $professionClient;
            if (!empty($updateFields)) {
                $updateFields['updated_at_client'] = date('Y-m-d H:i:s');
                $modelClient = new ModelClient();
                $modelClient->update($updateFields, (int)$existingClient['id_client']);
            }
        } else {
            // NOUVEAU CLIENT : Création d'une fiche client unique
            $clientCode = $this->validator->generateCode('clients', 'code_client', 'CLI-', 8);
            if (empty($zoneCode)) {
                $stmtDZ = $db->query("SELECT code_zone FROM zones LIMIT 1");
                $dz = $stmtDZ->fetch(PDO::FETCH_ASSOC);
                $zoneCode = $dz['code_zone'] ?? Context::zone();
            }

            $clientData = [
                'code_client' => $clientCode,
                'nom_client' => $nomClient,
                'telephone_client' => $telClient,
                'email_client' => $emailClient,
                'sexe_client' => $sexeClient,
                'lieu_residence_client' => $lieuClient,
                'profession_client' => $professionClient,
                'statut_client' => 'actif',
                'created_at_client' => date('Y-m-d H:i:s'),
                'user_code' => $userCode,
                'etablissement_code' => $etabCode,
                'zone_code' => $zoneCode
            ];
            $modelClient = new ModelClient();
            if (!$modelClient->create($clientData)) {
                $this->error('Erreur lors de la création de la fiche client.');
                return;
            }
        }

        // 2. CRÉATION DE LA SOUSCRIPTION
        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);

        $inClause = implode(',', array_fill(0, count($packCodes), '?'));
        $stmtP = $db->prepare("
            SELECT SUM(prix_cotisation_pack) as total_prix 
            FROM packs 
            WHERE code_pack IN ($inClause) AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtP->execute(array_merge($packCodes, [$etabCode, $zoneCode, $anneeCode]));
        $resP = $stmtP->fetch(PDO::FETCH_ASSOC);
        $cotisJour = (float)($resP['total_prix'] ?? 0);

        $stmtS = $db->prepare("
            SELECT nombre_jour_session 
            FROM sessions 
            WHERE code_session = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtS->execute([$sessionCode, $etabCode, $zoneCode, $anneeCode]);
        $resS = $stmtS->fetch(PDO::FETCH_ASSOC);
        $nbJours = (int)($resS['nombre_jour_session'] ?? 0);
        if ($nbJours <= 0) {
            $this->error("Erreur d'insertion : La session sélectionnée est invalide ou son nombre de jours n'est pas configuré.");
            return;
        }

        $montantTotalPrevu = $cotisJour * $nbJours;

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $clientCode,
            'session_code' => $sessionCode,
            'zone_code' => $zoneCode,
            'date_debut_souscription' => date('Y-m-d'),
            'montant_total_prevu' => $montantTotalPrevu,
            'montant_cotisation_journaliere' => $cotisJour,
            'nombre_jour_total' => $nbJours,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createSouscriptionWithMultiplePacks($souscriptionData, $packCodes)) {
            $msgSuccess = $existingClient 
                ? "Souscription rattachée au client existant '{$existingClient['nom_client']}' ($clientCode) avec succès !"
                : "Nouveau client créé ($clientCode) et souscription enregistrée avec succès !";
            $this->success($msgSuccess, [
                'code_souscription' => $codeSouscription,
                'redirect' => RACINE . 'souscription/list'
            ]);
        } else {
            $this->error('Erreur lors de la validation de la souscription.');
        }
    }

    /**
     * Affiche l'interface de ressouscription pour un client existant
     */
    public function ressouscription()
    {
        $this->requirePermission(['COMMERCIAL_ADD_SOUSCRIPTION', 'GESTIONNAIRE_ADD_SOUSCRIPTION']);

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $stmtS = $this->model->getCon()->prepare("SELECT * FROM sessions WHERE statut_session='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?");
        $stmtS->execute([$etabCode, $zoneCode, $anneeCode]);
        $sessions = $stmtS->fetchAll(PDO::FETCH_ASSOC);

        $modelCat = new ModelCategoriePack();
        $categories = $modelCat->getAll();

        $clientCode = trim($_GET['client_code'] ?? ($_GET['client'] ?? ''));
        $preselectedClient = null;

        if (!empty($clientCode)) {
            $stmt = $this->model->getCon()->prepare("
                SELECT c.id_client, c.code_client, c.nom_client, c.telephone_client, c.sexe_client, 
                       c.lieu_residence_client, c.email_client, c.profession_client, c.numero_cni, c.statut_client,
                       (SELECT COUNT(*) FROM souscriptions sub WHERE sub.client_code = c.code_client) as total_souscriptions
                FROM clients c
                WHERE (c.code_client = ? OR c.id_client = ?)
                  AND c.etablissement_code = ?
                LIMIT 1
            ");
            $stmt->execute([$clientCode, $clientCode, $etabCode]);
            $preselectedClient = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        }

        $this->loadView('../views/souscriptions/ressouscription.php', [
            'sessions' => $sessions,
            'categories' => $categories,
            'preselectedClient' => $preselectedClient
        ]);
    }

    /**
     * API: Recherche prédictive AJAX de clients existants pour Select2
     */
    public function apiClientsSearch()
    {
        $this->requirePermission(['COMMERCIAL_ADD_SOUSCRIPTION', 'GESTIONNAIRE_ADD_SOUSCRIPTION', 'COMMERCIAL_VIEW_OWN_CLIENTS', 'GESTIONNAIRE_VIEW_ALL_CLIENTS']);
        $q = trim($this->get('q') ?? ($this->post('q') ?? ''));

        if (empty($q)) {
            $this->json(['results' => []]);
            return;
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $userCode = Context::user();

        $sql = "
            SELECT c.id_client, c.code_client, c.nom_client, c.telephone_client, c.sexe_client, 
                   c.lieu_residence_client, c.email_client, c.profession_client, c.numero_cni, c.statut_client,
                   (SELECT COUNT(*) FROM souscriptions sub WHERE sub.client_code = c.code_client) as total_souscriptions
            FROM clients c
            WHERE c.etablissement_code = ?
              AND (
                c.nom_client LIKE ? OR 
                c.telephone_client LIKE ? OR 
                c.code_client LIKE ? OR 
                c.numero_cni LIKE ?
              )
        ";
        $params = [$etabCode, "%$q%", "%$q%", "%$q%", "%$q%"];

        if (Context::isCommercial()) {
            $sql .= " AND (c.user_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.user_code = ?))";
            $params[] = $userCode;
            $params[] = $userCode;
        } elseif (Context::isGestionnaire() && !empty($zoneCode)) {
            $sql .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub2 WHERE sub2.client_code = c.code_client AND sub2.zone_code = ?))";
            $params[] = $zoneCode;
            $params[] = $zoneCode;
        }

        $sql .= " ORDER BY c.created_at_client DESC LIMIT 25";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $results = [];
        foreach ($clients as $c) {
            $nom = htmlspecialchars($c['nom_client'] ?? 'Client');
            $tel = htmlspecialchars($c['telephone_client'] ?? '');
            $code = htmlspecialchars($c['code_client'] ?? '');
            $cni = !empty($c['numero_cni']) ? " | CNI: " . htmlspecialchars($c['numero_cni']) : "";
            $nbSous = (int)$c['total_souscriptions'];
            $badgeSous = $nbSous > 0 ? " ($nbSous souscription" . ($nbSous > 1 ? "s" : "") . ")" : " (Nouveau)";

            $results[] = [
                'id' => $c['code_client'],
                'text' => "{$nom} - {$tel} [{$code}]{$cni}{$badgeSous}",
                'client' => $c
            ];
        }

        $this->json(['results' => $results]);
    }

    /**
     * Traite la soumission de ressouscription pour un client existant sans contrainte
     */
    public function processRessouscription()
    {
        $this->requirePost(false);
        $this->requirePermission(['COMMERCIAL_ADD_SOUSCRIPTION', 'GESTIONNAIRE_ADD_SOUSCRIPTION']);

        $data = $_POST;
        unset($data['csrf_token']);

        $clientCode = trim($data['client_code'] ?? '');
        $sessionCode = trim($data['session_code'] ?? '');
        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $anneeCode = Context::annee();
        $zoneCode = $data['zone_code'] ?? Context::zone();

        if (empty($clientCode)) {
            $this->error('Veuillez sélectionner un client existant.');
            return;
        }

        if (empty($sessionCode)) {
            $this->error('Veuillez sélectionner une session d\'activité.');
            return;
        }

        $rawPacks = $data['packs'] ?? '[]';
        $packCodes = is_array($rawPacks) ? $rawPacks : json_decode($rawPacks, true);

        if (empty($packCodes) || !is_array($packCodes)) {
            $this->error('Veuillez sélectionner au moins un pack.');
            return;
        }

        $db = $this->model->getCon();

        // 1. Vérifier l'existence du client
        $stmtC = $db->prepare("SELECT * FROM clients WHERE code_client = ? AND etablissement_code = ? LIMIT 1");
        $stmtC->execute([$clientCode, $etabCode]);
        $clientExist = $stmtC->fetch(PDO::FETCH_ASSOC);

        if (!$clientExist) {
            $this->error('Client introuvable ou non autorisé.');
            return;
        }

        // 2. Déterminer la zone de la session
        if (!empty($sessionCode)) {
            $stmtSessZ = $db->prepare("SELECT zone_code FROM sessions WHERE code_session = ? LIMIT 1");
            $stmtSessZ->execute([$sessionCode]);
            $sessZ = $stmtSessZ->fetchColumn();
            if (!empty($sessZ)) {
                $zoneCode = $sessZ;
            }
        }
        if (empty($zoneCode)) {
            $zoneCode = Context::zone();
        }

        if (empty($userCode) || empty($zoneCode) || empty($etabCode) || empty($anneeCode)) {
            $this->error("Erreur d'enregistrement : La zone, l'année d'exercice, l'utilisateur connecté et l'établissement sont obligatoires.");
            return;
        }

        // 3. Calculer le prix total par jour des packs sélectionnés
        $inClause = implode(',', array_fill(0, count($packCodes), '?'));
        $stmtP = $db->prepare("
            SELECT SUM(prix_cotisation_pack) as total_prix 
            FROM packs 
            WHERE code_pack IN ($inClause) AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtP->execute(array_merge($packCodes, [$etabCode, $zoneCode, $anneeCode]));
        $resP = $stmtP->fetch(PDO::FETCH_ASSOC);
        $cotisJour = (float)($resP['total_prix'] ?? 0);

        // 4. Obtenir la durée en jours de la session
        $stmtS = $db->prepare("
            SELECT nombre_jour_session 
            FROM sessions 
            WHERE code_session = ? AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtS->execute([$sessionCode, $etabCode, $zoneCode, $anneeCode]);
        $resS = $stmtS->fetch(PDO::FETCH_ASSOC);
        $nbJours = (int)($resS['nombre_jour_session'] ?? 0);

        if ($nbJours <= 0) {
            $this->error("La session sélectionnée est invalide ou son nombre de jours n'est pas configuré.");
            return;
        }

        $montantTotalPrevu = $cotisJour * $nbJours;
        $codeSouscription = $this->validator->generateCode('souscriptions', 'code_souscription', 'SUB-', 8);

        $souscriptionData = [
            'code_souscription' => $codeSouscription,
            'client_code' => $clientCode,
            'session_code' => $sessionCode,
            'zone_code' => $zoneCode,
            'date_debut_souscription' => date('Y-m-d'),
            'montant_total_prevu' => $montantTotalPrevu,
            'montant_cotisation_journaliere' => $cotisJour,
            'nombre_jour_total' => $nbJours,
            'nombre_jour_cotise' => 0,
            'montant_total_cotise' => 0,
            'statut_distribution' => 'En attente',
            'statut_souscription' => 'valide',
            'user_code' => $userCode,
            'etablissement_code' => $etabCode,
            'annee_code' => $anneeCode,
            'created_at_souscription' => date('Y-m-d H:i:s')
        ];

        if ($this->model->createSouscriptionWithMultiplePacks($souscriptionData, $packCodes)) {
            $nomClient = htmlspecialchars($clientExist['nom_client'] ?? '');
            $this->success("Ressouscription enregistrée avec succès pour {$nomClient} ($codeSouscription) !", [
                'code_souscription' => $codeSouscription,
                'redirect' => RACINE . 'cautisation-payment/situation/' . $codeSouscription
            ]);
        } else {
            $this->error('Erreur lors de la création de la ressouscription.');
        }
    }
}
