<?php

class SemestreController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelSemestre();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/semestres/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "SELECT s.*, a.libelle_annee 
                FROM semestres s
                LEFT JOIN annees a ON a.code_annee = s.annee_code
                ORDER BY s.id_semestre DESC";
        $items = $this->model->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_semestre'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    private function normalizeLibelle(string $raw): ?string
    {
        $trim = trim($raw);
        $upper = strtoupper($trim);
        if ($upper === 'SEMESTRE 1' || $upper === 'SEMESTRE1' || $upper === 'S1') {
            return 'Semestre 1';
        }
        if ($upper === 'SEMESTRE 2' || $upper === 'SEMESTRE2' || $upper === 'S2') {
            return 'Semestre 2';
        }
        return null;
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle strict du libellé : uniquement Semestre 1 ou Semestre 2
        $libelle = $this->normalizeLibelle($data['libelle_semestre'] ?? '');
        if (!$libelle) {
            $this->error('Veuillez sélectionner un semestre valide (Semestre 1 ou Semestre 2).');
            return;
        }
        $data['libelle_semestre'] = $libelle;

        $userCode = Context::user() ?? '';
        $anneeCode = !empty($data['annee_code']) ? $data['annee_code'] : (Context::annee());
        $etabCode = '5454544456';

        if (empty($anneeCode)) {
            $this->error('Veuillez sélectionner une année académique.');
            return;
        }

        // Contrôle d'unicité : un seul Semestre 1 et un seul Semestre 2 par année académique
        $stmt = $this->model->getCon()->prepare("
            SELECT id_semestre FROM semestres 
            WHERE (libelle_semestre = ? OR UPPER(libelle_semestre) = ?) AND annee_code = ?
        ");
        $stmt->execute([$libelle, strtoupper($libelle), $anneeCode]);
        if ($stmt->fetch()) {
            $this->error("Le $libelle est déjà enregistré pour l'année académique sélectionnée.");
            return;
        }

        if (empty($data['code_semestre'])) {
            $data['code_semestre'] = $this->validator->generateCode('semestres', 'code_semestre', 'SEM-', 8);
        }
        $data['statut_semestre'] = $data['statut_semestre'] ?? 'actif';
        $data['created_at_semestre'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE semestres")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('annee_code', $cols)) $data['annee_code'] = $anneeCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Semestre créé avec succès!');
        } else {
            $this->error('Erreur lors de la création du semestre.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_semestre');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        
        $current = $this->model->getById($id);
        if (!$current) { $this->error('Semestre introuvable'); return; }

        $data = $_POST;
        unset($data['csrf_token']);

        // Contrôle strict du libellé : uniquement Semestre 1 ou Semestre 2
        $libelle = $this->normalizeLibelle($data['libelle_semestre'] ?? '');
        if (!$libelle) {
            $this->error('Veuillez sélectionner un semestre valide (Semestre 1 ou Semestre 2).');
            return;
        }
        $data['libelle_semestre'] = $libelle;

        $anneeCode = !empty($data['annee_code']) ? $data['annee_code'] : ($current['annee_code'] ?? '');
        if (empty($anneeCode)) {
            $this->error('Veuillez sélectionner une année académique.');
            return;
        }

        // Contrôle d'unicité par année académique (en excluant l'enregistrement en cours d'édition)
        $stmt = $this->model->getCon()->prepare("
            SELECT id_semestre FROM semestres 
            WHERE (libelle_semestre = ? OR UPPER(libelle_semestre) = ?) AND annee_code = ? AND id_semestre != ?
        ");
        $stmt->execute([$libelle, strtoupper($libelle), $anneeCode, $id]);
        if ($stmt->fetch()) {
            $this->error("Le $libelle est déjà enregistré pour cette année académique.");
            return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE semestres")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Semestre modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification du semestre.');
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
                SELECT s.*, a.libelle_annee 
                FROM semestres s
                LEFT JOIN annees a ON a.code_annee = s.annee_code
                WHERE s.id_semestre = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { header('Location: ' . RACINE . 'semestre/list'); exit(); }

            $semestreCode = $item['code_semestre'];

            // Statistiques d'évaluations et de notes
            $stmtStats = $this->model->getCon()->prepare("
                SELECT 
                    (SELECT COUNT(*) FROM notes WHERE semestre_code = ? AND statut_note = 'actif') as total_notes,
                    (SELECT COUNT(DISTINCT ins.classe_code) FROM notes n JOIN inscriptions ins ON ins.code_inscription = n.inscription_code WHERE n.semestre_code = ?) as total_classes_evaluees,
                    (SELECT COUNT(DISTINCT ins.etudiant_code) FROM notes n JOIN inscriptions ins ON ins.code_inscription = n.inscription_code WHERE n.semestre_code = ?) as total_etudiants_notes
            ");
            $stmtStats->execute([$semestreCode, $semestreCode, $semestreCode]);
            $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: [
                'total_notes' => 0, 'total_classes_evaluees' => 0, 'total_etudiants_notes' => 0
            ];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("SemestreController::details error: " . $e->getMessage());
            $this->renderNotFound("Le semestre demandé est introuvable.");
        }
        $this->loadView('../views/semestres/details.php', [
            'item' => $item, 
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
            if (!$item) { header('Location: ' . RACINE . 'semestre/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'semestre/list'); exit();
        }
        $this->loadView('../views/semestres/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/semestres/edit.php', ['item' => []]);
    }
}