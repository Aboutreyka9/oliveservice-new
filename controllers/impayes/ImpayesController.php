<?php

class ImpayesController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelImpayes();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/impayes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_relance'];
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

        if (empty($data['code_relance'])) {
            $data['code_relance'] = $this->validator->generateCode('relances_impayes', 'code_relance', 'REL-', 8);
        }
        $data['statut_relance'] = 'envoye';
        $data['created_at_relance'] = date('Y-m-d H:i:s');
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $cols = $this->model->getCon()->query("DESCRIBE relances_impayes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Relance d\'impayé enregistrée et expédiée avec succès!');
        } else {
            $this->error('Erreur lors de l\'enregistrement de la relance');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_inscription');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE inscriptions")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT r.*, 
                       e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant, e.telephone_etudiant, e.email_etudiant,
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       a.libelle_annee,
                       u.nom_user, u.prenom_user
                FROM relances_impayes r
                LEFT JOIN etudiants e ON e.code_etudiant = r.etudiant_code
                LEFT JOIN inscriptions ins ON (ins.code_inscription = r.inscription_code OR (ins.etudiant_code = r.etudiant_code AND ins.statut_inscription = 'actif'))
                LEFT JOIN classes cl ON cl.code_classe = ins.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN annees a ON a.code_annee = r.annee_code
                LEFT JOIN users u ON u.code_user = r.user_code
                WHERE r.id_relance = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'impayes/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'impayes/list'); exit();
        }
        $this->loadView('../views/impayes/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'impayes/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'impayes/list'); exit();
        }
        $this->loadView('../views/impayes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/impayes/edit.php', ['item' => []]);
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['en_attente', 'envoye', 'regle'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_relance');
            } else {
                $success = $this->model->toggleStatus($id);
            }
            if ($success) {
                $this->success('Statut de la relance mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Relance introuvable');
        }
    }
}
