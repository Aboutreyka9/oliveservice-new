<?php

class ClasseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClasse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/classes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT c.*, 
                       f.libelle_filiere,
                       n.libelle_niveau
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                ORDER BY c.id_classe DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_classe'];
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

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUnique('classes', 'libelle_classe', $data['libelle_classe'], 'Nom de la classe')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_classe'])) {
            $data['code_classe'] = $this->validator->generateCode('classes', 'code_classe', 'CLA-', 8);
        }
        $data['statut_classe'] = $data['statut_classe'] ?? 'actif';
        $data['created_at_classe'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Classe créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de la classe.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_classe');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Génération automatique intelligente de secours si le champ libelle_classe est vide
        if (empty($data['libelle_classe']) && !empty($data['filiere_code']) && !empty($data['niveau_code'])) {
            $stmtF = $this->model->getCon()->prepare("SELECT libelle_filiere FROM filieres WHERE code_filiere = ?");
            $stmtF->execute([$data['filiere_code']]);
            $fName = $stmtF->fetchColumn();

            $stmtN = $this->model->getCon()->prepare("SELECT libelle_niveau FROM niveaux WHERE code_niveau = ?");
            $stmtN->execute([$data['niveau_code']]);
            $nName = $stmtN->fetchColumn();

            if ($fName && $nName) {
                $data['libelle_classe'] = trim($fName) . ' - ' . trim($nName);
            }
        }

        if (!empty($data['libelle_classe'])) {
            if (!$this->checkUnique('classes', 'libelle_classe', $data['libelle_classe'], 'Nom de la classe', 'id_classe', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE classes")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Classe modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification de la classe.');
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
                SELECT c.*, 
                       f.libelle_filiere,
                       n.libelle_niveau,
                       a.libelle_annee
                FROM classes c
                LEFT JOIN filieres f ON f.code_filiere = c.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = c.niveau_code
                LEFT JOIN annees a ON a.code_annee = c.annee_code
                WHERE c.id_classe = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("La classe demandée est introuvable.");
                return;
            }

            $classeCode = $item['code_classe'];

            // Liste des étudiants inscrits dans cette classe
            $stmtEtu = $this->model->getCon()->prepare("
                SELECT e.*, ins.created_at_inscription, ins.statut_inscription, ins.code_inscription
                FROM etudiants e
                INNER JOIN inscriptions ins ON ins.etudiant_code = e.code_etudiant
                WHERE ins.classe_code = ? AND (ins.statut_inscription != 'annule' OR ins.statut_inscription IS NULL)
                ORDER BY e.nom_etudiant ASC, e.prenom_etudiant ASC
            ");
            $stmtEtu->execute([$classeCode]);
            $etudiants = $stmtEtu->fetchAll(PDO::FETCH_ASSOC);

            // Liste des matières & enseignants assignés
            $stmtMat = $this->model->getCon()->prepare("
                SELECT em.*, m.libelle_matiere, m.code_matiere,
                       COALESCE(e.nom_enseignant, u.nom_user) as nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) as prenom_prof,
                       e.grade_enseignant
                FROM enseignant_matiere em
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                LEFT JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN users u ON u.code_user = em.user_code
                WHERE em.classe_code = ?
                ORDER BY m.libelle_matiere ASC
            ");
            $stmtMat->execute([$classeCode]);
            $matieres = $stmtMat->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ClasseController::details error: " . $e->getMessage());
            $this->renderNotFound("La classe demandée est introuvable.");
            return;
        }
        $this->loadView('../views/classes/details.php', [
            'item' => $item, 
            'etudiants' => $etudiants,
            'matieres' => $matieres,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'classe/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'classe/list'); exit();
        }
        $this->loadView('../views/classes/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/classes/edit.php', ['item' => []]);
    }
}