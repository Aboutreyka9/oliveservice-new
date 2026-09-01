<?php

class VersementController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelVersement();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/versements/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAllWithDetails();
        $data = [];

        foreach ($items as $v) {
            $id = $v['id_versement'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($v, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_commercial_complet' => trim(($v['nom_commercial'] ?? '') . ' ' . ($v['prenom_commercial'] ?? '')),
                'nom_validator_complet' => trim(($v['nom_validator'] ?? '') . ' ' . ($v['prenom_validator'] ?? ''))
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

        if (empty($data['commercial_code']) || empty($data['montant_versement'])) {
            $this->error('Veuillez sélectionner un commercial et le montant versé !');
            return;
        }

        $userCode = Context::user() ?? '';
        $etabCode = '5454544456';
        $codeVersement = $this->validator->generateCode('versements_commerciaux', 'code_versement_commercial', 'VRS-', 8);

        $versementData = [
            'code_versement_commercial' => $codeVersement,
            'reference_versement' => $data['reference_versement'] ?? $codeVersement,
            'montant_versement' => (int)$data['montant_versement'],
            'commercial_code' => $data['commercial_code'],
            'periode_versement_debut' => !empty($data['periode_versement_debut']) ? $data['periode_versement_debut'] : date('Y-m-d'),
            'periode_versement_fin' => !empty($data['periode_versement_fin']) ? $data['periode_versement_fin'] : date('Y-m-d'),
            'zone_code' => $data['zone_code'] ?? '',
            'statut_versement' => $data['statut_versement'] ?? 'En attente',
            'etablissement_code' => $etabCode,
            'user_code' => $userCode,
            'created_at_versement' => date('Y-m-d H:i:s'),
            'user_validate' => '',
            'date_validation' => '1000-01-01 00:00:00',
            'commentaire_validation' => ''
        ];

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($versementData, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Versement enregistré avec succès !', ['code' => $codeVersement]);
        } else {
            $this->error('Erreur lors de l\'enregistrement du versement');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_versement');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);

        $cols = $this->model->getCon()->query("DESCRIBE versements_commerciaux")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Versement modifié avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function valider()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_versement');
        $commentaire = $this->post('commentaire_validation') ?? 'Validé par la gestion';
        $userValidateCode = Context::user() ?? '';

        if (!$id) {
            $this->error('Identifiant de versement invalide');
            return;
        }

        if ($this->model->validateVersement($id, $userValidateCode, $commentaire)) {
            $this->success('Versement validé avec succès !', ['reload' => true]);
        } else {
            $this->error('Erreur lors de la validation du versement');
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
            $this->error('Versement introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                $this->renderNotFound("Le versement demandé est introuvable.");
                return;
            }

            $stmtU = $this->model->getCon()->prepare("SELECT * FROM users WHERE code_user = ?");
            $stmtU->execute([$item['commercial_code']]);
            $commercial = $stmtU->fetch(PDO::FETCH_ASSOC);

            $stmtZ = $this->model->getCon()->prepare("SELECT * FROM zones WHERE code_zone = ?");
            $stmtZ->execute([$item['zone_code']]);
            $zone = $stmtZ->fetch(PDO::FETCH_ASSOC);

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            $this->renderNotFound("Le versement demandé est introuvable.");
            return;
        }
        $this->loadView('../views/versements/details.php', [
            'item' => $item,
            'commercial' => $commercial,
            'zone' => $zone,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'versement/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'versement/list'); exit();
        }
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => $item,
            'commerciaux' => $commerciaux,
            'zones' => $zones,
            'encryptedId' => $encryptedId
        ]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $commerciaux = $this->model->getCon()->query("SELECT code_user, nom_user, prenom_user FROM users WHERE statut_user='actif'")->fetchAll(PDO::FETCH_ASSOC);
        $zones = $this->model->getCon()->query("SELECT code_zone, libelle_zone FROM zones WHERE statut_zone='actif'")->fetchAll(PDO::FETCH_ASSOC);

        $this->loadView('../views/versements/edit.php', [
            'item' => [],
            'commerciaux' => $commerciaux,
            'zones' => $zones
        ]);
    }
}
