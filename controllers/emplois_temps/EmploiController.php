<?php

class EmploiController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelEmploi();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/emplois_temps/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_emploi'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function getAssignedTeacher()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $matiereCode = trim($_GET['matiere_code'] ?? ($_POST['matiere_code'] ?? ''));

        if (empty($classeCode) || empty($matiereCode)) {
            $this->json(['status' => 0, 'message' => 'Classe ou matière non spécifiée']);
            return;
        }

        $db = $this->model->getCon();

        // 1. Recherche précise par classe_code + matiere_code
        $stmt = $db->prepare("
            SELECT em.*, 
                   e.nom_enseignant, e.prenom_enseignant, e.code_enseignant, e.grade_enseignant,
                   m.libelle_matiere, cl.libelle_classe
            FROM enseignant_matiere em
            JOIN enseignants e ON e.code_enseignant = em.enseignant_code
            LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
            LEFT JOIN classes cl ON cl.code_classe = em.classe_code
            WHERE em.classe_code = ? AND em.matiere_code = ? AND em.statut_enseignant_matiere = 'actif'
            LIMIT 1
        ");
        $stmt->execute([$classeCode, $matiereCode]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2. Recherche de secours par matière
        if (!$row) {
            $stmtGlobal = $db->prepare("
                SELECT em.*, 
                       e.nom_enseignant, e.prenom_enseignant, e.code_enseignant, e.grade_enseignant,
                       m.libelle_matiere
                FROM enseignant_matiere em
                JOIN enseignants e ON e.code_enseignant = em.enseignant_code
                LEFT JOIN matieres m ON m.code_matiere = em.matiere_code
                WHERE em.matiere_code = ? AND em.statut_enseignant_matiere = 'actif'
                LIMIT 1
            ");
            $stmtGlobal->execute([$matiereCode]);
            $row = $stmtGlobal->fetch(PDO::FETCH_ASSOC);
        }

        if ($row) {
            $nomComplet = trim(($row['nom_enseignant'] ?? '') . ' ' . ($row['prenom_enseignant'] ?? ''));
            $this->json([
                'status' => 1,
                'data' => [
                    'enseignant_code' => $row['code_enseignant'],
                    'nom_complet' => $nomComplet,
                    'grade' => $row['grade_enseignant'] ?? '',
                    'matiere' => $row['libelle_matiere'] ?? ''
                ]
            ]);
        } else {
            $this->json(['status' => 0, 'message' => 'Aucun enseignant spécifiquement affecté']);
        }
    }

    public function getScheduleConflicts(string $classeCode, string $salleCode, string $enseignantCode, string $jour, string $heureDebut, string $heureFin, $excludeId = null): array
    {
        $conflicts = [];
        $jour = strtolower(trim($jour));
        $heureDebut = trim($heureDebut);
        $heureFin = trim($heureFin);

        if (empty($jour) || empty($heureDebut) || empty($heureFin)) {
            return $conflicts;
        }

        if (strtotime($heureFin) <= strtotime($heureDebut)) {
            $conflicts[] = [
                'type' => 'horaire',
                'title' => 'Incohérence des horaires',
                'message' => "L'heure de fin ($heureFin) doit être strictement postérieure à l'heure de début ($heureDebut)."
            ];
            return $conflicts;
        }

        $db = $this->model->getCon();

        $baseSql = "
            SELECT edt.*, 
                   cl.libelle_classe, 
                   m.libelle_matiere, 
                   s.libelle_salle, 
                   COALESCE(CONCAT(e.nom_enseignant, ' ', e.prenom_enseignant), u.nom_user, 'Enseignant') AS nom_prof
            FROM emplois_temps edt
            LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
            LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
            LEFT JOIN salles s ON s.code_salle = edt.salle_code
            LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
            LEFT JOIN users u ON u.code_user = edt.user_code
            WHERE edt.statut_emploi = 'actif'
              AND LOWER(edt.jour) = ?
              AND (edt.heure_debut < ? AND edt.heure_fin > ?)
        ";
        $excludeSql = $excludeId ? " AND edt.id_emploi != " . (int)$excludeId : "";

        // 1. Vérification Conflit Salle
        if (!empty($salleCode)) {
            $stmtS = $db->prepare($baseSql . " AND edt.salle_code = ?" . $excludeSql);
            $stmtS->execute([$jour, $heureFin, $heureDebut, $salleCode]);
            $rowS = $stmtS->fetch(PDO::FETCH_ASSOC);
            if ($rowS) {
                $salleNom = $rowS['libelle_salle'] ?: 'Cette salle';
                $debutConf = substr($rowS['heure_debut'], 0, 5);
                $finConf = substr($rowS['heure_fin'], 0, 5);
                $conflicts[] = [
                    'type' => 'salle',
                    'title' => 'Salle déjà occupée',
                    'message' => "La salle <strong>" . htmlspecialchars($salleNom) . "</strong> est déjà occupée de <strong>$debutConf à $finConf</strong> par la classe <em>" . htmlspecialchars($rowS['libelle_classe'] ?: 'Autre classe') . "</em> (" . htmlspecialchars($rowS['libelle_matiere'] ?: 'Cours') . ")."
                ];
            }
        }

        // 2. Vérification Conflit Enseignant
        if (!empty($enseignantCode)) {
            $stmtE = $db->prepare($baseSql . " AND edt.enseignant_code = ?" . $excludeSql);
            $stmtE->execute([$jour, $heureFin, $heureDebut, $enseignantCode]);
            $rowE = $stmtE->fetch(PDO::FETCH_ASSOC);
            if ($rowE) {
                $profNom = $rowE['nom_prof'] ?: 'Cet enseignant';
                $debutConf = substr($rowE['heure_debut'], 0, 5);
                $finConf = substr($rowE['heure_fin'], 0, 5);
                $conflicts[] = [
                    'type' => 'enseignant',
                    'title' => 'Enseignant non disponible',
                    'message' => "L'enseignant <strong>" . htmlspecialchars($profNom) . "</strong> donne déjà cours de <strong>$debutConf à $finConf</strong> avec la classe <em>" . htmlspecialchars($rowE['libelle_classe'] ?: 'Autre classe') . "</em> en salle " . htmlspecialchars($rowE['libelle_salle'] ?: 'N/A') . "."
                ];
            }
        }

        // 3. Vérification Conflit Classe
        if (!empty($classeCode)) {
            $stmtC = $db->prepare($baseSql . " AND edt.classe_code = ?" . $excludeSql);
            $stmtC->execute([$jour, $heureFin, $heureDebut, $classeCode]);
            $rowC = $stmtC->fetch(PDO::FETCH_ASSOC);
            if ($rowC) {
                $classeNom = $rowC['libelle_classe'] ?: 'Cette classe';
                $debutConf = substr($rowC['heure_debut'], 0, 5);
                $finConf = substr($rowC['heure_fin'], 0, 5);
                $conflicts[] = [
                    'type' => 'classe',
                    'title' => 'Classe déjà en cours',
                    'message' => "La classe <strong>" . htmlspecialchars($classeNom) . "</strong> a déjà un cours de <em>" . htmlspecialchars($rowC['libelle_matiere'] ?: 'Matière') . "</em> programmé de <strong>$debutConf à $finConf</strong>."
                ];
            }
        }

        return $conflicts;
    }

    public function checkScheduleConflicts()
    {
        $this->requireAuth();
        $classeCode = trim($_GET['classe_code'] ?? ($_POST['classe_code'] ?? ''));
        $salleCode = trim($_GET['salle_code'] ?? ($_POST['salle_code'] ?? ''));
        $enseignantCode = trim($_GET['enseignant_code'] ?? ($_POST['enseignant_code'] ?? ''));
        $jour = trim($_GET['jour'] ?? ($_POST['jour'] ?? ''));
        $heureDebut = trim($_GET['heure_debut'] ?? ($_POST['heure_debut'] ?? ''));
        $heureFin = trim($_GET['heure_fin'] ?? ($_POST['heure_fin'] ?? ''));
        $excludeId = $_GET['id_emploi'] ?? ($_POST['id_emploi'] ?? null);

        $conflicts = $this->getScheduleConflicts($classeCode, $salleCode, $enseignantCode, $jour, $heureDebut, $heureFin, $excludeId);

        $this->json([
            'status' => 1,
            'has_conflict' => !empty($conflicts),
            'conflicts' => $conflicts
        ]);
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

        // Contrôle de conflits côté serveur
        $conflicts = $this->getScheduleConflicts(
            $data['classe_code'] ?? '',
            $data['salle_code'] ?? '',
            $data['enseignant_code'] ?? '',
            $data['jour'] ?? '',
            $data['heure_debut'] ?? '',
            $data['heure_fin'] ?? ''
        );

        if (!empty($conflicts)) {
            $msg = strip_tags($conflicts[0]['message']);
            $this->error("Impossible d'enregistrer : $msg");
            return;
        }

        if (empty($data['code_emploi'])) {
            $data['code_emploi'] = $this->validator->generateCode('emplois_temps', 'code_emploi', 'EMP-', 8);
        }
        $data['jour'] = strtolower($data['jour'] ?? 'lundi');
        $data['statut_emploi'] = $data['statut_emploi'] ?? 'actif';
        $data['created_at_emploi'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE emplois_temps")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Créneau horaire planifié avec succès!');
        } else {
            $this->error('Erreur lors de la création');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_emploi');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle de conflits côté serveur
        $conflicts = $this->getScheduleConflicts(
            $data['classe_code'] ?? '',
            $data['salle_code'] ?? '',
            $data['enseignant_code'] ?? '',
            $data['jour'] ?? '',
            $data['heure_debut'] ?? '',
            $data['heure_fin'] ?? '',
            $id
        );

        if (!empty($conflicts)) {
            $msg = strip_tags($conflicts[0]['message']);
            $this->error("Impossible de modifier : $msg");
            return;
        }

        if (isset($data['jour'])) {
            $data['jour'] = strtolower($data['jour']);
        }

        $cols = $this->model->getCon()->query("DESCRIBE emplois_temps")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Créneau horaire mis à jour avec succès!');
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
                SELECT edt.*, 
                       cl.libelle_classe, f.libelle_filiere, n.libelle_niveau,
                       m.libelle_matiere,
                       s.libelle_salle, s.capacite_salle,
                       COALESCE(e.nom_enseignant, u.nom_user) as nom_prof,
                       COALESCE(e.prenom_enseignant, u.prenom_user) as prenom_prof,
                       e.grade_enseignant
                FROM emplois_temps edt
                LEFT JOIN classes cl ON cl.code_classe = edt.classe_code
                LEFT JOIN filieres f ON f.code_filiere = cl.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = cl.niveau_code
                LEFT JOIN matieres m ON m.code_matiere = edt.matiere_code
                LEFT JOIN salles s ON s.code_salle = edt.salle_code
                LEFT JOIN enseignants e ON e.code_enseignant = edt.enseignant_code
                LEFT JOIN users u ON u.code_user = edt.user_code
                WHERE edt.id_emploi = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'emploi/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'emploi/list'); exit();
        }
        $this->loadView('../views/emplois_temps/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/emplois_temps/edit.php', ['item' => []]);
    }
}
