<?php

class ClientController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelClient();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/clients/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $anneeCode = Context::annee();
        $zoneCode = Context::zone();
        $userCode = Context::user();

        $sql = "
            SELECT DISTINCT c.*, z.libelle_zone
            FROM clients c
            LEFT JOIN zones z ON z.code_zone = c.zone_code
            INNER JOIN souscriptions s ON s.client_code = c.code_client
            WHERE 1=1
        ";
        $params = [];

        if ($anneeCode !== '0GklBk07waYoLB6pHwY') {
            $sql .= " AND s.annee_code = ?";
            $params[] = $anneeCode;
        }

        if ($zoneCode !== null && $zoneCode !== '') {
            $sql .= " AND s.zone_code = ?";
            $params[] = $zoneCode;
        }

        if ($userCode !== null && $userCode !== '') {
            $sql .= " AND s.user_code = ?";
            $params[] = $userCode;
        }

        $sql .= " ORDER BY c.created_at_client DESC";

        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];

        foreach ($clients as $c) {
            $id = $c['id_client'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($c, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_complet' => trim($c['nom_client'] ?? '')
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

        if (!empty($data['telephone_client'])) {
            if (!$this->checkUnique('clients', 'telephone_client', $data['telephone_client'], 'Téléphone client')) return;
        }

        $userCode = Context::user() ?? '';
        $etabCode = Context::etablissement();
        $zoneCode = Context::zone();

        if (empty($data['code_client'])) {
            $data['code_client'] = $this->validator->generateCode('clients', 'code_client', 'CLI-', 8);
        }
        $data['statut_client'] = $data['statut_client'] ?? 'actif';
        $data['created_at_client'] = date('Y-m-d H:i:s');

        $cols = $this->model->getCon()->query("DESCRIBE clients")->fetchAll(PDO::FETCH_COLUMN);
        if (in_array('user_code', $cols)) $data['user_code'] = $userCode;
        if (in_array('etablissement_code', $cols)) $data['etablissement_code'] = $etabCode;

        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->create($filteredData)) {
            $this->success('Client créé avec succès!', ['code_client' => $data['code_client']]);
        } else {
            $this->error('Erreur lors de la création du client');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_client');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        if (!empty($data['telephone_client'])) {
            if (!$this->checkUnique('clients', 'telephone_client', $data['telephone_client'], 'Téléphone client', 'id_client', $id)) return;
        }

        $data['updated_at_client'] = date('Y-m-d H:i:s');
        $cols = $this->model->getCon()->query("DESCRIBE clients")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Client modifié avec succès!');
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
            $this->error('Client introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("Le client demandé est introuvable.");
                return;
            }

            // Récupérer les souscriptions de ce client
            $stmtSous = $this->model->getCon()->prepare("
                SELECT s.*, p.libelle_pack, z.libelle_zone
                FROM souscriptions s
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
                LEFT JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                WHERE s.client_code = ?
                ORDER BY s.created_at_souscription DESC
            ");
            $stmtSous->execute([$item['code_client']]);
            $souscriptions = $stmtSous->fetchAll(PDO::FETCH_ASSOC) ?: [];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le client demandé est introuvable.");
            return;
        }
        $this->loadView('../views/clients/details.php', [
            'item' => $item,
            'souscriptions' => $souscriptions,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'client/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'client/list'); exit();
        }
        $this->loadView('../views/clients/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/clients/edit.php', ['item' => []]);
    }
}