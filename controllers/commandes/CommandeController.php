<?php

class CommandeController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelCommande();
    }

    public function list()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        $clientModel = new ModelClient();
        if (!empty($pressingCode)) {
            $clients = $clientModel->getByPressing($pressingCode);
        } else {
            $clients = $clientModel->getAll();
        }

        $pressingModel = new ModelPressing();
        $pressings     = $pressingModel->getByStatus('actif');

        $articleModel  = new ModelArticle();
        if (!empty($pressingCode)) {
            $articles = $articleModel->getAllWithCategory($pressingCode);
        } else {
            $articles = $articleModel->getAllWithCategory();
        }

        $serviceModel  = new ModelService();
        if (!empty($pressingCode)) {
            $services = $serviceModel->getByPressing($pressingCode);
        } else {
            $services = $serviceModel->getByStatus('actif');
        }

        $tarifModel    = new ModelTarifArticle();
        if (!empty($pressingCode)) {
            $tarifs = $tarifModel->getByPressing($pressingCode);
        } else {
            $tarifs = $tarifModel->getAllWithDetails();
        }

        $this->loadView('../views/commandes/list.php', [
            'clients'   => $clients,
            'pressings' => $pressings,
            'articles'  => $articles,
            'services'  => $services,
            'tarifs'    => $tarifs
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $commandes = $this->model->getByPressingWithDetails($pressingCode);
        } else {
            $commandes = $this->model->getAllWithDetails();
        }

        $data = [];

        foreach ($commandes as $c) {
            $idCrypte = $this->validator->crypter($c['id_commande']);
            $statutSuivi = $c['statut_suivi_commande'] ?? 'creee';
            $statutSuiviLabel = STATUTS::SUIVI_COMMANDES[$statutSuivi] ?? ucfirst(str_replace('_', ' ', $statutSuivi));
            $typeCmd = $c['type_commande'] ?? 'detaillee';

            $data[] = [
                'code' => $c['code_commande'],
                'type' => $typeCmd,
                'type_label' => ($typeCmd === 'colis') ? 'Sac / Colis (' . ($c['nb_sacs_colis'] ?? 1) . ')' : 'Détaillée',
                'client' => $c['nom_client'] ?? ($c['client_code'] ?? 'Client'),
                'client_tel' => $c['telephone_client'] ?? '-',
                'pressing' => $c['libelle_pressing'] ?? ($c['pressing_code'] ?? '-'),
                'date' => !empty($c['created_at_commande']) ? date('d/m/Y H:i', strtotime($c['created_at_commande'])) : '-',
                'montant' => (float)($c['montant_total_commande'] ?? 0),
                'statut_suivi' => $statutSuivi,
                'statut_suivi_label' => $statutSuiviLabel,
                'statut' => $c['statut_commande'],
                'id' => $c['id_commande'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if ($this->isLivreur()) {
            $this->error('Action non autorisée : un livreur ne peut pas créer de commande.', 403);
            return;
        }

        $clientCode = $this->post('client_code');
        if (empty($clientCode)) {
            $this->error('Veuillez sélectionner un client');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode === null) {
            $pressingCode = $this->post('pressing_code');
        }

        if (empty($pressingCode)) {
            $this->error('Veuillez sélectionner un pressing');
            return;
        }

        $typeCommande = in_array($this->post('type_commande'), ['colis', 'detaillee']) ? $this->post('type_commande') : 'detaillee';
        $nbSacsColis  = ($typeCommande === 'colis') ? max(1, (int)$this->post('nb_sacs_colis', 1)) : 1;

        $code = $this->post('code') ?: $this->validator->generateCode(TABLES::COMMANDES, 'code_commande', 'CMD-', 6);
        $userCode = Context::user() ?? '';

        $fraisCollecte = (float)($this->post('frais_collecte_commande') ?: 0);
        $fraisLivraison = (float)($this->post('frais_livraison_commande') ?: 0);
        $remise = (float)($this->post('remise_commande') ?: 0);

        $itemsJson = $this->post('items_json');
        $items = [];
        if (!empty($itemsJson)) {
            $items = json_decode($itemsJson, true) ?: [];
        }

        $sousTotalArticles = 0;
        if ($typeCommande === 'detaillee' && !empty($items)) {
            foreach ($items as $item) {
                $qty = max(1, (int)($item['quantite'] ?? 1));
                $pu  = max(0, (float)($item['prix_unitaire'] ?? 0));
                $sousTotalArticles += ($qty * $pu);
            }
            $montantTotal = $sousTotalArticles + $fraisCollecte + $fraisLivraison - $remise;
        } else {
            $montantTotal = (float)($this->post('montant_total_commande') ?: 0);
        }

        // Si commande colis créée par pressing avant inventaire, montant peut être 0 et statut 'recue_pressing'
        $statutSuivi = ($typeCommande === 'colis' && $montantTotal == 0) ? 'recue_pressing' : 'creee';

        $data = [
            'code_commande' => $code,
            'pressing_code' => $pressingCode,
            'client_code' => $clientCode,
            'user_code' => $userCode,
            'type_commande' => $typeCommande,
            'nb_sacs_colis' => $nbSacsColis,
            'remise_commande' => $remise,
            'frais_collecte_commande' => $fraisCollecte,
            'frais_livraison_commande' => $fraisLivraison,
            'montant_total_commande' => max(0, $montantTotal),
            'adresse_livraison_commande' => $this->post('adresse_livraison_commande') ?? '',
            'observation_commande' => $this->post('observation_commande') ?? '',
            'date_livraison_commande' => $this->post('date_livraison_commande') ?: null,
            'statut_commande' => 'actif',
            'statut_suivi_commande' => $statutSuivi,
            'created_at_commande' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            // Insertion des articles détaillés dans commande_details
            if ($typeCommande === 'detaillee' && !empty($items)) {
                $db = $this->model->getCon();
                $stmtDet = $db->prepare("
                    INSERT INTO " . TABLES::COMMANDE_DETAILS . " 
                    (code_commande_detail, commande_code, article_code, service_code, quantite_commande_detail, prix_unitaire_commande_detail, sous_total_commande_detail, created_at_commande_detail)
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                ");

                foreach ($items as $idx => $it) {
                    $detCode = 'CMD-DET-' . strtoupper(substr(uniqid(), -6)) . '-' . ($idx + 1);
                    $artCode = $it['article_code'] ?? '';
                    $srvCode = $it['service_code'] ?? '';
                    $qty     = max(1, (int)($it['quantite'] ?? 1));
                    $pu      = max(0, (float)($it['prix_unitaire'] ?? 0));
                    $st      = $qty * $pu;

                    if (!empty($artCode) && !empty($srvCode)) {
                        $stmtDet->execute([$detCode, $code, $artCode, $srvCode, $qty, $pu, $st]);
                    }
                }
            }

            // Notification autonome
            try {
                $pressingObj = (new ModelPressing())->getByCode($pressingCode);
                $pressingName = $pressingObj['libelle_pressing'] ?? 'Le pressing';
                NotificationService::notifyOrderCreated($clientCode, $code, $pressingName);
            } catch (Exception $e) {
                error_log('[NotificationService Error] ' . $e->getMessage());
            }

            $this->success('Commande créée avec succès !', [
                'commande_code' => $code,
                'editId' => $this->validator->crypter($this->model->getCon()->lastInsertId())
            ]);
        } else {
            $this->error('Erreur lors de la création de la commande');
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        $item = null;
        $id = null;

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getWithDetails($id);
        } catch (Exception $e) {
            $item = null;
        }

        if (!$item) {
            $item = $this->model->getByCodeWithDetails($details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        if (!$item && is_numeric($details)) {
            $item = $this->model->getWithDetails((int)$details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $encryptedId = $this->validator->crypter($id);

        $clientModel = new ModelClient();
        $clients = $clientModel->getByStatus('actif');

        $pressingModel = new ModelPressing();
        $pressings = $pressingModel->getByStatus('actif');

        $this->loadView('../views/commandes/edit.php', [
            'order' => $item,
            'clients' => $clients,
            'pressings' => $pressings,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int) $this->post('id_commande');

        if (!$id) {
            $this->error('ID de commande requis');
            return;
        }

        $current = $this->model->getById($id);
        if (!$current) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($current['pressing_code'] ?? '');

        $statut = in_array($this->post('statut_commande'), ['actif', 'inactif']) ? $this->post('statut_commande') : 'actif';
        $statutSuivi = $this->post('statut_suivi_commande') ?: ($current['statut_suivi_commande'] ?? 'creee');

        $data = [
            'id' => $id,
            'client_code' => $this->post('client_code') ?: $current['client_code'],
            'remise_commande' => (float)($this->post('remise_commande') ?? ($current['remise_commande'] ?? 0)),
            'frais_collecte_commande' => (float)($this->post('frais_collecte_commande') ?? ($current['frais_collecte_commande'] ?? 0)),
            'frais_livraison_commande' => (float)($this->post('frais_livraison_commande') ?? ($current['frais_livraison_commande'] ?? 0)),
            'montant_total_commande' => (float)($this->post('montant_total_commande') ?? ($current['montant_total_commande'] ?? 0)),
            'adresse_livraison_commande' => $this->post('adresse_livraison_commande') ?? ($current['adresse_livraison_commande'] ?? ''),
            'observation_commande' => $this->post('observation_commande') ?? ($current['observation_commande'] ?? ''),
            'statut_suivi_commande' => $statutSuivi,
            'statut_commande' => $statut,
            'updated_at_commande' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Commande modifiée avec succès !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la modification de la commande');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Commande introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $item = null;
        $id = null;

        // 1. Décryptage ID
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getWithDetails($id);
        } catch (Exception $e) {
            $item = null;
        }

        // 2. Recherche par code commande direct
        if (!$item) {
            $item = $this->model->getByCodeWithDetails($details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        // 3. Recherche par ID direct
        if (!$item && is_numeric($details)) {
            $item = $this->model->getWithDetails((int)$details);
            if ($item) {
                $id = $item['id_commande'];
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $encryptedId = $this->validator->crypter($id);

        $ligneModel = new ModelCommandeDetail();
        $lignes = $ligneModel->getByCommande($item['code_commande']);

        // Livreurs disponibles pour assignation
        $livreurModel = new ModelLivreur();
        $livreurs = $livreurModel->getByStatus('actif');

        // Missions rattachées à cette commande
        $missions = [];
        try {
            $stmtM = $this->model->getCon()->prepare("
                SELECT m.*, l.nom_livreur, l.telephone_livreur 
                FROM " . TABLES::MISSIONS . " m
                LEFT JOIN " . TABLES::LIVREURS . " l ON m.livreur_code = l.code_livreur
                WHERE m.commande_code = ?
                ORDER BY m.id_mission DESC
            ");
            $stmtM->execute([$item['code_commande']]);
            $missions = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            $missions = [];
        }

        // Articles, Services et Tarifs pour le devis après inventaire
        $articleModel = new ModelArticle();
        $pressingCode = $item['pressing_code'] ?? '';
        $articles     = [];
        if ($pressingCode !== '') {
            $articles = $articleModel->getByPressing($pressingCode);
        }
        if (empty($articles)) {
            $articles = $articleModel->getByStatus('actif');
        }

        $serviceModel = new ModelService();
        $services     = $serviceModel->getByStatus('actif');

        $tarifModel   = new ModelTarifArticle();
        $tarifs       = $tarifModel->getByPressing($pressingCode);
        $allTarifs    = $tarifModel->getAll();

        $this->loadView('../views/commandes/details.php', [
            'order'       => $item,
            'encryptedId' => $encryptedId,
            'lignes'      => $lignes,
            'livreurs'    => $livreurs,
            'missions'    => $missions,
            'articles'    => $articles,
            'services'    => $services,
            'tarifs'      => $tarifs,
            'allTarifs'   => $allTarifs
        ]);
    }

    public function accepter()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'accepter des commandes');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'acceptee', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderAccepted(
                $item['client_code'],
                $code,
                $item['libelle_pressing'] ?? 'Le pressing'
            );
            $this->success('Commande acceptée avec succès !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'acceptation');
        }
    }

    public function refuser()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');
        $motif = trim($this->post('motif', ''));

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'refusee', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderRejected(
                $item['client_code'],
                $code,
                $item['libelle_pressing'] ?? 'Le pressing',
                $motif ?: null
            );
            $this->success('Commande refusée', ['reload' => true]);
        } else {
            $this->error('Erreur lors du refus');
        }
    }

    public function saisirDevisColis()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'saisir des devis au sac');

        $itemsJson = $this->post('items_json');
        $items = [];
        if (!empty($itemsJson)) {
            $items = json_decode($itemsJson, true) ?: [];
        }

        $fraisCollecte = (float)($item['frais_collecte_commande'] ?? 0);
        $fraisLivraison = (float)($item['frais_livraison_commande'] ?? 0);
        $remise = (float)($item['remise_commande'] ?? 0);

        $sousTotalArticles = 0;
        if (!empty($items)) {
            foreach ($items as $it) {
                $qty = max(1, (int)($it['quantite'] ?? 1));
                $pu  = max(0, (float)($it['prix_unitaire'] ?? 0));
                $sousTotalArticles += ($qty * $pu);
            }
            $montant = $sousTotalArticles + $fraisCollecte + $fraisLivraison - $remise;
        } else {
            $montant = (float) $this->post('montant_total');
        }

        if ($montant <= 0) {
            $this->error('Veuillez ajouter au moins un article ou renseigner un montant valide');
            return;
        }

        $db = $this->model->getCon();

        // 1. Insertion des articles inventoriés dans commande_details
        if (!empty($items)) {
            $db->prepare("DELETE FROM " . TABLES::COMMANDE_DETAILS . " WHERE commande_code = ?")->execute([$code]);

            $stmtDet = $db->prepare("
                INSERT INTO " . TABLES::COMMANDE_DETAILS . " 
                (code_commande_detail, commande_code, article_code, service_code, quantite_commande_detail, prix_unitaire_commande_detail, sous_total_commande_detail, created_at_commande_detail)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            foreach ($items as $idx => $it) {
                $detCode = 'CMD-DET-' . strtoupper(substr(uniqid(), -6)) . '-' . ($idx + 1);
                $artCode = $it['article_code'] ?? '';
                $srvCode = $it['service_code'] ?? '';
                $qty     = max(1, (int)($it['quantite'] ?? 1));
                $pu      = max(0, (float)($it['prix_unitaire'] ?? 0));
                $st      = $qty * $pu;

                if (!empty($artCode) && !empty($srvCode)) {
                    $stmtDet->execute([$detCode, $code, $artCode, $srvCode, $qty, $pu, $st]);
                }
            }
        }

        // 2. Mise à jour de la commande
        $sql = "UPDATE " . TABLES::COMMANDES . " SET montant_total_commande = ?, statut_suivi_commande = 'prix_a_valider', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $db->prepare($sql);
        if ($stmt->execute([$montant, $code])) {
            // Notification au client pour validation du devis
            NotificationService::notifyColisPriceToConfirm(
                $item['client_code'],
                $code,
                $montant,
                $item['libelle_pressing'] ?? 'Le pressing'
            );
            $this->success('Devis enregistré avec succès ! Le client a été notifié pour confirmation.', ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du devis');
        }
    }

    public function lancerTraitement()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'lancer le traitement du linge');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'en_traitement', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyProcessingStarted($item['client_code'], $code);
            $this->success('Traitement du linge démarré !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function marquerPrete()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');

        if (!$code) {
            $this->error('Code commande requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'marquer la commande comme prête');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'prete', updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        if ($stmt->execute([$code])) {
            // Notification au client
            NotificationService::notifyOrderReady($item['client_code'], $code);
            $this->success('Commande marquée comme prête !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function assignerLivreur()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $code = $this->post('code_commande');
        $livreurCode = $this->post('livreur_code');
        $typeMission = $this->post('type_mission') ?: 'livraison'; // 'collecte' ou 'livraison'

        if (!$code || !$livreurCode) {
            $this->error('Commande et livreur requis');
            return;
        }

        $item = $this->model->getByCodeWithDetails($code);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'assigner des livreurs');

        // Récupérer le nom du livreur
        $livreurModel = new ModelLivreur();
        $livreur = $this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $livreurCode);
        $nomLivreur = $livreur ? ($livreur['nom_livreur'] ?? 'Le livreur') : 'Le livreur';

        // Créer la mission
        $missionModel = new ModelMission();
        $missionCode = $this->validator->generateCode(TABLES::MISSIONS, 'code_mission', 'MIS-', 6);
        $missionData = [
            'code_mission' => $missionCode,
            'commande_code' => $code,
            'livreur_code' => $livreurCode,
            'type_mission' => $typeMission,
            'adresse_mission' => $item['adresse_client'] ?? '',
            'statut_mission' => 'en_attente',
            'created_at_mission' => date('Y-m-d H:i:s')
        ];
        $missionModel->create($missionData);

        // Mettre à jour le statut de suivi de la commande
        $nouveauStatut = ($typeMission === 'collecte') ? 'collecte_programmee' : 'en_livraison';
        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = ?, updated_at_commande = NOW() WHERE code_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute([$nouveauStatut, $code]);

        // Notifications
        if ($typeMission === 'collecte') {
            NotificationService::notifyDriverAssigned($item['client_code'], $code, $nomLivreur);
        } else {
            NotificationService::notifyDeliveryEnRoute($item['client_code'], $code, $nomLivreur);
        }

        $this->success("Livreur {$nomLivreur} assigné avec succès !", ['reload' => true]);
    }

    public function transition()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int) $this->post('id_commande');
        $next = $this->post('statut_suivi_commande');

        if (!$id || !$next) {
            $this->error('Identifiant et statut requis');
            return;
        }

        $allowed = array_keys(STATUTS::SUIVI_COMMANDES);

        if (!in_array($next, $allowed, true)) {
            $this->error('Statut de suivi non reconnu');
            return;
        }

        $item = $this->model->getWithDetails($id);
        if (!$item) {
            $this->error('Commande introuvable');
            return;
        }

        $this->requirePressingAccess($item['pressing_code'] ?? '');
        $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'mettre à jour le suivi de cette commande');

        $sql = "UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = ?, updated_at_commande = NOW() WHERE id_commande = ?";
        $stmt = $this->model->getCon()->prepare($sql);

        if ($stmt->execute([$next, $id])) {
            // Déclenchement automatique de la notification selon le statut
            $clientCode = $item['client_code'] ?? '';
            $orderCode = $item['code_commande'] ?? '';
            $pressingName = $item['libelle_pressing'] ?? 'Le pressing';

            switch ($next) {
                case 'acceptee':
                    NotificationService::notifyOrderAccepted($clientCode, $orderCode, $pressingName);
                    break;
                case 'refusee':
                    NotificationService::notifyOrderRejected($clientCode, $orderCode, $pressingName);
                    break;
                case 'collectee':
                    NotificationService::notifyCollectionCompleted($clientCode, $orderCode);
                    break;
                case 'recue_pressing':
                    NotificationService::notifyReceivedAtPressing($clientCode, $orderCode, $pressingName);
                    break;
                case 'en_traitement':
                    NotificationService::notifyProcessingStarted($clientCode, $orderCode);
                    break;
                case 'prete':
                    NotificationService::notifyOrderReady($clientCode, $orderCode);
                    break;
                case 'en_livraison':
                    NotificationService::notifyDeliveryEnRoute($clientCode, $orderCode);
                    break;
                case 'livree':
                    NotificationService::notifyOrderDelivered($clientCode, $orderCode);
                    break;
            }

            $this->success("Statut de la commande mis à jour vers {$next}", ['reload' => true]);
        } else {
            $this->error('Erreur lors de la mise à jour');
        }
    }

    public function ticket($details)
    {
        $this->requireAuth();
        try {
            $commandeId = $this->validator->decrypter($details);
            $commande = $this->model->getWithDetails($commandeId);
            if (!$commande) {
                header('Location: ' . RACINE . 'commande/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'commande/list');
            exit();
        }

        $this->requirePressingAccess($commande['pressing_code'] ?? '');

        // Récupérer les lignes de commande
        $detailModel = new ModelCommandeDetail();
        $commande['details'] = $detailModel->getByCommandeCode($commande['code_commande']);

        // Récupérer le mode de paiement
        $stmtPay = $this->model->getCon()->prepare("SELECT * FROM " . TABLES::PAIEMENTS . " WHERE commande_code = ? ORDER BY id_paiement DESC LIMIT 1");
        $stmtPay->execute([$commande['code_commande']]);
        $paiement = $stmtPay->fetch(PDO::FETCH_ASSOC);

        $this->loadView('../views/commandes/ticket.php', [
            'commande' => $commande,
            'paiement' => $paiement
        ]);
    }
}