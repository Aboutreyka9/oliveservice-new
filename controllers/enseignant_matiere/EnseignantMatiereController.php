<?php

class EnseignantMatiereController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEnseignantMatiere();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/enseignant_matiere/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT em.*, 
                       m.libelle_matiere,
                       cl.libelle_classe,
                       CONCAT(COALESCE(e.nom_enseignant, u.nom_user, ''), ' ', COALESCE(e.prenom_enseignant, u.prenom_user, '')) AS enseignant_nom,
                       e.code_enseignant
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = e.user_code
                ORDER BY em.id_enseignant_matiere DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_enseignant_matiere'];
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

        if (empty($data['enseignant_code']) || empty($data['matiere_code']) || empty($data['classe_code'])) {
            $this->error('Veuillez sélectionner un enseignant, une matière et une classe.');
            return;
        }

        $data['coefficient'] = !empty($data['coefficient']) ? (float)$data['coefficient'] : 1.0;
        $data['statut_enseignant_matiere'] = $data['statut_enseignant_matiere'] ?? 'actif';
        $data['created_at_enseignant_matiere'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE enseignant_matiere")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Affectation de cours créée avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_enseignant_matiere');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['coefficient'])) {
            $data['coefficient'] = (float)$data['coefficient'];
        }

        $data['updated_at_enseignant_matiere'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE enseignant_matiere")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Affectation modifiée avec succès!');
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
                SELECT em.*, 
                       m.libelle_matiere,
                       cl.libelle_classe,
                       f.libelle_filiere,
                       n.libelle_niveau,
                       COALESCE(e.nom_enseignant, u.nom_user) AS nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) AS prenom_prof,
                       e.grade_enseignant,
                       e.email_enseignant,
                       e.telephone_enseignant
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = em.user_code
                WHERE em.id_enseignant_matiere = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'enseignant_matiere/list'); exit(); }

            // Notes saisies pour ce cours dans cette classe
            $stmtNotes = $this->model->getCon()->prepare("
                SELECT COUNT(*) FROM notes n
                JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
                WHERE ins.classe_code = ? AND n.matiere_code = ? AND (n.statut_note = 'actif' OR n.statut_note IS NULL)
            ");
            $stmtNotes->execute([$item['classe_code'], $item['matiere_code']]);
            $nbNotes = (int)$stmtNotes->fetchColumn();

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("EnseignantMatiereController::details error: " . $e->getMessage());
            $this->renderNotFound("L'affectation de cours demandée est introuvable.");
        }
        $this->loadView('../views/enseignant_matiere/details.php', [
            'item' => $item, 
            'nbNotes' => $nbNotes,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'enseignant_matiere/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant_matiere/list'); exit();
        }
        $this->loadView('../views/enseignant_matiere/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/enseignant_matiere/edit.php', ['item' => []]);
    }
}
