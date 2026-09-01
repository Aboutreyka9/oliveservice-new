<?php

class EnseignantController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEnseignant();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/enseignants/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT e.*, 
                       COALESCE(e.nom_enseignant, u.nom_user) AS nom_enseignant, 
                       COALESCE(e.prenom_enseignant, u.prenom_user) AS prenom_enseignant, 
                       COALESCE(e.email_enseignant, u.email_user) AS email_enseignant, 
                       COALESCE(e.telephone_enseignant, u.telephone_user) AS telephone_enseignant, 
                       COALESCE(e.sexe_enseignant, u.sexe_user, 'M') AS sexe_enseignant
                FROM enseignants e
                LEFT JOIN users u ON u.code_user = e.user_code
                ORDER BY e.id_enseignant DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_enseignant'];
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

        $nom = trim($data['nom_enseignant'] ?? '');
        $prenom = trim($data['prenom_enseignant'] ?? '');
        $email = trim($data['email_enseignant'] ?? '');
        $telephone = Validator::cleanPhone($data['telephone_enseignant'] ?? '');
        $passwordRaw = trim($data['password_enseignant'] ?? '123456');
        $userCode = !empty($data['user_code']) ? trim($data['user_code']) : null;
        $etabCode = '5454544456';

        if (empty($nom)) {
            $this->error('Le nom de l\'enseignant est obligatoire.');
            return;
        }

        if (!empty($email)) {
            $stmtCheckEmail = $this->model->getCon()->prepare("SELECT id_enseignant FROM enseignants WHERE email_enseignant = ?");
            $stmtCheckEmail->execute([$email]);
            if ($stmtCheckEmail->fetch()) {
                $this->error('Cette adresse email est déjà enregistrée pour un enseignant.');
                return;
            }
        }

        $codeEnseignant = $this->validator->generateCode('enseignants', 'code_enseignant', 'ENS-', 8);
        $rawPassword = !empty($data['password_enseignant']) ? trim($data['password_enseignant']) : ('ENS' . rand(100000, 999999));
        $passwordHashed = password_hash($rawPassword, PASSWORD_DEFAULT);
        $numeroAutorisation = trim($data['numero_autorisation'] ?? '');

        // Insertion autonome et directe dans la table enseignants
        $stmtEns = $this->model->getCon()->prepare("
            INSERT INTO enseignants (
                code_enseignant, nom_enseignant, prenom_enseignant, email_enseignant, telephone_enseignant, 
                sexe_enseignant, password_enseignant, user_code, grade_enseignant, type_contrat, numero_autorisation, taux_horaire, 
                etablissement_code, statut_enseignant, created_at_enseignant
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
        ");
        $success = $stmtEns->execute([
            $codeEnseignant,
            $nom,
            $prenom,
            $email ?: null,
            $telephone ?: null,
            $data['sexe_enseignant'] ?? 'M',
            $passwordHashed,
            $userCode,
            $data['grade_enseignant'] ?? 'Enseignant',
            $data['type_contrat'] ?? 'permanent',
            $numeroAutorisation ?: null,
            !empty($data['taux_horaire']) ? (float)$data['taux_horaire'] : 0.00,
            $etabCode
        ]);

        if ($success) {
            $idDisplay = $email ?: ($telephone ?: $nom);
            $this->success("Enseignant enregistré avec succès ! Identifiant : <strong>{$idDisplay}</strong> | Mot de passe généré : <strong style='color:#15803D;'>{$rawPassword}</strong>", ['password' => $rawPassword]);
        } else {
            $this->error('Erreur lors de l\'enregistrement de l\'enseignant.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_enseignant');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $item = $this->model->getById($id);
        if (!$item) { $this->error('Enseignant introuvable'); return; }

        $data = $_POST;
        unset($data['csrf_token']);

        $nom = trim($data['nom_enseignant'] ?? '');
        $prenom = trim($data['prenom_enseignant'] ?? '');
        $email = trim($data['email_enseignant'] ?? '');
        $telephone = Validator::cleanPhone($data['telephone_enseignant'] ?? '');
        $userCode = !empty($data['user_code']) ? trim($data['user_code']) : null;
        $numeroAutorisation = trim($data['numero_autorisation'] ?? '');

        if (empty($nom)) {
            $this->error('Le nom de l\'enseignant est obligatoire.');
            return;
        }

        if (!empty($email)) {
            $stmtCheck = $this->model->getCon()->prepare("SELECT id_enseignant FROM enseignants WHERE email_enseignant = ? AND id_enseignant != ?");
            $stmtCheck->execute([$email, $id]);
            if ($stmtCheck->fetch()) {
                $this->error('Cette adresse email est déjà enregistrée pour un autre enseignant.');
                return;
            }
        }

        $sqlUpdate = "
            UPDATE enseignants 
            SET nom_enseignant = ?, prenom_enseignant = ?, email_enseignant = ?, telephone_enseignant = ?, 
                sexe_enseignant = ?, user_code = ?, grade_enseignant = ?, type_contrat = ?, numero_autorisation = ?, 
                statut_enseignant = ?, updated_at_enseignant = NOW()";
        $params = [
            $nom,
            $prenom,
            $email ?: null,
            $telephone ?: null,
            $data['sexe_enseignant'] ?? 'M',
            $userCode,
            $data['grade_enseignant'] ?? 'Enseignant',
            $data['type_contrat'] ?? 'permanent',
            $numeroAutorisation ?: null,
            $data['statut_enseignant'] ?? 'actif'
        ];

        if (!empty($data['password_enseignant'])) {
            $sqlUpdate .= ", password_enseignant = ?";
            $params[] = password_hash(trim($data['password_enseignant']), PASSWORD_DEFAULT);
        }

        $sqlUpdate .= " WHERE id_enseignant = ?";
        $params[] = $id;

        $stmt = $this->model->getCon()->prepare($sqlUpdate);
        $success = $stmt->execute($params);

        if ($success) {
            $this->success('Profil enseignant modifié avec succès !');
        } else {
            $this->error('Erreur lors de la modification.');
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
                SELECT e.*, 
                       COALESCE(e.nom_enseignant, u.nom_user) AS nom_enseignant, 
                       COALESCE(e.prenom_enseignant, u.prenom_user) AS prenom_enseignant, 
                       COALESCE(e.email_enseignant, u.email_user) AS email_enseignant, 
                       COALESCE(e.telephone_enseignant, u.telephone_user) AS telephone_enseignant, 
                       COALESCE(e.sexe_enseignant, u.sexe_user, 'M') AS sexe_enseignant
                FROM enseignants e
                LEFT JOIN users u ON u.code_user = e.user_code
                WHERE e.id_enseignant = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'enseignant/list'); exit(); }

            // Récupérer les cours / matières affectés
            $stmtCours = $this->model->getCon()->prepare("
                SELECT em.*, m.libelle_matiere, cl.libelle_classe
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN classes cl ON cl.code_classe = em.classe_code
                WHERE em.enseignant_code = ?
                ORDER BY cl.libelle_classe ASC, m.libelle_matiere ASC
            ");
            $stmtCours->execute([$item['code_enseignant']]);
            $cours = $stmtCours->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant/list'); exit();
        }
        $this->loadView('../views/enseignants/details.php', [
            'item' => $item, 
            'cours' => $cours,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT e.*, 
                       COALESCE(e.nom_enseignant, u.nom_user) AS nom_enseignant, 
                       COALESCE(e.prenom_enseignant, u.prenom_user) AS prenom_enseignant, 
                       COALESCE(e.email_enseignant, u.email_user) AS email_enseignant, 
                       COALESCE(e.telephone_enseignant, u.telephone_user) AS telephone_enseignant, 
                       COALESCE(e.sexe_enseignant, u.sexe_user, 'M') AS sexe_enseignant
                FROM enseignants e
                LEFT JOIN users u ON u.code_user = e.user_code
                WHERE e.id_enseignant = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'enseignant/list'); exit(); }
            $users = (new ModelUser())->getAll();
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'enseignant/list'); exit();
        }
        $this->loadView('../views/enseignants/edit.php', [
            'item' => $item, 
            'users' => $users,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $users = (new ModelUser())->getAll();
        $this->loadView('../views/enseignants/edit.php', [
            'item' => [],
            'users' => $users
        ]);
    }
}