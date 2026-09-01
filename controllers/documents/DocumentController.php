<?php

class DocumentController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelDocument();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/documents/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT d.*, 
                       COALESCE(f.libelle_filiere, d.filiere_code, 'Toutes les filières') as libelle_filiere, 
                       COALESCE(n.libelle_niveau, d.niveaux_code, 'Tous les niveaux') as libelle_niveau,
                       DATE_FORMAT(d.created_at_document, '%d/%m/%Y') as date_creation_formatee
                FROM documents d
                LEFT JOIN filieres f ON (f.code_filiere = d.filiere_code OR f.libelle_filiere = d.filiere_code)
                LEFT JOIN niveaux n ON (n.code_niveau = d.niveaux_code OR n.libelle_niveau = d.niveaux_code)
                ORDER BY d.id_document DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_document'];
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

        if (empty($data['code_document'])) {
            $data['code_document'] = $this->validator->generateCode('documents', 'code_document', 'DOC-', 8);
        }

        // Support chemin_fichier alias to lien_document
        if (!empty($data['chemin_fichier']) && empty($data['lien_document'])) {
            $data['lien_document'] = $data['chemin_fichier'];
        }

        // Gestion upload de fichier
        if (!empty($_FILES['fichier_upload']['name']) && $_FILES['fichier_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier_upload']);
            if ($uploadResult['success']) {
                $data['lien_document'] = $uploadResult['path'];
                if (empty($data['type_document'])) {
                    $data['type_document'] = $uploadResult['type'];
                }
            } else {
                $this->error($uploadResult['message']); 
                return;
            }
        }

        if (empty($data['lien_document'])) {
            $this->error('Veuillez sélectionner un fichier à uploader ou saisir un lien URL.');
            return;
        }

        $data['statut_document'] = $data['statut_document'] ?? 'actif';
        $data['created_at_document'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE documents")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('etablisement_code', $cols)) $data['etablisement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Document enregistré avec succès !', ['redirect' => RACINE . 'document/list']);
        } else {
            $this->error('Erreur lors de l\'enregistrement du document');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_document');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Support chemin_fichier alias to lien_document
        if (!empty($data['chemin_fichier']) && empty($data['lien_document'])) {
            $data['lien_document'] = $data['chemin_fichier'];
        }

        // Gestion upload de fichier
        if (!empty($_FILES['fichier_upload']['name']) && $_FILES['fichier_upload']['error'] === UPLOAD_ERR_OK) {
            $uploadResult = $this->handleFileUpload($_FILES['fichier_upload']);
            if ($uploadResult['success']) {
                $data['lien_document'] = $uploadResult['path'];
                if (empty($data['type_document'])) {
                    $data['type_document'] = $uploadResult['type'];
                }
            } else {
                $this->error($uploadResult['message']); 
                return;
            }
        }

        $cols = $this->model->getCon()->query("DESCRIBE documents")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Document modifié avec succès !', ['redirect' => RACINE . 'document/list']);
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    /**
     * Gère l'upload d'un fichier document vers public/uploads/documents/
     */
    private function handleFileUpload(array $file): array
    {
        $maxSize = 50 * 1024 * 1024; // 50 Mo
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Le fichier dépasse la taille maximale de 50 Mo.'];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-zA-Z0-9]/', '', $ext);

        $allowedExts = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'rtf', 'odt', 'ods', 'odp', 'zip', 'rar', '7z', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        if (!in_array($ext, $allowedExts)) {
            return ['success' => false, 'message' => 'Extension non autorisée (.' . $ext . '). Formats acceptés : PDF, Word, Excel, PowerPoint, Zip, Images, etc.'];
        }

        // Détection de la catégorie de type
        $typeCategory = 'autre';
        if ($ext === 'pdf') $typeCategory = 'pdf';
        elseif (in_array($ext, ['doc', 'docx', 'odt', 'rtf', 'txt'])) $typeCategory = 'word';
        elseif (in_array($ext, ['xls', 'xlsx', 'ods'])) $typeCategory = 'excel';
        elseif (in_array($ext, ['ppt', 'pptx', 'odp'])) $typeCategory = 'powerpoint';
        elseif (in_array($ext, ['zip', 'rar', '7z'])) $typeCategory = 'archive';
        elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'])) $typeCategory = 'image';

        $newName = 'DOC_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $uploadDir = __DIR__ . '/../../public/uploads/documents/';
        if (!is_dir($uploadDir)) { 
            mkdir($uploadDir, 0755, true); 
        }
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'message' => 'Échec de l\'enregistrement du fichier uploadé sur le serveur.'];
        }

        // Chemin relatif accessible
        $relativePath = RACINE . 'uploads/documents/' . $newName;
        return [
            'success' => true, 
            'path' => $relativePath,
            'type' => $typeCategory,
            'filename' => $file['name'],
            'filesize' => $file['size']
        ];
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Item introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'document/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'document/list'); exit();
        }
        $this->loadView('../views/documents/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'document/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'document/list'); exit();
        }
        $this->loadView('../views/documents/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/documents/edit.php', ['item' => []]);
    }
}
