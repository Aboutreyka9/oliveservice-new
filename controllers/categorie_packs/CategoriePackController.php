<?php

class CategoriePackController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelCategoriePack();
    }

    public function list()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $this->loadView('../views/categorie_packs/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $items = $this->model->getAll();
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_categorie_pack'];
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
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['libelle_categorie_pack'])) {
            if (!$this->checkUnique('categorie_packs', 'libelle_categorie_pack', $data['libelle_categorie_pack'], 'Catégorie de pack')) return;
        }

        $userCode = Context::user();
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();

        if (empty($userCode) || empty($etabCode) || empty($zoneCode)) {
            $this->error("Erreur d'insertion : L'utilisateur connecté, la zone commerciale et l'établissement sont obligatoires et ne peuvent pas être null.");
            return;
        }

        if (empty($data['code_categorie_pack'])) {
            $data['code_categorie_pack'] = $this->validator->generateCode('categorie_packs', 'code_categorie_pack', 'CPK-', 8);
        }
        $data['statut_categorie_pack'] = $data['statut_categorie_pack'] ?? 'actif';
        $data['created_at_categorie_pack'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE categorie_packs")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('zone_code', $cols)) $data['zone_code'] = $zoneCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Catégorie créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de la catégorie');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $id = (int)$this->post('id_categorie_pack');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['libelle_categorie_pack'])) {
            if (!$this->checkUnique('categorie_packs', 'libelle_categorie_pack', $data['libelle_categorie_pack'], 'Catégorie de pack', 'id_categorie_pack', $id)) return;
        }

        $data['updated_at_categorie_pack'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE categorie_packs")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Catégorie modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Catégorie introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("La catégorie demandée est introuvable.");
                return;
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("La catégorie demandée est introuvable.");
            return;
        }

        $codeCat = $item['code_categorie_pack'] ?? '';

        // Récupérer les packs rattachés à cette catégorie
        $stmtPacks = $this->model->getCon()->prepare("
            SELECT p.*,
                   a.libelle_annee,
                   s.libelle_session,
                   z.libelle_zone,
                   (SELECT COUNT(*) FROM pack_souscriptions ps WHERE ps.pack_code = p.code_pack) as total_souscriptions
            FROM packs p
            LEFT JOIN annees a ON a.code_annee = p.annee_code
            LEFT JOIN sessions s ON s.code_session = p.session_code
            LEFT JOIN zones z ON z.code_zone = p.zone_code
            WHERE p.categorie_pack_code = ?
            ORDER BY p.id_pack DESC
        ");
        $stmtPacks->execute([$codeCat]);
        $packs = $stmtPacks->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($packs as &$pk) {
            $pk['encrypted_id'] = $this->validator->crypter($pk['id_pack']);
        }
        unset($pk);

        // Statistiques globales de la catégorie
        $stmtStats = $this->model->getCon()->prepare("
            SELECT 
                COUNT(DISTINCT p.id_pack) as total_packs,
                COUNT(DISTINCT ps.souscription_code) as total_souscriptions,
                COALESCE(SUM(cc.montant_cautisation_client), 0) as total_cotisations
            FROM packs p
            LEFT JOIN pack_souscriptions ps ON ps.pack_code = p.code_pack
            LEFT JOIN cautisation_clients cc ON cc.souscription_code = ps.souscription_code AND cc.statut_cautisation_client = 'valide'
            WHERE p.categorie_pack_code = ?
        ");
        $stmtStats->execute([$codeCat]);
        $stats = $stmtStats->fetch(PDO::FETCH_ASSOC) ?: ['total_packs' => 0, 'total_souscriptions' => 0, 'total_cotisations' => 0];

        $this->loadView('../views/categorie_packs/details.php', [
            'item' => $item,
            'encryptedId' => $encryptedId,
            'packs' => $packs,
            'stats' => $stats
        ]);
    }

    public function edition($details)
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'categorie_pack/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'categorie_pack/list'); exit();
        }
        $this->loadView('../views/categorie_packs/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('GESTIONNAIRE_MANAGE_CATEGORIE_PACKS');
        $this->loadView('../views/categorie_packs/edit.php', ['item' => []]);
    }
}
