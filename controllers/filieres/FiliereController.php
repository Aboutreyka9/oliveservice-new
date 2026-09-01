<?php

class FiliereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelFiliere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/filieres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_filiere'];
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
        if (!empty($data['libelle_filiere'])) {
            if (!$this->checkUnique('filieres', 'libelle_filiere', $data['libelle_filiere'], 'Nom de la filière')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_filiere'])) {
            $data['code_filiere'] = $this->validator->generateCode('filieres', 'code_filiere', 'FIL-', 8);
        }
        $data['statut_filiere'] = $data['statut_filiere'] ?? 'actif';
        $data['created_at_filiere'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE filieres")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_filiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_filiere'])) {
            if (!$this->checkUnique('filieres', 'libelle_filiere', $data['libelle_filiere'], 'Nom de la filière', 'id_filiere', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE filieres")->fetchAll(PDO::FETCH_COLUMN);
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
            if (!$item) { header('Location: ' . RACINE . 'filiere/list'); exit(); }

            $filiereCode = $item['code_filiere'];

            // Cycles associés
            $stmtCyc = $this->model->getCon()->prepare("
                SELECT c.* FROM cycles c
                INNER JOIN filiere_cycles fc ON fc.cycle_code = c.code_cycle
                WHERE fc.filiere_code = ?
            ");
            $stmtCyc->execute([$filiereCode]);
            $cycles = $stmtCyc->fetchAll(PDO::FETCH_ASSOC);

            // Classes ouvertes pour cette filière
            $stmtCls = $this->model->getCon()->prepare("
                SELECT cl.*, n.libelle_niveau,
                       (SELECT COUNT(*) FROM inscriptions ins WHERE ins.classe_code = cl.code_classe AND ins.statut_inscription = 'actif') as nb_eleves
                FROM classes cl
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                WHERE cl.filiere_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmtCls->execute([$filiereCode]);
            $classes = $stmtCls->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques globales
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM classes WHERE filiere_code = ? AND statut_classe = 'actif') as total_classes,
                    (SELECT COUNT(*) FROM inscriptions ins 
                     JOIN classes cl ON cl.code_classe = ins.classe_code 
                     WHERE cl.filiere_code = ? AND ins.statut_inscription = 'actif') as total_etudiants
            ");
            $stmtStats->execute([$filiereCode, $filiereCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => count($classes), 'total_etudiants' => 0];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere/list'); exit();
        }
        $this->loadView('../views/filieres/details.php', [
            'item' => $item, 
            'cycles' => $cycles,
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
            if (!$item) { header('Location: ' . RACINE . 'filiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'filiere/list'); exit();
        }
        $this->loadView('../views/filieres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/filieres/edit.php', ['item' => []]);
    }
}