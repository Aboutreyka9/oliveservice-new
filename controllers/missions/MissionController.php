<?php

class MissionController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelMission();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/missions/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            $missions = $livreurCode ? $this->model->getByLivreur($livreurCode) : [];
        } else {
            $missions = (new ModelHome())->getLivreurMissions(null, 100);
        }

        $data = [];

        foreach ($missions as $m) {
            $idCrypte = $this->validator->crypter($m['id_mission']);
            $lat = $m['latitude_mission'] ?? ($m['latitude_client'] ?? '');
            $lng = $m['longitude_mission'] ?? ($m['longitude_client'] ?? '');
            $adr = !empty($m['adresse_mission']) ? $m['adresse_mission'] : ($m['adresse_client'] ?? 'Abidjan');
            $gpsUrl = ($lat && $lng) ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}" : "https://www.google.com/maps/search/?api=1&query=" . urlencode($adr . ' Abidjan');

            $data[] = [
                'code' => $m['code_mission'],
                'commande' => $m['commande_code'] ?? '',
                'livreur' => $m['nom_livreur'] ?? ($m['livreur_code'] ?? ''),
                'type' => $m['type_mission'] ?? '',
                'adresse' => $adr,
                'statut' => $m['statut_mission'],
                'id' => $m['id_mission'],
                'editId' => $idCrypte,
                'gpsUrl' => $gpsUrl,
                'telephone' => $m['telephone_client'] ?? ''
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if ($this->isLivreur()) {
            $this->error('Action non autorisée : un livreur ne peut pas créer de mission.', 403);
            return;
        }

        $notEmpty = Validator::validateRequiredFields(['commande_code' => $_POST['commande_code'] ?? '', 'type_mission' => $_POST['type_mission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_mission') ?: $this->validator->generateCode(TABLES::MISSIONS, 'code_mission', 'MIS-', 6);
        if ($this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $code)) {
            $this->error('Ce code mission existe déjà!');
            return;
        }

        $commandeCode = $this->post('commande_code');
        $adresseMission = $this->post('adresse_mission') ?? '';
        $latMission = $this->post('latitude_mission') ?? null;
        $lngMission = $this->post('longitude_mission') ?? null;

        // Récupération automatique de l'adresse et des coordonnées GPS réelles de la commande
        if (empty($adresseMission) || empty($latMission) || empty($lngMission)) {
            $db = $this->model->getCon();
            $stmtC = $db->prepare("
                SELECT c.adresse_livraison_commande, c.latitude_livraison, c.longitude_livraison,
                       cl.adresse_client, cl.latitude_client, cl.longitude_client
                FROM " . TABLES::COMMANDES . " c
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.code_commande = ? LIMIT 1
            ");
            $stmtC->execute([$commandeCode]);
            $cRow = $stmtC->fetch(PDO::FETCH_ASSOC);

            if ($cRow) {
                if (empty($adresseMission)) {
                    $adresseMission = !empty($cRow['adresse_livraison_commande']) ? $cRow['adresse_livraison_commande'] : ($cRow['adresse_client'] ?? '');
                }
                if (empty($latMission) || (float)$latMission == 0) {
                    $latMission = (!empty($cRow['latitude_livraison']) && (float)$cRow['latitude_livraison'] != 0) ? $cRow['latitude_livraison'] : ($cRow['latitude_client'] ?? null);
                }
                if (empty($lngMission) || (float)$lngMission == 0) {
                    $lngMission = (!empty($cRow['longitude_livraison']) && (float)$cRow['longitude_livraison'] != 0) ? $cRow['longitude_livraison'] : ($cRow['longitude_client'] ?? null);
                }
            }
        }

        $data = [
            'code_mission' => $code,
            'commande_code' => $commandeCode,
            'livreur_code' => $this->post('livreur_code') ?? '',
            'type_mission' => $this->post('type_mission'),
            'adresse_mission' => $adresseMission,
            'latitude_mission' => $latMission,
            'longitude_mission' => $lngMission,
            'observation_mission' => $this->post('observation_mission') ?? '',
            'statut_mission' => 'en_attente',
            'created_at_mission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            if (!empty($data['livreur_code'])) {
                NotificationService::notifyLivreur(
                    $data['livreur_code'],
                    'mission.assignee',
                    'Nouvelle mission assignée',
                    "La mission #{$code} (" . ($data['type_mission'] === 'collecte' ? 'Collecte' : 'Livraison') . ") vous a été assignée pour la commande #{$data['commande_code']}.",
                    $data['commande_code']
                );
            }
            $this->success('Mission ajoutée avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['commande_code' => $_POST['commande_code'] ?? '', 'type_mission' => $_POST['type_mission'] ?? '', 'id_mission' => $_POST['id_mission'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $id = (int) $this->post('id_mission');
        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Mission introuvable!');
            return;
        }

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            if ($livreurCode === null || ($item['livreur_code'] ?? '') !== $livreurCode) {
                $this->error('Accès refusé : vous n\'êtes pas assigné à cette mission', 403);
                return;
            }
        }

        $statut = in_array($this->post('statut_mission'), STATUTS::MISSIONS) ? $this->post('statut_mission') : 'en_attente';

        $data = [
            'id_mission' => $id,
            'commande_code' => $this->post('commande_code'),
            'livreur_code' => $this->post('livreur_code') ?? '',
            'type_mission' => $this->post('type_mission'),
            'adresse_mission' => $this->post('adresse_mission') ?? '',
            'latitude_mission' => $this->post('latitude_mission') ?? null,
            'longitude_mission' => $this->post('longitude_mission') ?? null,
            'observation_mission' => $this->post('observation_mission') ?? '',
            'statut_mission' => $statut,
            'updated_at_mission' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            if (!empty($data['livreur_code']) && ($item['livreur_code'] ?? '') !== $data['livreur_code']) {
                NotificationService::notifyLivreur(
                    $data['livreur_code'],
                    'mission.assignee',
                    'Nouvelle mission assignée',
                    "La mission #{$item['code_mission']} (" . ($data['type_mission'] === 'collecte' ? 'Collecte' : 'Livraison') . ") vous a été assignée pour la commande #{$data['commande_code']}.",
                    $data['commande_code']
                );
            }
            $this->success('Mission modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->isLivreur()) {
                $item = $this->model->getById($id);
                $livreurCode = $this->getCurrentLivreurCode();
                if ($livreurCode === null || ($item['livreur_code'] ?? '') !== $livreurCode) {
                    $this->json(['status' => 0, 'message' => 'Accès refusé : vous n\'êtes pas assigné à cette mission'], 403);
                    return;
                }
            }

            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Mission introuvable!');
        }
    }

    public function details($details)
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

        if (!$item && is_numeric($details)) {
            $item = $this->model->getWithDetails((int)$details);
            if ($item) $id = $item['id_mission'];
        }

        if (!$item) {
            $item = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $details);
            if ($item) {
                $id = $item['id_mission'];
                $item = $this->model->getWithDetails($id);
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'mission/list');
            exit();
        }

        if ($this->isLivreur()) {
            $livreurCode = $this->getCurrentLivreurCode();
            if ($livreurCode === null || ($item['livreur_code'] ?? '') !== $livreurCode) {
                header('Location: ' . RACINE . 'mission/list');
                exit();
            }
        }

        $encryptedId = $this->validator->crypter($id);

        $this->loadView('../views/missions/details.php', [
            'mission' => $item,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        if ($this->isLivreur()) {
            header('Location: ' . RACINE . 'mission/list?error=forbidden');
            exit();
        }

        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'mission/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'mission/list');
            exit();
        }

        $this->loadView('../views/missions/edit.php', ['mission' => $item]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner une mission';
        foreach ($items as $i) {
            $options[$i['code_mission']] = $i['code_mission'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        if ($this->isLivreur()) {
            header('Location: ' . RACINE . 'mission/list?error=forbidden');
            exit();
        }
        $this->loadView('../views/missions/edit.php', ['mission' => []]);
    }

    // === ACTIONS LIVREUR TERRAIN & NOTIFICATIONS CLIENT ===

    public function enRouteCollecte()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $codeMission = $this->post('code_mission');

        $mission = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $codeMission);
        if (!$mission) {
            $this->error('Mission introuvable');
            return;
        }

        $cmdModel = new ModelCommande();
        $commande = $cmdModel->getByCodeWithDetails($mission['commande_code']);
        if (!$commande) {
            $this->error('Commande associée introuvable');
            return;
        }

        // Récupérer le nom du livreur
        $livreur = $this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $mission['livreur_code']);
        $nomLivreur = $livreur ? ($livreur['nom_livreur'] ?? 'Le coursier') : 'Le coursier';

        // Mettre à jour la mission & la commande
        $this->model->getCon()->prepare("UPDATE " . TABLES::MISSIONS . " SET statut_mission = 'en_cours' WHERE code_mission = ?")->execute([$codeMission]);
        $this->model->getCon()->prepare("UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'livreur_en_route_collecte' WHERE code_commande = ?")->execute([$mission['commande_code']]);

        // Notification client
        NotificationService::notifyDriverEnRoute($commande['client_code'], $commande['code_commande'], $nomLivreur);

        $this->success('Statut mis à jour : vous êtes en route pour la collecte !', ['reload' => true]);
    }

    public function lingeCollecte()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $codeMission = $this->post('code_mission');

        $mission = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $codeMission);
        if (!$mission) {
            $this->error('Mission introuvable');
            return;
        }

        $cmdModel = new ModelCommande();
        $commande = $cmdModel->getByCodeWithDetails($mission['commande_code']);

        // Mettre à jour la commande
        $this->model->getCon()->prepare("UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'collectee' WHERE code_commande = ?")->execute([$mission['commande_code']]);

        // Notification client
        if ($commande) {
            NotificationService::notifyCollectionCompleted($commande['client_code'], $commande['code_commande']);
        }

        $this->success('Linge collecté avec succès chez le client !', ['reload' => true]);
    }

    public function deposeAuPressing()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $codeMission = $this->post('code_mission');

        $mission = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $codeMission);
        if (!$mission) {
            $this->error('Mission introuvable');
            return;
        }

        $cmdModel = new ModelCommande();
        $commande = $cmdModel->getByCodeWithDetails($mission['commande_code']);

        // Clôturer la mission de collecte & mettre la commande en "recue_pressing"
        $this->model->getCon()->prepare("UPDATE " . TABLES::MISSIONS . " SET statut_mission = 'terminee' WHERE code_mission = ?")->execute([$codeMission]);
        $this->model->getCon()->prepare("UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'recue_pressing' WHERE code_commande = ?")->execute([$mission['commande_code']]);

        // Notification client
        if ($commande) {
            NotificationService::notifyReceivedAtPressing($commande['client_code'], $commande['code_commande'], $commande['libelle_pressing'] ?? 'Le pressing');
        }

        $this->success('Linge déposé au pressing ! Mission de collecte terminée.', ['reload' => true]);
    }

    public function enRouteLivraison()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $codeMission = $this->post('code_mission');

        $mission = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $codeMission);
        if (!$mission) {
            $this->error('Mission introuvable');
            return;
        }

        $cmdModel = new ModelCommande();
        $commande = $cmdModel->getByCodeWithDetails($mission['commande_code']);

        $livreur = $this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $mission['livreur_code']);
        $nomLivreur = $livreur ? ($livreur['nom_livreur'] ?? 'Le coursier') : 'Le coursier';

        // Mettre à jour la mission & la commande
        $this->model->getCon()->prepare("UPDATE " . TABLES::MISSIONS . " SET statut_mission = 'en_cours' WHERE code_mission = ?")->execute([$codeMission]);
        $this->model->getCon()->prepare("UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'en_livraison' WHERE code_commande = ?")->execute([$mission['commande_code']]);

        // Notification client
        if ($commande) {
            NotificationService::notifyDeliveryEnRoute($commande['client_code'], $commande['code_commande'], $nomLivreur);
        }

        $this->success('Statut mis à jour : vous êtes en route pour la livraison !', ['reload' => true]);
    }

    public function remiseAuClient()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $codeMission = $this->post('code_mission');

        $mission = $this->validator->getByElement(TABLES::MISSIONS, 'code_mission', $codeMission);
        if (!$mission) {
            $this->error('Mission introuvable');
            return;
        }

        $cmdModel = new ModelCommande();
        $commande = $cmdModel->getByCodeWithDetails($mission['commande_code']);

        // Clôturer la mission de livraison & mettre la commande en "livree"
        $this->model->getCon()->prepare("UPDATE " . TABLES::MISSIONS . " SET statut_mission = 'terminee' WHERE code_mission = ?")->execute([$codeMission]);
        $this->model->getCon()->prepare("UPDATE " . TABLES::COMMANDES . " SET statut_suivi_commande = 'livree' WHERE code_commande = ?")->execute([$mission['commande_code']]);

        // Notification client
        if ($commande) {
            NotificationService::notifyOrderDelivered($commande['client_code'], $commande['code_commande']);
        }

        $this->success('Linge remis au client ! Commande livrée et clôturée avec succès.', ['reload' => true]);
    }

    public function carte()
    {
        $this->requireAuth();
        $livreurCode = $this->getCurrentLivreurCode();
        $missions = (new ModelHome())->getLivreurMissions($livreurCode, 50);

        $this->loadView('../views/missions/carte.php', [
            'livreurCode' => $livreurCode,
            'missions' => $missions
        ]);
    }
}
