<?php

class ParentController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelParent();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/parents/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_parent'];
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
        if (!empty($data['telephone_parent'])) {
            if (!$this->checkUnique('parents', 'telephone_parent', $data['telephone_parent'], 'Telephone du parent')) return;
        }
        if (!empty($data['email_parent'])) {
            if (!$this->checkUnique('parents', 'email_parent', $data['email_parent'], 'Email du parent')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_parent'])) {
            $data['code_parent'] = $this->validator->generateCode('parents', 'code_parent', 'PAR-', 8);
        }
        $data['statut_parent'] = $data['statut_parent'] ?? 'actif';
        $data['created_at_parent'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE parents")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_parent');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['telephone_parent'])) {
            if (!$this->checkUnique('parents', 'telephone_parent', $data['telephone_parent'], 'Telephone du parent', 'id_parent', $id)) return;
        }
        if (!empty($data['email_parent'])) {
            if (!$this->checkUnique('parents', 'email_parent', $data['email_parent'], 'Email du parent', 'id_parent', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE parents")->fetchAll(PDO::FETCH_COLUMN);
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
                SELECT p.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       cl.libelle_classe
                FROM parents p
                LEFT JOIN etudiants e ON e.code_etudiant = p.etudiant_code
                LEFT JOIN inscriptions ins ON (ins.etudiant_code = e.code_etudiant AND ins.statut_inscription = 'actif')
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                WHERE p.id_parent = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'parent/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'parent/list'); exit();
        }
        $this->loadView('../views/parents/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'parent/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'parent/list'); exit();
        }
        $this->loadView('../views/parents/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/parents/edit.php', ['item' => []]);
    }
}