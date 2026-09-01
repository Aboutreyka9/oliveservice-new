<?php

class NoteController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelNote();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/notes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_note'];
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
        if (empty($data['code_note'])) {
            $data['code_note'] = $this->validator->generateCode('notes', 'code_note', 'NOT-', 8);
        }
        $data['statut_note'] = $data['statut_note'] ?? 'actif';
        $data['created_at_note'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_note');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE notes")->fetchAll(PDO::FETCH_COLUMN);
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
                SELECT n.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       m.libelle_matiere,
                       cl.libelle_classe, f.libelle_filiere, niv.libelle_niveau,
                       s.libelle_semestre, a.libelle_annee,
                       COALESCE(em.coefficient_enseignant_matiere, em.coefficient, 1.0) as coef_cours
                FROM notes n
                LEFT JOIN inscriptions ins ON ins.code_inscription = n.inscription_code
                LEFT JOIN etudiants e ON e.code_etudiant = ins.etudiant_code
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux niv ON niv.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = n.matiere_code
                LEFT JOIN semestres s ON s.code_semestre = n.semestre_code
                LEFT JOIN annees a ON a.code_annee = n.annee_code
                LEFT JOIN enseignant_matiere em ON (em.classe_code = ins.classe_code AND em.matiere_code = n.matiere_code)
                WHERE n.id_note = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'note/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("NoteController::details error: " . $e->getMessage());
            $this->renderNotFound("L'évaluation / note demandée est introuvable.");
        }
        $this->loadView('../views/notes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'note/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'note/list'); exit();
        }
        $this->loadView('../views/notes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/notes/edit.php', ['item' => []]);
    }
}
