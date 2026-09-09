<?php

class PackController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelPack();
    }

    public function getStats(): array
    {
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT p.*, 
                   s.nombre_jour_session
            FROM packs p
            LEFT JOIN sessions s ON s.code_session = p.session_code AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            WHERE p.etablissement_code = ? AND p.zone_code = ? AND p.annee_code = ?
        ";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute([
            $etabCode, $zoneCode, $anneeCode,
            $etabCode, $zoneCode, $anneeCode
        ]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $totalPacks = count($items);
        $nbActif = 0;
        $nbInactif = 0;
        $sumPrixJour = 0;

        foreach ($items as $p) {
            $isActif = (($p['statut_pack'] ?? '') === 'actif');
            if ($isActif) {
                $nbActif++;
                $sumPrixJour += (float)($p['prix_cotisation_pack'] ?? 0);
            } else {
                $nbInactif++;
            }
        }

        $avgPrixJour = $nbActif > 0 ? round($sumPrixJour / $nbActif, 2) : 0;

        $stmtArt = $this->model->getCon()->query("SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'");
        $totalArticles = $stmtArt ? (int)$stmtArt->fetchColumn() : 0;

        return [
            'total_packs' => $totalPacks,
            'nb_actif' => $nbActif,
            'nb_inactif' => $nbInactif,
            'avg_prix_jour' => $avgPrixJour,
            'total_articles' => $totalArticles
        ];
    }

    public function list()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $stats = $this->getStats();
        $this->loadView('../views/packs/list.php', ['stats' => $stats]);
    }

    public function apiList()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $sql = "
            SELECT p.*, 
                   c.libelle_categorie_pack,
                   s.libelle_session,
                   s.nombre_jour_session,
                   z.libelle_zone,
                   a.libelle_annee
            FROM packs p
            LEFT JOIN categorie_packs c ON c.code_categorie_pack = p.categorie_pack_code
            LEFT JOIN sessions s ON s.code_session = p.session_code AND s.etablissement_code = ? AND s.zone_code = ? AND s.annee_code = ?
            LEFT JOIN zones z ON z.code_zone = p.zone_code
            LEFT JOIN annees a ON a.code_annee = p.annee_code
            WHERE p.etablissement_code = ? AND p.zone_code = ? AND p.annee_code = ?
            ORDER BY p.annee_code ASC, p.zone_code ASC, p.id_pack DESC
        ";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute([
            $etabCode, $zoneCode, $anneeCode,
            $etabCode, $zoneCode, $anneeCode
        ]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $data = [];

        foreach ($items as $i) {
            $id = $i['id_pack'];
            $idCrypte = $this->validator->crypter($id);
            $prixCotis = (float)($i['prix_cotisation_pack'] ?? 0);
            $nbJours = (int)($i['nombre_jour_session'] ?? 0);
            $montantTotal = $prixCotis * $nbJours;

            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'libelle_annee' => $i['libelle_annee'] ?? ($i['annee_code'] ?? '-'),
                'libelle_categorie' => $i['libelle_categorie_pack'] ?? ($i['categorie_pack_code'] ?? '-'),
                'libelle_session' => $i['libelle_session'] ?? ($i['session_code'] ?? '-'),
                'libelle_zone' => $i['libelle_zone'] ?? ($i['zone_code'] ?? '-'),
                'nombre_jour_session' => $nbJours,
                'montant_total' => $montantTotal
            ]);
        }
        $stats = $this->getStats();
        $this->json([
            'data' => $data,
            'stats' => $stats
        ]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $data = $_POST;
        unset($data['csrf_token']);

        $userCode = Context::user();
        $anneeCode = Context::annee();
        $etabCode = Context::etablissement();
        $zoneCode = !empty($data['zone_code']) ? $data['zone_code'] : Context::zone();

        if (empty($userCode) || empty($anneeCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale, l'année d'exercice et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        if (!empty($data['libelle_pack'])) {
            $conditions = [
                'session_code' => $data['session_code'] ?? '',
                'categorie_pack_code' => $data['categorie_pack_code'] ?? '',
                'zone_code' => $zoneCode,
                'libelle_pack' => $data['libelle_pack'] ?? '',
                'etablissement_code' => $etabCode,
                'annee_code' => $anneeCode
            ];
            if (!$this->checkUniquePair('packs', $conditions, 'Pack (Session + Catégorie + Zone + Nom)')) return;
        }

        if (empty($data['code_pack'])) {
            $data['code_pack'] = $this->validator->generateCode('packs', 'code_pack', 'PCK-', 8);
        }
        $data['statut_pack'] = $data['statut_pack'] ?? 'actif';
        $data['created_at_pack'] = date('Y-m-d H:i:s');
        if (isset($data['montant_pack'])) {
            unset($data['montant_pack']);
        }

        if (isset($_FILES['image_pack']) && $_FILES['image_pack']['error'] === UPLOAD_ERR_OK && !empty($_FILES['image_pack']['name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/images/packs/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['image_pack']['name'], PATHINFO_EXTENSION));
            $filename = 'pack_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image_pack']['tmp_name'], $uploadDir . $filename)) {
                $data['image_pack'] = $filename;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE packs")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        if (in_array('zone_code', $cols)) $data['zone_code'] = $zoneCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            // Traiter la liste des articles du pack s'il y en a
            if (!empty($_POST['articles']) && is_array($_POST['articles'])) {
                $this->model->syncArticles($data['code_pack'], $_POST['articles'], $anneeCode, $etabCode, $zoneCode);
            }
            $this->success('Pack créé avec succès!');
        } else {
            $this->error('Erreur lors de la création du pack');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $id = (int)$this->post('id_pack');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $pack = $this->model->getById($id);
        if (!$pack || $pack['etablissement_code'] !== Context::etablissement() || $pack['zone_code'] !== Context::zone() || $pack['annee_code'] !== Context::annee()) {
            $this->error('Pack introuvable ou non autorisé'); 
            return; 
        }

        if (!empty($data['libelle_pack'])) {
            $conditions = [
                'session_code' => $data['session_code'] ?? $pack['session_code'],
                'categorie_pack_code' => $data['categorie_pack_code'] ?? $pack['categorie_pack_code'],
                'zone_code' => $data['zone_code'] ?? $pack['zone_code'],
                'libelle_pack' => $data['libelle_pack'] ?? $pack['libelle_pack']
            ];
            if (!$this->checkUniquePair('packs', $conditions, 'Pack (Session + Catégorie + Zone + Nom)', 'id_pack', $id)) return;
        }

        $data['updated_at_pack'] = date('Y-m-d H:i:s');
        if (isset($data['montant_pack'])) {
            unset($data['montant_pack']);
        }

        if (isset($_FILES['image_pack']) && $_FILES['image_pack']['error'] === UPLOAD_ERR_OK && !empty($_FILES['image_pack']['name'])) {
            $uploadDir = __DIR__ . '/../../public/assets/images/packs/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['image_pack']['name'], PATHINFO_EXTENSION));
            $filename = 'pack_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image_pack']['tmp_name'], $uploadDir . $filename)) {
                $data['image_pack'] = $filename;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE packs")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            if (isset($_POST['articles']) && is_array($_POST['articles'])) {
                $anneeCode = Context::annee();
                $etabCode = Context::etablissement();
                $zoneCode = $pack['zone_code'] ?? Context::zone();
                $this->model->syncArticles($pack['code_pack'], $_POST['articles'], $anneeCode, $etabCode, $zoneCode);
            }
            $this->success('Pack modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ($item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->error('Pack introuvable');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Pack introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                $this->renderNotFound("Le pack demandé est introuvable.");
                return;
            }
            $packArticles = $this->model->getArticles($item['code_pack']);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le pack demandé est introuvable.");
            return;
        }
        $this->loadView('../views/packs/details.php', [
            'item' => $item,
            'packArticles' => $packArticles,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || $item['etablissement_code'] !== Context::etablissement() || $item['zone_code'] !== Context::zone() || $item['annee_code'] !== Context::annee()) {
                header('Location: ' . RACINE . 'pack/list'); exit();
            }
            $packArticles = $this->model->getArticles($item['code_pack']);
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'pack/list'); exit();
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $categories = $this->model->getCon()->query("SELECT code_categorie_pack, libelle_categorie_pack FROM categorie_packs WHERE statut_categorie_pack='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $stmtSessions = $this->model->getCon()->prepare("
            SELECT code_session, libelle_session, nombre_jour_session 
            FROM sessions 
            WHERE statut_session='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtSessions->execute([$etabCode, $zoneCode, $anneeCode]);
        $sessions = $stmtSessions->fetchAll(PDO::FETCH_ASSOC);

        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $articles = $this->model->getCon()->query("SELECT code_article, libelle_article FROM articles WHERE statut_article='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/packs/edit.php', [
            'item' => $item,
            'packArticles' => $packArticles,
            'categories' => $categories,
            'sessions' => $sessions,
            'zones' => $zones,
            'articles' => $articles,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_PACKS');
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $anneeCode = Context::annee();

        $categories = $this->model->getCon()->query("SELECT code_categorie_pack, libelle_categorie_pack FROM categorie_packs WHERE statut_categorie_pack='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $stmtSessions = $this->model->getCon()->prepare("
            SELECT code_session, libelle_session, nombre_jour_session 
            FROM sessions 
            WHERE statut_session='actif' AND etablissement_code = ? AND zone_code = ? AND annee_code = ?
        ");
        $stmtSessions->execute([$etabCode, $zoneCode, $anneeCode]);
        $sessions = $stmtSessions->fetchAll(PDO::FETCH_ASSOC);

        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $articles = $this->model->getCon()->query("SELECT code_article, libelle_article FROM articles WHERE statut_article='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/packs/edit.php', [
            'item' => [],
            'packArticles' => [],
            'categories' => $categories,
            'sessions' => $sessions,
            'zones' => $zones,
            'articles' => $articles
        ]);
    }
}
