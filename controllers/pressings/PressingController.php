<?php

class PressingController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelPressing();
    }

    public function list()
    {
        $this->requireAuth();
        $db = $this->model->getCon();

        $villes = $db->query("SELECT code_ville, libelle_ville FROM " . TABLES::VILLES . " WHERE statut_ville = 'actif' ORDER BY libelle_ville ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $quartiers = $db->query("SELECT code_quartier, ville_code, libelle_quartier FROM " . TABLES::QUARTIERS . " WHERE statut_quartier = 'actif' ORDER BY libelle_quartier ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $modelForfait = new ModelForfait();
        $forfaits = $db->query("SELECT * FROM " . TABLES::FORFAITS . " WHERE statut_forfait = 'actif' ORDER BY montant_forfait ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($forfaits as &$f) {
            $f['avantages'] = $modelForfait->getAvantagesByCode($f['code_forfait']);
            $f['avantages_forfait'] = json_encode($f['avantages'], JSON_UNESCAPED_UNICODE);
        }
        unset($f);

        $this->loadView('../views/pressings/list.php', [
            'villes' => $villes,
            'quartiers' => $quartiers,
            'forfaits' => $forfaits,
            'isSuperAdmin' => $this->isSuperAdmin(),
            'currentPressingCode' => $this->getCurrentPressingCode()
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressings = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $pressings = array_filter($pressings, function($p) use ($pressingCode) {
                return $p['code_pressing'] === $pressingCode;
            });
        }

        $data = [];

        foreach ($pressings as $p) {
            $idCrypte = $this->validator->crypter($p['id_pressing']);
            $data[] = [
                'code' => $p['code_pressing'],
                'libelle' => $p['libelle_pressing'],
                'telephone' => $p['telephone_pressing'] ?? '',
                'email' => $p['email_pressing'] ?? '',
                'adresse' => $p['adresse_pressing'] ?? '',
                'statut' => $p['statut_pressing'],
                'id' => $p['id_pressing'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireSuperAdmin("Seul le Super Administrateur peut créer ou ajouter un nouveau pressing.");

        $libelle = trim($this->post('libelle_pressing'));
        $emailUser = trim($this->post('email_user'));
        $nomUser = trim($this->post('nom_user'));
        $passwordUser = trim($this->post('password_user')) ?: '12345';
        $forfaitCode = trim($this->post('forfait_code'));

        if (empty($libelle) || empty($emailUser) || empty($nomUser) || empty($forfaitCode)) {
            $this->error('Veuillez remplir toutes les informations obligatoires (Nom du Pressing, Responsable, Email Login et Forfait B2B) !');
            return;
        }

        $db = $this->model->getCon();

        // 1. Vérifier si l'email gérant existe déjà
        $stmtU = $db->prepare("SELECT id_user FROM " . TABLES::USERS . " WHERE email_user = ? LIMIT 1");
        $stmtU->execute([$emailUser]);
        if ($stmtU->fetch()) {
            $this->error('Cet email d\'utilisateur (' . $emailUser . ') est déjà attribué à un autre compte !');
            return;
        }

        // 2. Générer le code pressing
        $codePressing = $this->post('code_pressing') ?: $this->validator->generateCode(TABLES::PRESSINGS, 'code_pressing', 'PRS-', 6);

        // 3. Récupérer le forfait B2B
        $stmtF = $db->prepare("SELECT * FROM " . TABLES::FORFAITS . " WHERE code_forfait = ? LIMIT 1");
        $stmtF->execute([$forfaitCode]);
        $forfait = $stmtF->fetch(PDO::FETCH_ASSOC);
        if (!$forfait) {
            $this->error('Le forfait B2B sélectionné est invalide !');
            return;
        }

        $dureeMois = (int)($this->post('duree_mois') ?: 3);
        if ($dureeMois < 1) $dureeMois = 3;
        $montant = (float)($this->post('montant_abonnement') !== '' ? $this->post('montant_abonnement') : ($forfait['montant_forfait'] ?? 0));
        $dateDebut = $this->post('date_debut_abonnement') ?: date('Y-m-d');
        $dateFin = date('Y-m-d', strtotime("+$dureeMois months", strtotime($dateDebut)));

        $geo = $this->resolveGeoCoordinates(
            $this->post('latitude_pressing'),
            $this->post('longitude_pressing'),
            $this->post('adresse_pressing') ?? '',
            $this->post('quartier_code') ?? '',
            $this->post('ville_code') ?? ''
        );

        // 4. Lancer l'onboarding tout-en-un sous transaction
        $res = $this->model->onboardPressingWithOwnerAndSubscription([
            'code_pressing' => $codePressing,
            'libelle_pressing' => $libelle,
            'telephone_pressing' => $this->post('telephone_pressing') ?? '',
            'email_pressing' => $this->post('email_pressing') ?? $emailUser,
            'adresse_pressing' => $this->post('adresse_pressing') ?? '',
            'ville_code' => $this->post('ville_code') ?? '',
            'quartier_code' => $this->post('quartier_code') ?? '',
            'latitude_pressing' => $geo['lat'],
            'longitude_pressing' => $geo['lng'],
            'logo_pressing' => $this->post('logo_pressing') ?? ''
        ], [
            'nom_user' => $nomUser,
            'prenom_user' => $this->post('prenom_user') ?? '',
            'telephone_user' => $this->post('telephone_user') ?? $this->post('telephone_pressing'),
            'email_user' => $emailUser,
            'password_user' => $passwordUser
        ], [
            'forfait_code' => $forfaitCode,
            'montant_abonnement' => $montant,
            'date_debut_abonnement' => $dateDebut,
            'date_fin_abonnement' => $dateFin
        ], $this->getCurrentUserCode());

        if ($res['success']) {
            $this->success('Pressing, compte gérant et abonnement B2B créés et activés avec succès !');
        } else {
            $this->error('Erreur lors de la création : ' . ($res['error'] ?? 'Veuillez réessayer.'));
        }
    }

    private function handleLogoUpload(?string $existingLogo = null): ?string
    {
        if (isset($_FILES['logo_file']) && $_FILES['logo_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];

            if (!in_array($ext, $allowedExtensions, true)) {
                throw new InvalidArgumentException("Format d'image du logo non autorisé (JPG, PNG, WEBP, SVG, GIF).");
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                throw new InvalidArgumentException("Le fichier du logo ne doit pas dépasser 5 Mo.");
            }

            $uploadDir = __DIR__ . '/../../public/assets/images/pressings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'prs_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                return $newFileName;
            }
        }

        $postedLogo = trim($_POST['logo_pressing'] ?? '');
        if ($postedLogo !== '') {
            return $postedLogo;
        }

        return $existingLogo;
    }

    private function handleMiniatureUpload(?string $existingMiniature = null): ?string
    {
        if (isset($_FILES['miniature_file']) && $_FILES['miniature_file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['miniature_file'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'svg', 'gif'];

            if (!in_array($ext, $allowedExtensions, true)) {
                throw new InvalidArgumentException("Format d'image miniature non autorisé (JPG, PNG, WEBP, SVG, GIF).");
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                throw new InvalidArgumentException("Le fichier de la miniature ne doit pas dépasser 5 Mo.");
            }

            $uploadDir = __DIR__ . '/../../public/assets/images/pressings/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $newFileName = 'min_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $destPath = $uploadDir . $newFileName;

            if (move_uploaded_file($file['tmp_name'], $destPath)) {
                return $newFileName;
            }
        }

        $postedMiniature = trim($_POST['miniature_pressing'] ?? '');
        if ($postedMiniature !== '') {
            return $postedMiniature;
        }

        return $existingMiniature;
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (empty($_POST) && isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0) {
            $this->error('L\'image ou le fichier téléchargé est trop volumineux pour le serveur (taille max 8 Mo). Veuillez choisir une photo plus légère !');
            return;
        }

        $notEmpty = Validator::validateRequiredFields(['libelle_pressing' => $_POST['libelle_pressing'] ?? '', 'id_pressing' => $_POST['id_pressing'] ?? '']);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs obligatoires !');
            return;
        }

        $statut = in_array($this->post('statut_pressing'), ['actif', 'inactif', 'suspendu']) ? $this->post('statut_pressing') : 'actif';
        $id = (int) $this->post('id_pressing');
        $pressing = $this->model->getById($id);
        if (!$pressing) {
            $this->error('Pressing introuvable!');
            return;
        }

        try {
            $logo = $this->handleLogoUpload($pressing['logo_pressing'] ?? null);
            $miniature = $this->handleMiniatureUpload($pressing['miniature_pressing'] ?? null);
        } catch (Exception $e) {
            $this->error($e->getMessage());
            return;
        }

        $modeLogistique = in_array($_POST['mode_logistique'] ?? '', ['pressing', 'lavex', 'retrait_uniquement']) ? $_POST['mode_logistique'] : 'pressing';
        $fraisCollectePressing = (isset($_POST['frais_collecte_pressing']) && $_POST['frais_collecte_pressing'] !== '') ? (float)$_POST['frais_collecte_pressing'] : 1000.00;
        $fraisLivraisonPressing = (isset($_POST['frais_livraison_pressing']) && $_POST['frais_livraison_pressing'] !== '') ? (float)$_POST['frais_livraison_pressing'] : 1000.00;
        $tauxCommissionPressing = (isset($_POST['taux_commission_pressing']) && $_POST['taux_commission_pressing'] !== '') ? (float)$_POST['taux_commission_pressing'] : 0.00;
        $proposeLivraison = isset($_POST['propose_livraison']) ? (int)$_POST['propose_livraison'] : ($modeLogistique === 'retrait_uniquement' ? 0 : 1);
        $livraisonGratuite = isset($_POST['livraison_gratuite']) ? (int)$_POST['livraison_gratuite'] : 0;
        $seuilLivraisonGratuite = (isset($_POST['seuil_livraison_gratuite']) && $_POST['seuil_livraison_gratuite'] !== '') ? (float)$_POST['seuil_livraison_gratuite'] : 0.00;
        $delaiLivraison = !empty($_POST['delai_livraison_pressing']) ? trim($_POST['delai_livraison_pressing']) : '24h - 48h';
        $accepteColisSansDetail = isset($_POST['accepte_colis_sans_detail']) ? (int)$_POST['accepte_colis_sans_detail'] : 0;

        $geoEdit = $this->resolveGeoCoordinates(
            $this->post('latitude_pressing'),
            $this->post('longitude_pressing'),
            $this->post('adresse_pressing') ?? '',
            $this->post('quartier_code') ?? '',
            $this->post('ville_code') ?? ''
        );

        $data = [
            'id_pressing' => $id,
            'libelle_pressing' => $this->post('libelle_pressing'),
            'telephone_pressing' => $this->post('telephone_pressing') ?? '',
            'email_pressing' => $this->post('email_pressing') ?? '',
            'adresse_pressing' => $this->post('adresse_pressing') ?? '',
            'ville_code' => $this->post('ville_code') ?? '',
            'quartier_code' => $this->post('quartier_code') ?? '',
            'latitude_pressing' => $geoEdit['lat'],
            'longitude_pressing' => $geoEdit['lng'],
            'logo_pressing' => $logo ?? '',
            'miniature_pressing' => $miniature ?? '',
            'mode_logistique' => $modeLogistique,
            'frais_collecte_pressing' => $fraisCollectePressing,
            'frais_livraison_pressing' => $fraisLivraisonPressing,
            'taux_commission_pressing' => $tauxCommissionPressing,
            'propose_livraison' => $proposeLivraison,
            'livraison_gratuite' => $livraisonGratuite,
            'seuil_livraison_gratuite' => $seuilLivraisonGratuite,
            'delai_livraison_pressing' => $delaiLivraison,
            'accepte_colis_sans_detail' => $accepteColisSansDetail,
            'statut_pressing' => $statut,
            'updated_at_pressing' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            $this->success('Pressing et paramètres mis à jour avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireSuperAdmin("Seul le Super Administrateur peut désactiver ou activer un pressing.");
        $id = $this->post('id');
        if (isset($id) && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Pressing introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        $item = null;
        $id = null;

        // Tenter le décryptage de l'identifiant
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
        } catch (Exception $e) {
            $item = null;
        }

        // Si non trouvé par ID crypté, chercher par code_pressing
        if (!$item) {
            $item = $this->validator->getByElement(TABLES::PRESSINGS, 'code_pressing', $details);
            if ($item) {
                $id = $item['id_pressing'];
            }
        }

        // Si non trouvé, chercher par ID direct numérique
        if (!$item && is_numeric($details)) {
            $item = $this->model->getById((int)$details);
            if ($item) {
                $id = $item['id_pressing'];
            }
        }

        if (!$item) {
            header('Location: ' . RACINE . 'pressing/list');
            exit();
        }

        $encryptedId = $this->validator->crypter($id);
        $pressingCode = $item['code_pressing'];

        // Si l'utilisateur est un gérant de pressing (ROLE-PRO), s'assurer qu'il accède bien à son propre pressing
        $this->requirePressingAccess($pressingCode);

        // Chargement de l'écosystème 360° du pressing
        $stats = $this->model->getPressingStats($pressingCode);
        $orders = $this->model->getPressingOrders($pressingCode, 100);
        $tarifs = $this->model->getPressingTarifs($pressingCode);
        $horaires = $this->model->getPressingHoraires($pressingCode);
        $clients = $this->model->getPressingClients($pressingCode);
        $missions = $this->model->getPressingMissions($pressingCode, 100);
        $abonnement = $this->model->getPressingAbonnement($pressingCode);
        $owner = $this->model->getPressingOwner($pressingCode);
        $pressingUsers = $this->model->getPressingUsers($pressingCode);

        $this->loadView('../views/pressings/details.php', [
            'pressing' => $item,
            'encryptedId' => $encryptedId,
            'stats' => $stats,
            'orders' => $orders,
            'tarifs' => $tarifs,
            'horaires' => $horaires,
            'clients' => $clients,
            'missions' => $missions,
            'abonnement' => $abonnement,
            'owner' => $owner,
            'pressingUsers' => $pressingUsers
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        $db = $this->model->getCon();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'pressing/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'pressing/list');
            exit();
        }

        $this->requirePressingAccess($item['code_pressing']);

        $villes = $db->query("SELECT code_ville, libelle_ville FROM " . TABLES::VILLES . " WHERE statut_ville = 'actif' ORDER BY libelle_ville ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $quartiers = $db->query("SELECT code_quartier, ville_code, libelle_quartier FROM " . TABLES::QUARTIERS . " WHERE statut_quartier = 'actif' ORDER BY libelle_quartier ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $modelForfait = new ModelForfait();
        $forfaits = $db->query("SELECT * FROM " . TABLES::FORFAITS . " WHERE statut_forfait = 'actif' ORDER BY montant_forfait ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($forfaits as &$f) {
            $f['avantages'] = $modelForfait->getAvantagesByCode($f['code_forfait']);
            $f['avantages_forfait'] = json_encode($f['avantages'], JSON_UNESCAPED_UNICODE);
        }
        unset($f);

        $this->loadView('../views/pressings/edit.php', [
            'pressing' => $item,
            'villes' => $villes,
            'quartiers' => $quartiers,
            'forfaits' => $forfaits
        ]);
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un pressing';
        foreach ($items as $i) {
            $options[$i['code_pressing']] = $i['libelle_pressing'];
        }
        $this->json(['options' => $options]);
    }

    public function formulaire()
    {
        $this->requireSuperAdmin();
        $db = $this->model->getCon();

        $villes = $db->query("SELECT code_ville, libelle_ville FROM " . TABLES::VILLES . " WHERE statut_ville = 'actif' ORDER BY libelle_ville ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $quartiers = $db->query("SELECT code_quartier, ville_code, libelle_quartier FROM " . TABLES::QUARTIERS . " WHERE statut_quartier = 'actif' ORDER BY libelle_quartier ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $modelForfait = new ModelForfait();
        $forfaits = $db->query("SELECT * FROM " . TABLES::FORFAITS . " WHERE statut_forfait = 'actif' ORDER BY montant_forfait ASC")->fetchAll(PDO::FETCH_ASSOC) ?: [];
        foreach ($forfaits as &$f) {
            $f['avantages'] = $modelForfait->getAvantagesByCode($f['code_forfait']);
            $f['avantages_forfait'] = json_encode($f['avantages'], JSON_UNESCAPED_UNICODE);
        }
        unset($f);

        $this->loadView('../views/pressings/edit.php', [
            'pressing' => [],
            'villes' => $villes,
            'quartiers' => $quartiers,
            'forfaits' => $forfaits
        ]);
    }

    public function addUser()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $pressingCode = trim($this->post('pressing_code'));
        $nom = trim($this->post('nom_user'));
        $prenom = trim($this->post('prenom_user'));
        $telephone = trim($this->post('telephone_user'));
        $email = trim($this->post('email_user'));
        $password = trim($this->post('password_user'));
        $roleCode = trim($this->post('role_code'));

        if (empty($pressingCode) || empty($nom) || empty($telephone) || empty($roleCode)) {
            $this->error('Veuillez renseigner toutes les informations obligatoires (Nom, Téléphone et Rôle) !');
            return;
        }

        if (!Validator::validNumber($telephone, 10)) {
            $this->error('Le numéro de téléphone doit contenir 10 chiffres !');
            return;
        }

        if (!in_array($roleCode, ['ROLE-PRO', 'ROLE-GEST', 'ROLE-LIV'])) {
            $this->error('Rôle invalide ! Sélectionnez soit Propriétaire, Gestionnaire ou Livreur.');
            return;
        }

        if (empty($password)) {
            $password = '12345';
        }

        $this->requirePressingAccess($pressingCode);

        $db = $this->model->getCon();

        // 1. Vérifier si le téléphone existe déjà
        $stmtT = $db->prepare("SELECT id_user FROM " . TABLES::USERS . " WHERE telephone_user = ? LIMIT 1");
        $stmtT->execute([$telephone]);
        if ($stmtT->fetch()) {
            $this->error("Le numéro de téléphone ($telephone) est déjà attribué à un autre compte !");
            return;
        }

        // 2. Vérifier l'email s'il est renseigné
        if (!empty($email)) {
            $stmtU = $db->prepare("SELECT id_user FROM " . TABLES::USERS . " WHERE email_user = ? LIMIT 1");
            $stmtU->execute([$email]);
            if ($stmtU->fetch()) {
                $this->error("L'adresse email ($email) est déjà attribuée à un autre compte !");
                return;
            }
        } else {
            $email = null;
        }

        try {
            $db->beginTransaction();

            $userCode = $this->validator->generateCode(TABLES::USERS, 'code_user', 'USR-', 6);
            $userPressingCode = $this->validator->generateCode(TABLES::USERS_PRESSINGS, 'code_user_pressing', 'USP-', 6);
            $hashedPassword = Validator::hashPassword($password);

            // 2. Création du compte User
            $stmtUser = $db->prepare("
                INSERT INTO " . TABLES::USERS . " (code_user, role_code, nom_user, prenom_user, email_user, telephone_user, password_user, statut_user, created_at_user)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtUser->execute([$userCode, $roleCode, $nom, $prenom, $email, $telephone, $hashedPassword]);

            // 3. Liaison User <-> Pressing
            $stmtUp = $db->prepare("
                INSERT INTO " . TABLES::USERS_PRESSINGS . " (code_user_pressing, user_code, pressing_code, role_code, statut_user_pressing, created_at_user_pressing)
                VALUES (?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtUp->execute([$userPressingCode, $userCode, $pressingCode, $roleCode]);

            // 4. Si Rôle Livreur, ajouter également dans la table livreurs
            if ($roleCode === 'ROLE-LIV') {
                $codeLivreur = $this->validator->generateCode(TABLES::LIVREURS, 'code_livreur', 'LIV-', 6);
                $stmtLiv = $db->prepare("
                    INSERT INTO " . TABLES::LIVREURS . " (code_livreur, pressing_code, user_code, nom_livreur, prenom_livreur, telephone_livreur, statut_livreur, created_at_livreur)
                    VALUES (?, ?, ?, ?, ?, ?, 'actif', NOW())
                ");
                $stmtLiv->execute([$codeLivreur, $pressingCode, $userCode, $nom, $prenom, $telephone]);
            }

            $db->commit();

            $msg = 'Propriétaire du pressing créé avec succès !';
            if ($roleCode === 'ROLE-GEST') $msg = 'Gestionnaire du pressing créé avec succès !';
            if ($roleCode === 'ROLE-LIV') $msg = 'Livreur du pressing créé et rattaché avec succès !';

            $this->success($msg);

        } catch (Exception $e) {
            if ($db->inTransaction()) $db->rollBack();
            error_log("Erreur PressingController::addUser: " . $e->getMessage());
            $this->error("Erreur serveur lors de la création du membre de l'équipe.");
        }
    }

    public function config()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if (empty($pressingCode)) {
            $db = $this->model->getCon();
            $pressingCode = $db->query("SELECT code_pressing FROM " . TABLES::PRESSINGS . " LIMIT 1")->fetchColumn() ?: 'PRS-001';
        }

        $db = $this->model->getCon();
        $stmt = $db->prepare("SELECT id_pressing FROM " . TABLES::PRESSINGS . " WHERE code_pressing = ? LIMIT 1");
        $stmt->execute([$pressingCode]);
        $idPressing = $stmt->fetchColumn();

        if (!$idPressing) {
            header('Location: ' . RACINE . 'pressing/list');
            exit();
        }

        $encryptedId = $this->validator->crypter($idPressing);
        $this->details($encryptedId);
    }

    /**
     * Récupère automatiquement les coordonnées GPS (Latitude & Longitude) du pressing
     */
    private function resolveGeoCoordinates(?string $postedLat, ?string $postedLng, string $adresse, string $quartierCode, string $villeCode): array
    {
        $lat = ($postedLat !== null && $postedLat !== '' && (float)$postedLat != 0) ? (float)$postedLat : null;
        $lng = ($postedLng !== null && $postedLng !== '' && (float)$postedLng != 0) ? (float)$postedLng : null;

        if ($lat !== null && $lng !== null) {
            return ['lat' => $lat, 'lng' => $lng];
        }

        $db = $this->model->getCon();
        $quartierName = '';
        $villeName = '';

        if (!empty($quartierCode)) {
            $stmtQ = $db->prepare("SELECT libelle_quartier FROM " . TABLES::QUARTIERS . " WHERE code_quartier = ? LIMIT 1");
            $stmtQ->execute([$quartierCode]);
            $quartierName = $stmtQ->fetchColumn() ?: '';
        }

        if (!empty($villeCode)) {
            $stmtV = $db->prepare("SELECT libelle_ville FROM " . TABLES::VILLES . " WHERE code_ville = ? LIMIT 1");
            $stmtV->execute([$villeCode]);
            $villeName = $stmtV->fetchColumn() ?: '';
        }

        // 1. Géocodage Nominatim avec l'adresse complète
        $query = trim("{$adresse} {$quartierName} {$villeName} Côte d'Ivoire");
        $geo = $this->queryNominatim($query);

        if (!$geo && (!empty($quartierName) || !empty($villeName))) {
            // 2. Géocodage avec Quartier + Ville
            $query2 = trim("{$quartierName} {$villeName} Côte d'Ivoire");
            $geo = $this->queryNominatim($query2);
        }

        if ($geo) {
            return $geo;
        }

        // 3. Fallbacks par Villes majeures de Côte d'Ivoire
        $vLower = strtolower($villeName);
        if (strpos($vLower, 'bouak') !== false) {
            return ['lat' => 7.690000, 'lng' => -5.030000];
        } elseif (strpos($vLower, 'yamoussoukro') !== false) {
            return ['lat' => 6.816000, 'lng' => -5.274000];
        } elseif (strpos($vLower, 'san-p') !== false || strpos($vLower, 'san pedro') !== false) {
            return ['lat' => 4.748500, 'lng' => -6.636300];
        } elseif (strpos($vLower, 'korhogo') !== false) {
            return ['lat' => 9.458000, 'lng' => -5.629600];
        }

        // Par défaut : Abidjan
        return ['lat' => 5.348440, 'lng' => -4.010550];
    }

    private function queryNominatim(string $query): ?array
    {
        if (empty(trim($query))) return null;
        $url = "https://nominatim.openstreetmap.org/search?format=json&limit=1&q=" . urlencode($query);
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: LavexApp/1.0 (contact@lavex.ci)\r\n",
                'timeout' => 3
            ]
        ];
        $context = stream_context_create($opts);

        try {
            $json = @file_get_contents($url, false, $context);
            if ($json) {
                $data = json_decode($json, true);
                if (!empty($data[0]['lat']) && !empty($data[0]['lon'])) {
                    return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
                }
            }
        } catch (Exception $e) {
            // ignore
        }
        return null;
    }
}
