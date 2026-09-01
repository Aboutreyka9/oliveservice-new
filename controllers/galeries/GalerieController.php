<?php

class GalerieController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelGalerie();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/galeries/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_galerie'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);
        if (empty($data['code_galerie'])) {
            $data['code_galerie'] = $this->validator->generateCode('galeries', 'code_galerie', 'GAL-', 8);
        }
        // Gestion upload fichier
        if (!empty($_FILES['fichier_upload']['name']) && $_FILES['fichier_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier_upload']);
            if ($uploadResult['success']) {
                $data['url_fichier'] = $uploadResult['path'];
            } else {
                $this->error($uploadResult['message']); return;
            }
        }
        if (empty($data['url_fichier'])) {
            $this->error('Veuillez fournir un fichier ou une URL.'); return;
        }
        $data['statut_galerie'] = $data['statut_galerie'] ?? 'actif';
        $data['created_at_galerie'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE galeries")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Item créé avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_galerie');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        // Gestion upload fichier
        if (!empty($_FILES['fichier_upload']['name']) && $_FILES['fichier_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier_upload']);
            if ($uploadResult['success']) {
                $data['url_fichier'] = $uploadResult['path'];
            } else {
                $this->error($uploadResult['message']); return;
            }
        }
        $cols = $this->model->getCon()->query("DESCRIBE galeries")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    /**
     * Gère l'upload d'un fichier image ou vidéo vers public/uploads/galeries/
     */
    private function handleFileUpload(array $file): array
    {
        $allowedImages = ['image/jpeg','image/png','image/gif','image/webp','image/svg+xml'];
        $allowedVideos = ['video/mp4','video/quicktime','video/x-msvideo','video/webm','video/mpeg'];
        $allowed = array_merge($allowedImages, $allowedVideos);
        $maxSize = 50 * 1024 * 1024; // 50 Mo

        $mime = mime_content_type($file['tmp_name']);
        if (!in_array($mime, $allowed)) {
            return ['success' => false, 'message' => 'Format non autorisé : ' . $mime];
        }
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Le fichier dépasse 50 Mo.'];
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $ext = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $ext));
        $newName = 'GAL_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/galeries/';
        if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'message' => 'Échec du déplacement du fichier uploadé.'];
        }

        // Chemin relatif accessible depuis le web
        $relativePath = RACINE . 'uploads/galeries/' . $newName;
        return ['success' => true, 'path' => $relativePath];
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['actif', 'brouillon', 'archive'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_galerie');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de la galerie mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Galerie introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'galerie/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'galerie/list'); exit();
        }
        $this->loadView('../views/galeries/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'galerie/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'galerie/list'); exit();
        }
        $this->loadView('../views/galeries/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/galeries/edit.php', ['item' => []]);
    }
}
