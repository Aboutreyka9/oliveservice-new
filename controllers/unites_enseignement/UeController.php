<?php

class UeController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelUe();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/unites_enseignement/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_ue'];
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
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_ue'])) {
            if (!$this->checkUnique('unites_enseignement', 'libelle_ue', $data['libelle_ue'], 'Libelle de l UE')) return;
        }
        if (!empty($data['code_ue'])) {
            if (!$this->checkUnique('unites_enseignement', 'code_ue', $data['code_ue'], 'Code UE')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_ue'])) {
            $data['code_ue'] = $this->validator->generateCode('unites_enseignement', 'code_ue', 'UE-', 8);
        }
        $data['statut_ue'] = $data['statut_ue'] ?? 'actif';
        $data['created_at_ue'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE unites_enseignement")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_ue');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_ue'])) {
            if (!$this->checkUnique('unites_enseignement', 'libelle_ue', $data['libelle_ue'], 'Libelle de l UE', 'id_ue', $id)) return;
        }
        if (!empty($data['code_ue'])) {
            if (!$this->checkUnique('unites_enseignement', 'code_ue', $data['code_ue'], 'Code UE', 'id_ue', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE unites_enseignement")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
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
            $stmt = $this->model->getCon()->prepare("
                SELECT ue.*, f.libelle_filiere
                FROM unites_enseignement ue
                LEFT JOIN filieres f ON f.code_filiere = ue.filiere_code
                WHERE ue.id_ue = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'ue/list'); exit(); }

            // Matières associées à cette UE
            $stmtMat = $this->model->getCon()->prepare("
                SELECT m.* FROM matieres m 
                WHERE m.ue_code = ? OR m.filiere_code = ?
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtMat->execute([$item['code_ue'], $item['filiere_code']]);
            $matieres = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'ue/list'); exit();
        }
        $this->loadView('../views/unites_enseignement/details.php', [
            'item' => $item, 
            'matieres' => $matieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'ue/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'ue/list'); exit();
        }
        $this->loadView('../views/unites_enseignement/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/unites_enseignement/edit.php', ['item' => []]);
    }
}