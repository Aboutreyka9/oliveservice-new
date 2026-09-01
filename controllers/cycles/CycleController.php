<?php

class CycleController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCycle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/cycles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_cycle'];
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
        if (!empty($data['libelle_cycle'])) {
            if (!$this->checkUnique('cycles', 'libelle_cycle', $data['libelle_cycle'], 'Libelle du cycle')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_cycle'])) {
            $data['code_cycle'] = $this->validator->generateCode('cycles', 'code_cycle', 'CYC-', 8);
        }
        $data['statut_cycle'] = $data['statut_cycle'] ?? 'actif';
        $data['created_at_cycle'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE cycles")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_cycle');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_cycle'])) {
            if (!$this->checkUnique('cycles', 'libelle_cycle', $data['libelle_cycle'], 'Libelle du cycle', 'id_cycle', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE cycles")->fetchAll(PDO::FETCH_COLUMN);
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
            if (!$item) { header('Location: ' . RACINE . 'cycle/list'); exit(); }

            $cycleCode = $item['code_cycle'];

            // Filières associées à ce cycle
            $stmtFil = $this->model->getCon()->prepare("
                SELECT f.*,
                       (SELECT COUNT(*) FROM classes cl WHERE cl.filiere_code = f.code_filiere AND cl.statut_classe = 'actif') as nb_classes
                FROM filieres f
                INNER JOIN filiere_cycles fc ON fc.filiere_code = f.code_filiere
                WHERE fc.cycle_code = ?
                ORDER BY f.libelle_filiere ASC
            ");
            $stmtFil->execute([$cycleCode]);
            $filieres = $stmtFil->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques du cycle
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    COUNT(DISTINCT fc.filiere_code) as total_filieres,
                    (SELECT COUNT(*) FROM classes cl 
                     WHERE cl.filiere_code IN (SELECT fc2.filiere_code FROM filiere_cycles fc2 WHERE fc2.cycle_code = ?) 
                       AND cl.statut_classe = 'actif') as total_classes,
                    (SELECT COUNT(*) FROM inscriptions ins 
                     JOIN classes cl2 ON cl2.code_classe = ins.classe_code
                     WHERE cl2.filiere_code IN (SELECT fc3.filiere_code FROM filiere_cycles fc3 WHERE fc3.cycle_code = ?)
                       AND ins.statut_inscription = 'actif') as total_etudiants
                FROM filiere_cycles fc
                WHERE fc.cycle_code = ?
            ");
            $stmtStats->execute([$cycleCode, $cycleCode, $cycleCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [
                'total_filieres' => count($filieres), 'total_classes' => 0, 'total_etudiants' => 0
            ];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cycle/list'); exit();
        }
        $this->loadView('../views/cycles/details.php', [
            'item' => $item, 
            'stats' => $stats,
            'filieres' => $filieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'cycle/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'cycle/list'); exit();
        }
        $this->loadView('../views/cycles/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/cycles/edit.php', ['item' => []]);
    }
}