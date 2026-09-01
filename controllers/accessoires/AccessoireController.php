<?php

class AccessoireController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelAccessoire();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/accessoires/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_accessoire'];
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
        if (!empty($data['libelle_accessoire'])) {
            if (!$this->checkUnique('accessoires', 'libelle_accessoire', $data['libelle_accessoire'], 'Libelle de l accessoire')) return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';
        if (empty($data['code_accessoire'])) {
            $data['code_accessoire'] = $this->validator->generateCode('accessoires', 'code_accessoire', 'ACC-', 8);
        }
        $data['statut_accessoire'] = $data['statut_accessoire'] ?? 'actif';
        $data['created_at_accessoire'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE accessoires")->fetchAll(PDO::FETCH_COLUMN);
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
        $id = (int)$this->post('id_accessoire');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($data['libelle_accessoire'])) {
            if (!$this->checkUnique('accessoires', 'libelle_accessoire', $data['libelle_accessoire'], 'Libelle de l accessoire', 'id_accessoire', $id)) return;
        }

        $cols = $this->model->getCon()->query("DESCRIBE accessoires")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Item modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function apiDistributions()
    {
        $this->requireAuth();
        $filter = trim($_GET['filter'] ?? 'all');
        $items = $this->model->getDistributions($filter);
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_accessoire_inscription'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function apiStats()
    {
        $this->requireAuth();
        $stats = $this->model->getStats();
        $this->json(['status' => 1, 'stats' => $stats]);
    }

    public function toggleRetrait()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id');
        $code = trim($this->post('code_accessoire_inscription') ?? '');

        $db = $this->model->getCon();

        if ($id > 0) {
            $stmt = $db->prepare("SELECT * FROM accessoire_inscription WHERE id_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$id]);
        } else {
            $stmt = $db->prepare("SELECT * FROM accessoire_inscription WHERE code_accessoire_inscription = ? LIMIT 1");
            $stmt->execute([$code]);
        }

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            $this->error('Enregistrement de kit introuvable');
            return;
        }

        $newEtat = ($row['etat_retrait'] === 'retire') ? 'en_attente' : 'retire';
        $dateRetrait = ($newEtat === 'retire') ? date('Y-m-d H:i:s') : null;

        $stmtUp = $db->prepare("
            UPDATE accessoire_inscription 
            SET etat_retrait = ?, date_retrait = ? 
            WHERE id_accessoire_inscription = ?
        ");
        $success = $stmtUp->execute([$newEtat, $dateRetrait, $row['id_accessoire_inscription']]);

        if ($success) {
            $msg = ($newEtat === 'retire') 
                ? 'Kit marqué comme RETIRÉ avec succès !' 
                : 'Kit remis EN ATTENTE de retrait.';
            $this->success($msg, [
                'new_state' => $newEtat,
                'date_retrait' => $dateRetrait ? date('d/m/Y H:i', strtotime($dateRetrait)) : '-',
                'reload' => false
            ]);
        } else {
            $this->error('Erreur lors de la mise à jour du statut de distribution.');
        }
    }

    public function getStudentKits()
    {
        $this->requireAuth();
        $etudiantCode = trim($_GET['etudiant_code'] ?? $_POST['etudiant_code'] ?? '');
        if (empty($etudiantCode)) {
            $this->json(['status' => 0, 'message' => 'Étudiant non spécifié', 'existing_kits' => [], 'existing_codes' => []]);
            return;
        }

        $anneeCode = Context::annee();
        $db = $this->model->getCon();

        $stmt = $db->prepare("
            SELECT ai.*, a.libelle_accessoire,
                   DATE_FORMAT(ai.date_retrait, '%d/%m/%Y %H:%i') as date_retrait_formatee,
                   DATE_FORMAT(ai.created_at_accessoire_inscription, '%d/%m/%Y') as date_attribution_formatee
            FROM accessoire_inscription ai
            JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
            JOIN inscriptions i ON i.code_inscription = ai.inscription_code
            WHERE i.etudiant_code = ?
              AND (ai.annee_code = ? OR ai.annee_code IS NULL OR ai.annee_code = '')
              AND (ai.statut_accessoire_inscription = 'actif' OR ai.statut_accessoire_inscription IS NULL)
            ORDER BY ai.id_accessoire_inscription DESC
        ");
        $stmt->execute([$etudiantCode, $anneeCode]);
        $existingKits = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $existingCodes = array_values(array_unique(array_column($existingKits, 'accessoire_code')));

        $allKits = $db->query("SELECT code_accessoire, libelle_accessoire FROM accessoires WHERE statut_accessoire = 'actif' ORDER BY libelle_accessoire ASC")->fetchAll(PDO::FETCH_ASSOC);

        $this->json([
            'status' => 1,
            'existing_kits' => $existingKits,
            'existing_codes' => $existingCodes,
            'all_kits' => $allKits,
            'has_all' => (count($existingCodes) >= count($allKits) && count($allKits) > 0)
        ]);
    }

    public function attribuerKit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $db = $this->model->getCon();
        $etudiantCode = trim($_POST['etudiant_code'] ?? '');
        $etatRetrait = trim($_POST['etat_retrait'] ?? 'en_attente');

        // Support multiple accessoires
        $accessoireCodes = [];
        if (!empty($_POST['accessoires']) && is_array($_POST['accessoires'])) {
            $accessoireCodes = array_filter($_POST['accessoires']);
        } elseif (!empty($_POST['accessoire_code'])) {
            $accessoireCodes = [trim($_POST['accessoire_code'])];
        }

        if (empty($etudiantCode) || empty($accessoireCodes)) {
            $this->error('Veuillez sélectionner un étudiant et au moins un kit / accessoire.');
            return;
        }

        $userCode = Context::user() ?? '';
        $anneeCode = Context::annee();
        $etabCode = '5454544456';

        // Trouver la dernière inscription de l'étudiant
        $stmtIns = $db->prepare("SELECT code_inscription FROM inscriptions WHERE etudiant_code = ? ORDER BY id_inscription DESC LIMIT 1");
        $stmtIns->execute([$etudiantCode]);
        $ins = $stmtIns->fetch(PDO::FETCH_ASSOC);
        $insCode = $ins['code_inscription'] ?? '';

        if (empty($insCode)) {
            $this->error('Aucune inscription active trouvée pour cet étudiant. Veuillez d\'abord procéder à son inscription.');
            return;
        }

        // VÉRIFICATION D'UNICITÉ : Ne pas attribuer un kit déjà existant pour cet étudiant cette année
        $stmtCheck = $db->prepare("
            SELECT ai.id_accessoire_inscription, a.libelle_accessoire 
            FROM accessoire_inscription ai
            LEFT JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
            JOIN inscriptions i ON i.code_inscription = ai.inscription_code
            WHERE (i.etudiant_code = ? OR ai.inscription_code = ?)
              AND ai.accessoire_code = ? 
              AND (ai.annee_code = ? OR ai.annee_code IS NULL OR ai.annee_code = '')
              AND (ai.statut_accessoire_inscription = 'actif' OR ai.statut_accessoire_inscription IS NULL)
            LIMIT 1
        ");

        $toInsert = [];
        $duplicates = [];

        foreach ($accessoireCodes as $accCode) {
            $stmtCheck->execute([$etudiantCode, $insCode, $accCode, $anneeCode]);
            $existing = $stmtCheck->fetch(PDO::FETCH_ASSOC);
            if ($existing) {
                $duplicates[] = $existing['libelle_accessoire'] ?? $accCode;
            } else {
                $toInsert[] = $accCode;
            }
        }

        if (empty($toInsert)) {
            $nomsDoublons = implode(', ', $duplicates);
            $this->error("Attribution impossible : Cet étudiant possède déjà le(s) kit(s) [{$nomsDoublons}] pour cette année scolaire.");
            return;
        }

        $dateRetrait = ($etatRetrait === 'retire') ? date('Y-m-d H:i:s') : null;

        $stmt = $db->prepare("
            INSERT INTO accessoire_inscription
            (code_accessoire_inscription, inscription_code, accessoire_code, annee_code, statut_accessoire_inscription, etat_retrait, date_retrait, user_code, etablissement_code, created_at_accessoire_inscription)
            VALUES (?, ?, ?, ?, 'actif', ?, ?, ?, ?, NOW())
        ");

        $count = 0;
        foreach ($toInsert as $accCode) {
            $codeAccIns = $this->validator->generateCode('accessoire_inscription', 'code_accessoire_inscription', 'ACI-', 8);
            $ok = $stmt->execute([
                $codeAccIns,
                $insCode,
                $accCode,
                $anneeCode,
                $etatRetrait,
                $dateRetrait,
                $userCode,
                $etabCode
            ]);
            if ($ok) $count++;
        }

        if ($count > 0) {
            $msg = "{$count} nouveau(x) kit(s) attribué(s) avec succès !";
            if (!empty($duplicates)) {
                $msg .= " (Note : " . implode(', ', $duplicates) . " déjà possédé(s) et ignoré(s)).";
            }
            $this->success($msg, ['reload' => true]);
        } else {
            $this->error('Erreur lors de l\'attribution des kits.');
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
            if (!$item) { header('Location: ' . RACINE . 'accessoire/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'accessoire/list'); exit();
        }
        $this->loadView('../views/accessoires/details.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'accessoire/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'accessoire/list'); exit();
        }
        $this->loadView('../views/accessoires/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/accessoires/edit.php', ['item' => []]);
    }
}