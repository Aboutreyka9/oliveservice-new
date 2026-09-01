<?php

class LivreurController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelLivreur();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/livreurs/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $livreurs = $this->model->getByPressing($pressingCode);
        } else {
            $livreurs = $this->model->getAll();
        }

        $data = [];

        foreach ($livreurs as $l) {
            $idCrypte = $this->validator->crypter($l['id_livreur']);
            $data[] = [
                'code' => $l['code_livreur'],
                'nom' => $l['nom_livreur'] ?? '',
                'prenom' => $l['prenom_livreur'] ?? '',
                'telephone' => $l['telephone_livreur'] ?? '',
                'pressing' => $l['pressing_code'] ?? '',
                'statut' => $l['statut_livreur'],
                'id' => $l['id_livreur'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['nom_livreur' => $_POST['nom_livreur'] ?? '', 'telephone_livreur' => $_POST['telephone_livreur'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $code = $this->post('code_livreur') ?: $this->validator->generateCode(TABLES::LIVREURS, 'code_livreur', 'LIV-', 6);
        if ($this->validator->getByElement(TABLES::LIVREURS, 'code_livreur', $code)) {
            $this->error('Ce code livreur existe déjà!');
            return;
        }

        // Attribution automatique du pressing pour le gérant Pro ou fallback
        $pressingCode = $this->getCurrentPressingCode();
        if (empty($pressingCode)) {
            $pressingCode = $this->post('pressing_code') ?: 'PRS-001';
        }

        $this->requireActiveAbonnement($pressingCode, 'créer ou gérer des livreurs');

        $db = $this->model->getCon();
        $userCode = $this->validator->generateCode(TABLES::USERS, 'code_user', 'USR-', 6);
        $userPressingCode = $this->validator->generateCode(TABLES::USERS_PRESSINGS, 'code_user_pressing', 'USP-', 6);
        $telephone = $this->post('telephone_livreur');
        $email = $this->post('email') ?: ($this->post('email_user') ?: ($telephone . '@lavex.ci'));
        $password = $this->post('password') ?: '123456';
        $hashedPassword = Validator::hashPassword($password);

        // 1. Créer le compte utilisateur pour la connexion mobile/admin si inexistant
        $stmtCheck = $db->prepare("SELECT code_user FROM " . TABLES::USERS . " WHERE email_user = ? OR (telephone_user = ? AND telephone_user != '') LIMIT 1");
        $stmtCheck->execute([$email, $telephone]);
        $existingUser = $stmtCheck->fetchColumn();

        if (!$existingUser) {
            $stmtUser = $db->prepare("
                INSERT INTO " . TABLES::USERS . " (code_user, role_code, nom_user, prenom_user, email_user, telephone_user, password_user, statut_user, created_at_user)
                VALUES (?, 'ROLE-LIV', ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtUser->execute([$userCode, $this->post('nom_livreur'), $this->post('prenom_livreur') ?? '', $email, $telephone, $hashedPassword]);
            $existingUser = $userCode;
        }

        // 2. Liaison User <-> Pressing (Vérification anti-doublon SQL)
        $stmtCheckUp = $db->prepare("SELECT id_user_pressing FROM " . TABLES::USERS_PRESSINGS . " WHERE user_code = ? AND pressing_code = ? LIMIT 1");
        $stmtCheckUp->execute([$existingUser, $pressingCode]);
        if (!$stmtCheckUp->fetch()) {
            $stmtUp = $db->prepare("
                INSERT INTO " . TABLES::USERS_PRESSINGS . " (code_user_pressing, user_code, pressing_code, role_code, statut_user_pressing, created_at_user_pressing)
                VALUES (?, ?, ?, 'ROLE-LIV', 'actif', NOW())
            ");
            $stmtUp->execute([$userPressingCode, $existingUser, $pressingCode]);
        }

        $data = [
            'code_livreur' => $code,
            'pressing_code' => $pressingCode,
            'user_code' => $existingUser,
            'nom_livreur' => $this->post('nom_livreur'),
            'prenom_livreur' => $this->post('prenom_livreur') ?? '',
            'telephone_livreur' => $this->post('telephone_livreur'),
            'statut_livreur' => 'actif',
            'created_at_livreur' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Livreur ajouté avec succès et rattaché au pressing !');
        } else {
            $this->error('Erreur lors de l\'ajout du livreur');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $notEmpty = Validator::validateRequiredFields(['nom_livreur' => $_POST['nom_livreur'] ?? '', 'telephone_livreur' => $_POST['telephone_livreur'] ?? '', 'id_livreur' => $_POST['id_livreur'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs!');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';
        $id = (int) $this->post('id_livreur');

        $currentLivreur = $this->model->getById($id);
        if (!$currentLivreur) {
            $this->error('Livreur introuvable');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            // Si gérant de pressing, sécuriser qu'il n'édite que ses propres livreurs
            if (($currentLivreur['pressing_code'] ?? '') !== $pressingCode) {
                $this->error('Accès refusé', 403);
                return;
            }
        } else {
            $pressingCode = $this->post('pressing_code') ?: ($currentLivreur['pressing_code'] ?? 'PRS-001');
        }

        $this->requireActiveAbonnement($pressingCode, 'modifier des livreurs');

        $data = [
            'id_livreur' => $id,
            'pressing_code' => $pressingCode,
            'nom_livreur' => $this->post('nom_livreur'),
            'prenom_livreur' => $this->post('prenom_livreur') ?? '',
            'telephone_livreur' => $this->post('telephone_livreur'),
            'statut_livreur' => $statut,
            'updated_at_livreur' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Livreur modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'activer ou désactiver des livreurs');
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Livreur introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'livreur/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'livreur/list');
            exit();
        }

        $pressingName = 'Pressing Partenaire';
        if (!empty($item['pressing_code'])) {
            $stmtP = $this->model->getCon()->prepare("SELECT libelle_pressing FROM " . TABLES::PRESSINGS . " WHERE code_pressing = ? LIMIT 1");
            $stmtP->execute([$item['pressing_code']]);
            $pressingName = $stmtP->fetchColumn() ?: $item['pressing_code'];
        }

        // Récupération des missions du livreur
        $missions = [];
        $stats = [
            'total_missions' => 0,
            'terminees' => 0,
            'en_cours' => 0,
            'collectes' => 0,
            'livraisons' => 0
        ];
        try {
            $stmtM = $this->model->getCon()->prepare("
                SELECT m.*, c.statut_suivi_commande, cli.nom_client, cli.telephone_client
                FROM " . TABLES::MISSIONS . " m
                LEFT JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande
                LEFT JOIN " . TABLES::CLIENTS . " cli ON c.client_code = cli.code_client
                WHERE m.livreur_code = ?
                ORDER BY m.id_mission DESC
            ");
            $stmtM->execute([$item['code_livreur']]);
            $missions = $stmtM->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $stats['total_missions'] = count($missions);
            foreach ($missions as $m) {
                if (($m['statut_mission'] ?? '') === 'terminee') $stats['terminees']++;
                if (($m['statut_mission'] ?? '') === 'en_cours') $stats['en_cours']++;
                if (strtolower($m['type_mission'] ?? '') === 'collecte') $stats['collectes']++;
                if (strtolower($m['type_mission'] ?? '') === 'livraison') $stats['livraisons']++;
            }
        } catch (Exception $e) {
            error_log('[LivreurController::details] Missions error: ' . $e->getMessage());
        }

        $this->loadView('../views/livreurs/details.php', [
            'livreur' => $item,
            'pressingName' => $pressingName,
            'missions' => $missions,
            'stats' => $stats,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'livreur/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'livreur/list');
            exit();
        }

        $pressings = (new ModelPressing())->getByStatus('actif');

        $this->loadView('../views/livreurs/edit.php', [
            'livreur' => $item,
            'pressings' => $pressings
        ]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            $items = $this->model->getByPressing($pressingCode);
        } else {
            $items = $this->model->getByStatus('actif');
        }
        $options = [];
        $options[''] = 'Sélectionner un livreur';
        foreach ($items as $i) {
            if (($i['statut_livreur'] ?? '') === 'actif') {
                $options[$i['code_livreur']] = ($i['nom_livreur'] ?? '') . ' ' . ($i['prenom_livreur'] ?? '');
            }
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $pressings = (new ModelPressing())->getByStatus('actif');
        $this->loadView('../views/livreurs/edit.php', [
            'livreur' => [],
            'pressings' => $pressings
        ]);
    }

    public function updatePosition()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $lat = (float)$this->post('latitude');
        $lng = (float)$this->post('longitude');
        $codeLivreur = $this->post('code_livreur') ?: $this->getCurrentLivreurCode();

        if (!$codeLivreur) {
            $this->error('Code livreur introuvable');
            return;
        }

        if (empty($lat) || empty($lng)) {
            $this->error('Coordonnées GPS requises');
            return;
        }

        if ($this->model->updatePosition($codeLivreur, $lat, $lng)) {
            $this->success('Position GPS mise à jour', [
                'latitude' => $lat,
                'longitude' => $lng,
                'timestamp' => date('Y-m-d H:i:s')
            ]);
        } else {
            $this->error('Erreur lors de la mise à jour GPS');
        }
    }

    public function livePositions()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        $positions = $this->model->getLivePositions($pressingCode);
        $this->json(['data' => $positions]);
    }
}
