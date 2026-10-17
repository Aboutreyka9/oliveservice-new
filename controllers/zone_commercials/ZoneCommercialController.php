<?php

class ZoneCommercialController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelZoneCommercial();
    }

    public function list()
    {
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        $this->loadView('../views/zone_commercials/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        $items = $this->model->getAllWithDetails();
        $data = [];

        foreach ($items as $i) {
            $id = $i['id_zone_commercial'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_commercial' => trim(($i['nom_user'] ?? '') . ' ' . ($i['prenom_user'] ?? '')) ?: $i['commercial_code']
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['commercial_code']) || empty($data['zone_code'])) {
            $this->error('Veuillez sélectionner un commercial et une zone !');
            return;
        }

        $etabCode = Context::etablissement();
        if (empty($etabCode)) {
            $this->error("Erreur d'insertion : L'établissement actif est obligatoire et ne peut pas être null.");
            return;
        }

        $userCode = Context::user();
        if (empty($userCode)) {
            $this->error("Erreur d'insertion : Aucun utilisateur connecté dans la session.");
            return;
        }

        // Vérifier si cette affectation existe déjà
        $stmtCheck = $this->model->getCon()->prepare("
            SELECT id_zone_commercial FROM zone_commercials 
            WHERE commercial_code = ? AND zone_code = ?
            LIMIT 1
        ");
        $stmtCheck->execute([$data['commercial_code'], $data['zone_code']]);
        if ($stmtCheck->fetch()) {
            $this->error('Ce commercial est déjà affecté à cette zone !');
            return;
        }

        $data['user_code'] = $userCode;
        $data['etablissement_code'] = $etabCode;
        $data['statut_zone_commercial'] = $data['statut_zone_commercial'] ?? 'actif';
        $data['created_at_zone_commercial'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE zone_commercials")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Affectation créée avec succès!');
        } else {
            $this->error('Erreur lors de la création de l\'affectation');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        $id = (int)$this->post('id_zone_commercial');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['commercial_code']) || empty($data['zone_code'])) {
            $this->error('Veuillez sélectionner un commercial et une zone !');
            return;
        }

        // Vérifier si cette affectation existe déjà pour un autre enregistrement
        $stmtCheck = $this->model->getCon()->prepare("
            SELECT id_zone_commercial FROM zone_commercials 
            WHERE commercial_code = ? AND zone_code = ? AND id_zone_commercial != ?
            LIMIT 1
        ");
        $stmtCheck->execute([$data['commercial_code'], $data['zone_code'], $id]);
        if ($stmtCheck->fetch()) {
            $this->error('Ce commercial est déjà affecté à cette zone !');
            return;
        }

        $userCode = Context::user();
        if (!empty($userCode)) {
            $data['user_code'] = $userCode;
        }

        $data['updated_at_zone_commercial'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE zone_commercials")->fetchAll(PDO::FETCH_COLUMN);
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
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        $id = $this->post('id');
        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Affectation introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'zone_commercial/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'zone_commercial/list'); exit();
        }
        $this->loadView('../views/zone_commercials/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requirePermission('ADMIN_MANAGE_ZONES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'zone_commercial/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'zone_commercial/list'); exit();
        }

        $etabCode = Context::etablissement();
        if (!empty($etabCode) && !Context::hasJoker()) {
            $stmtZones = $this->model->getCon()->prepare("SELECT code_zone, libelle_zone FROM zones WHERE etablissement_code = ? AND statut_zone='actif' ORDER BY libelle_zone ASC");
            $stmtZones->execute([$etabCode]);
            $zones = $stmtZones->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif' ORDER BY libelle_zone ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $commerciaux = $this->model->getCon()->query("
            SELECT DISTINCT u.code_user, u.nom_user, u.prenom_user 
            FROM users u
            JOIN user_roles ur ON ur.user_code = u.code_user
            WHERE ur.role_code = 'ROLE_COMMERCIAL' AND u.statut_user = 'actif'
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($commerciaux)) {
            $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif' ORDER BY nom_user ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->loadView('../views/zone_commercials/edit.php', ['item' => $item, 'zones' => $zones, 'commerciaux' => $commerciaux, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('ADMIN_MANAGE_ZONES');

        $etabCode = Context::etablissement();
        if (!empty($etabCode) && !Context::hasJoker()) {
            $stmtZones = $this->model->getCon()->prepare("SELECT code_zone, libelle_zone FROM zones WHERE etablissement_code = ? AND statut_zone='actif' ORDER BY libelle_zone ASC");
            $stmtZones->execute([$etabCode]);
            $zones = $stmtZones->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif' ORDER BY libelle_zone ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $commerciaux = $this->model->getCon()->query("
            SELECT DISTINCT u.code_user, u.nom_user, u.prenom_user 
            FROM users u
            JOIN user_roles ur ON ur.user_code = u.code_user
            WHERE ur.role_code = 'ROLE_COMMERCIAL' AND u.statut_user = 'actif'
            ORDER BY u.nom_user ASC, u.prenom_user ASC
        ")->fetchAll(PDO::FETCH_ASSOC);

        if (empty($commerciaux)) {
            $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif' ORDER BY nom_user ASC")->fetchAll(PDO::FETCH_ASSOC);
        }

        $this->loadView('../views/zone_commercials/edit.php', ['item' => [], 'zones' => $zones, 'commerciaux' => $commerciaux]);
    }
}
