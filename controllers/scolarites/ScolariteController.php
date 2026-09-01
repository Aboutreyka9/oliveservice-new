<?php

class ScolariteController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelScolarite();
    }

    public function list()
    {
        $this->requireAuth();
        $db = $this->model->getCon();
        $totalScolarites = (int)$db->query("SELECT COUNT(*) FROM scolarites")->fetchColumn();
        $totalAffectes = (int)$db->query("SELECT COUNT(*) FROM scolarites WHERE affectation_etat = 'affecte'")->fetchColumn();
        $totalNonAffectes = (int)$db->query("SELECT COUNT(*) FROM scolarites WHERE affectation_etat = 'non_affecte' OR affectation_etat IS NULL OR affectation_etat = ''")->fetchColumn();
        $totalTranches = (int)$db->query("SELECT COUNT(*) FROM tranches_scolarite")->fetchColumn();

        $this->loadView('../views/scolarites/list.php', [
            'totalScolarites' => $totalScolarites,
            'totalAffectes' => $totalAffectes,
            'totalNonAffectes' => $totalNonAffectes,
            'totalTranches' => $totalTranches
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_scolarite'];
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
        $anneeCode = !empty($_POST['annee_code']) ? trim($_POST['annee_code']) : (Context::annee());
        $etabCode = '5454544456';
        $data = $_POST;
        unset($data['csrf_token']);
        if (empty($data['code_scolarite'])) {
            $data['code_scolarite'] = $this->validator->generateCode('scolarites', 'code_scolarite', 'SCO-', 8);
        }
        $data['statut_scolarite'] = $data['statut_scolarite'] ?? 'actif';
        $data['created_at_scolarite'] = date('Y-m-d H:i:s');
        $data['annee_code'] = $anneeCode;
        $cols = $this->model->getCon()->query("DESCRIBE scolarites")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Tarif de scolarité créé avec succès !');
        } else {
            $this->error('Erreur lors de la création de la scolarité.');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_scolarite');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        if (!empty($_POST['annee_code'])) {
            $data['annee_code'] = trim($_POST['annee_code']);
        }
        $cols = $this->model->getCon()->query("DESCRIBE scolarites")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Tarif de scolarité modifié avec succès !');
        } else {
            $this->error('Erreur lors de la modification de la scolarité.');
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
                SELECT s.*, 
                       f.libelle_filiere, 
                       n.libelle_niveau, 
                       a.libelle_annee
                FROM scolarites s
                LEFT JOIN filieres f ON f.code_filiere = s.filiere_code
                LEFT JOIN niveaux n ON n.code_niveau = s.niveau_code
                LEFT JOIN annees a ON a.code_annee = s.annee_code
                WHERE s.id_scolarite = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("La grille de scolarité demandée est introuvable.");
                return;
            }

            // Tranches / Échéancier de cette scolarité
            $stmtTranches = $this->model->getCon()->prepare("
                SELECT * FROM tranches_scolarite 
                WHERE (scolarite_code = ? OR (filiere_code = ? AND niveau_code = ?))
                ORDER BY date_limite ASC, id_tranche ASC
            ");
            $stmtTranches->execute([$item['code_scolarite'], $item['filiere_code'], $item['niveau_code']]);
            $tranches = $stmtTranches->fetchAll(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("ScolariteController::details error: " . $e->getMessage());
            $this->renderNotFound("La grille de scolarité demandée est introuvable.");
            return;
        }
        $this->loadView('../views/scolarites/details.php', [
            'item' => $item, 
            'tranches' => $tranches,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'scolarite/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'scolarite/list'); exit();
        }
        $this->loadView('../views/scolarites/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/scolarites/edit.php', ['item' => []]);
    }
}
