<?php

class OuvertureCaisseController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelOuvertureCaisse();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/ouvertures_caisse/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $sql = "
            SELECT o.*, u.nom_user, u.prenom_user
            FROM ouvertures_caisse o
            LEFT JOIN users u ON u.code_user = o.user_code
            WHERE 1=1
        ";
        $params = [];
        if (Context::isCommercial()) {
            $sql .= " AND o.user_code = ?";
            $params[] = Context::user();
        }
        $sql .= " ORDER BY o.date_ouverture DESC, o.id_ouverture DESC";
        $stmt = $this->model->getCon()->prepare($sql);
        $stmt->execute($params);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $data = [];
        foreach ($items as $i) {
            $id = $i['id_ouverture'];
            $idCrypte = $this->validator->crypter($id);
            $data[] = array_merge($i, [
                'id' => $id,
                'editId' => $idCrypte,
                'nom_auteur_complet' => trim(($i['nom_user'] ?? '') . ' ' . ($i['prenom_user'] ?? ''))
            ]);
        }
        $this->json(['data' => $data]);
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

        $dateOuverture = $data['date_ouverture'] ?? date('Y-m-d');

        $active = $this->model->getActiveOuvertureForToday($dateOuverture);
        if ($active) {
            $this->error('Une caisse est déjà OUVERTE pour la journée du ' . date('d/m/Y', strtotime($dateOuverture)));
            return;
        }

        if (empty($data['code_ouverture'])) {
            $data['code_ouverture'] = $this->validator->generateCode('ouvertures_caisse', 'code_ouverture', 'OUV-', 8);
        }
        $data['heure_ouverture'] = date('H:i:s');
        $data['statut_ouverture'] = 'ouverte';
        $data['user_code'] = $userCode;
        $data['annee_code'] = $anneeCode;
        $data['etablissement_code'] = $etabCode;

        $cols = $this->model->getCon()->query("DESCRIBE ouvertures_caisse")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));

        if ($this->model->create($filteredData)) {
            $this->success('Ouverture de caisse enregistrée avec succès! La caisse est maintenant OUVERTE.');
        } else {
            $this->error('Erreur lors de l\'enregistrement de l\'ouverture de caisse');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id_ouverture');
        if (!$id) { $this->error('Identifiant invalide'); return; }
        $data = $_POST;
        unset($data['csrf_token']);
        $cols = $this->model->getCon()->query("DESCRIBE ouvertures_caisse")->fetchAll(PDO::FETCH_COLUMN);
        $filteredData = array_intersect_key($data, array_flip($cols));
        if ($this->model->update($filteredData, $id)) {
            $this->success('Ouverture de caisse modifiée avec succès!');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');
        $statut = $this->post('statut') ?: $this->post('status');
        if ($id && $this->model->getById($id)) {
            $allowed = ['ouverte', 'cloturee'];
            if (!empty($statut) && in_array($statut, $allowed, true)) {
                $success = $this->model->updateStatus($id, $statut, 'statut_ouverture');
            } else {
                // Custom toggle between 'ouverte' and 'cloturee'
                $cur = $this->model->getById($id);
                $newStat = ($cur['statut_ouverture'] === 'ouverte') ? 'cloturee' : 'ouverte';
                $success = $this->model->updateStatus($id, $newStat, 'statut_ouverture');
            }
            if ($success) {
                $this->success('Statut de l\'ouverture de caisse mis à jour avec succès!', ['reload' => true]);
            } else {
                $this->error('Erreur lors de la mise à jour du statut');
            }
        } else {
            $this->error('Ouverture de caisse introuvable');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $stmt = $this->model->getCon()->prepare("
                SELECT o.*, u.nom_user, u.prenom_user
                FROM ouvertures_caisse o
                LEFT JOIN users u ON u.code_user = o.user_code
                WHERE o.id_ouverture = ?
            ");
            $stmt->execute([$id]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$item) { 
                $this->renderNotFound("L'ouverture de caisse demandée est introuvable.");
                return;
            }

            // Total encaissé sur cette journée
            $stmtP = $this->model->getCon()->prepare("
                SELECT COALESCE(SUM(montant_paiement), 0) as total_jour, COUNT(*) as nb_paiements
                FROM paiements 
                WHERE DATE(date_paiement) = ? AND statut_paiement != 'annule'
            ");
            $stmtP->execute([$item['date_ouverture']]);
            $statsJour = $stmtP->fetch(PDO::FETCH_ASSOC) ?: ['total_jour' => 0, 'nb_paiements' => 0];

            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            error_log("OuvertureCaisseController::details error: " . $e->getMessage());
            $this->renderNotFound("L'ouverture de caisse demandée est introuvable.");
            return;
        }
        $this->loadView('../views/ouvertures_caisse/details.php', [
            'item' => $item, 
            'statsJour' => $statsJour,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) { header('Location: ' . RACINE . 'ouverture_caisse/list'); exit(); }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'ouverture_caisse/list'); exit();
        }
        $this->loadView('../views/ouvertures_caisse/edit.php', ['item' => $item, 'encryptedId' => $encryptedId]);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadView('../views/ouvertures_caisse/edit.php', ['item' => []]);
    }
}
