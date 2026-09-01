<?php

class MatiereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelMatiere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/matieres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_matiere'];
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
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_matiere'])) {
            $data['code_matiere'] = $this->validator->generateCode('matieres', 'code_matiere', 'MAT-', 8);
        }
        $data['statut_matiere'] = $data['statut_matiere'] ?? 'actif';
        $data['created_at_matiere'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE matieres")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_matiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_matiere'])) {
            if (!$this->checkUnique('matieres', 'libelle_matiere', $data['libelle_matiere'], 'Nom de la matiere', 'id_matiere', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE matieres")->fetchAll(PDO::FETCH_COLUMN);
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
            if (!$item) { header('Location: ' . RACINE . 'matiere/list'); exit(); }

            $matiereCode = $item['code_matiere'];

            // Classes et Enseignants où cette matière est enseignée
            $stmtAffectations = $this->model->getCon()->prepare("
                SELECT em.*, cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       COALESCE(e.nom_enseignant, u.nom_user) as nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) as prenom_prof,
                       e.grade_enseignant
                FROM enseignant_matiere em
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = em.user_code
                WHERE em.matiere_code = ?
                ORDER BY cl.libelle_classe ASC
            ");
            $stmtAffectations->execute([$matiereCode]);
            $affectations = $stmtAffectations->fetchAll(PDO::FETCH_ASSOC);

            // Statistiques globales de la matière
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    COUNT(DISTINCT classe_code) as total_classes,
                    COUNT(DISTINCT COALESCE(enseignant_code, user_code)) as total_profs,
                    (SELECT COUNT(*) FROM notes WHERE matiere_code = ? AND statut_note = 'actif') as total_notes
                FROM enseignant_matiere
                WHERE matiere_code = ?
            ");
            $stmtStats->execute([$matiereCode, $matiereCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_classes' => count($affectations), 'total_profs' => 0, 'total_notes' => 0];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'matiere/list'); exit();
        }
        $this->loadView('../views/matieres/details.php', [
            'item' => $item, 
            'affectations' => $affectations,
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
            if (!$item) { header('Location: ' . RACINE . 'matiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'matiere/list'); exit();
        }
        $this->loadView('../views/matieres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/matieres/edit.php', ['item' => []]);
    }
}