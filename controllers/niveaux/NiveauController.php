<?php

class NiveauController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelNiveau();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/niveaux/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_niveau'];
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
        if (!empty($data['libelle_niveau'])) {
            if (!$this->checkUnique('niveaux', 'libelle_niveau', $data['libelle_niveau'], 'Libelle du niveau')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_niveau'])) {
            $data['code_niveau'] = $this->validator->generateCode('niveaux', 'code_niveau', 'NIV-', 8);
        }
        $data['statut_niveau'] = $data['statut_niveau'] ?? 'actif';
        $data['created_at_niveau'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE niveaux")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_niveau');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_niveau'])) {
            if (!$this->checkUnique('niveaux', 'libelle_niveau', $data['libelle_niveau'], 'Libelle du niveau', 'id_niveau', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE niveaux")->fetchAll(PDO::FETCH_COLUMN);
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
            if (!$item) { header('Location: ' . RACINE . 'niveau/list'); exit(); }

            $niveauCode = $item['code_niveau'];

            // Classes rattachées à ce niveau
            $stmtCls = $this->model->getCon()->prepare("
                SELECT cl.*, f.libelle_filiere,
                       (SELECT COUNT(*) FROM inscriptions ins WHERE ins.classe_code = cl.code_classe AND ins.statut_inscription = 'actif') as nb_eleves
                FROM classes cl
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                WHERE cl.niveau_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmtCls->execute([$niveauCode]);
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques globales
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM classes WHERE niveau_code = ? AND statut_classe = 'actif') as total_classes,
                    (SELECT COUNT(*) FROM inscriptions ins 
                     JOIN classes cl ON cl.code_classe = ins.classe_code 
                     WHERE cl.niveau_code = ? AND ins.statut_inscription = 'actif') as total_etudiants
            ");
            $stmtStats->execute([$niveauCode, $niveauCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => count($classes), 'total_etudiants' => 0];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'niveau/list'); exit();
        }
        $this->loadView('../views/niveaux/details.php', [
            'item' => $item, 
            'classes' => $classes,
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
            if (!$item) { header('Location: ' . RACINE . 'niveau/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'niveau/list'); exit();
        }
        $this->loadView('../views/niveaux/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/niveaux/edit.php', ['item' => []]);
    }
}