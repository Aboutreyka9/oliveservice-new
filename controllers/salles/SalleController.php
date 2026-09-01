<?php

class SalleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSalle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/salles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_salle'];
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
        if (!empty($data['libelle_salle'])) {
            if (!$this->checkUnique('salles', 'libelle_salle', $data['libelle_salle'], 'Nom de la salle')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_salle'])) {
            $data['code_salle'] = $this->validator->generateCode('salles', 'code_salle', 'SAL-', 8);
        }
        $data['statut_salle'] = $data['statut_salle'] ?? 'actif';
        $data['created_at_salle'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE salles")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_salle');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_salle'])) {
            if (!$this->checkUnique('salles', 'libelle_salle', $data['libelle_salle'], 'Nom de la salle', 'id_salle', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE salles")->fetchAll(PDO::FETCH_COLUMN);
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
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'salle/list'); exit(); }

            $salleCode = $item['code_salle'];

            // Planning / Emplois du temps dans cette salle
            $stmtEdt = $this->model->getCon()->prepare("
                SELECT edt.*, cl.libelle_classe, m.libelle_matiere,
                       COALESCE(e.nom_enseignant, u.nom_user) as nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) as prenom_prof
                FROM emplois_temps edt
                LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
                LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
                LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN users u ON u.code_user = edt.user_code
                WHERE edt.salle_code = ? AND (edt.statut_emploi = 'actif' OR edt.statut_emploi IS NULL)
                ORDER BY edt.jour ASC, edt.heure_debut ASC
            ");
            $stmtEdt->execute([$salleCode]);
            $planning = $stmtEdt->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("SalleController::details error: " . $e->getMessage());
            $this->renderNotFound("La salle demandée est introuvable.");
        }
        $this->loadView('../views/salles/details.php', [
            'item' => $item, 
            'planning' => $planning,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'salle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'salle/list'); exit();
        }
        $this->loadView('../views/salles/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/salles/edit.php', ['item' => []]);
    }
}