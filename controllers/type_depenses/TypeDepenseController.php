<?php

class TypeDepenseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelTypeDepense();
    }

    public function list()
    {
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $this->loadView('../views/type_depenses/list.php');
    }

    public function apiList()
    {
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $sql = "SELECT * FROM type_depenses WHERE 1=1";
        $params = [];
        if (Context::etablissement()) {
            $sql .= " AND etablissement_code = ?";
            $params[] = Context::etablissement();
        }
        if (Context::zone()) {
            $sql .= " AND zone_code = ?";
            $params[] = Context::zone();
        }
        $sql .= " ORDER BY id_type_depense DESC";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        foreach ($items as $td) {
            $id = $td['id_type_depense'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($td, [
                'id' => $id,
                'editId' => $idCrypte,
                'statut_type_depense' => $td['statut_typedepense'] ?? ($td['statut_type_depense'] ?? 'actif')
            ]);
        }
        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $data = $_POST;
        unset($data['csrf_token']);

        if (empty($data['libelle_type_depense'])) {
            $this->error('Veuillez renseigner le libellé !');
            return;
        }

        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();
        $userCode = Context::user();

        if (empty($data['code_type_depense'])) {
            $data['code_type_depense'] = $this->validator->generateCode('type_depenses', 'code_type_depense', 'TDP-', 6);
        }
        $data['statut_typedepense'] = $data['statut_typedepense'] ?? ($data['statut_type_depense'] ?? 'actif');

        $cols = $this->model->getCon()->query("DESCRIBE type_depenses")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;
        if (in_array('zone_code', $cols)) $data['zone_code'] = $zoneCode;
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Type de dépense ajouté avec succès!');
        } else {
            $this->error('Erreur lors de l\'ajout du type de dépense');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $id = (int)$this->post('id_type_depense');
        if (!$id) { $this->error('Identifiant invalide'); return; }

        $existing = $this->model->getById($id);
        if (!$existing || (Context::etablissement() && ($existing['etablissement_code'] ?? '') !== Context::etablissement()) || (Context::zone() && ($existing['zone_code'] ?? '') !== Context::zone())) {
            $this->error('Type de dépense introuvable ou non autorisé');
            return;
        }

        $data = $_POST;
        unset($data['csrf_token']);

        $cols = $this->model->getCon()->query("DESCRIBE type_depenses")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Type de dépense modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $id = $this->post('id');
        if ($id && ($item = $this->model->getById($id))) {
            if ((Context::etablissement() && ($item['etablissement_code'] ?? '') !== Context::etablissement()) || (Context::zone() && ($item['zone_code'] ?? '') !== Context::zone())) {
                $this->error('Type de dépense introuvable ou non autorisé');
                return;
            }
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Type de dépense introuvable');
        }
    }

    public function details($details)
    {
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || (Context::etablissement() && ($item['etablissement_code'] ?? '') !== Context::etablissement()) || (Context::zone() && ($item['zone_code'] ?? '') !== Context::zone())) {
                header('Location: ' . RACINE . 'type_depense/list'); exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'type_depense/list'); exit();
        }
        $this->loadView('../views/type_depenses/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function edition($details)
    {
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item || (Context::etablissement() && ($item['etablissement_code'] ?? '') !== Context::etablissement()) || (Context::zone() && ($item['zone_code'] ?? '') !== Context::zone())) {
                header('Location: ' . RACINE . 'type_depense/list'); exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'type_depense/list'); exit();
        }
        $this->loadView('../views/type_depenses/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requirePermission('FINANCE_MANAGE_TYPE_DEPENSES');
        $this->loadView('../views/type_depenses/edit.php', ['item' => []]);
    }
}