<?php

class TarifArticleController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelTarifArticle();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/tarifs_articles/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();

        if ($pressingCode !== null) {
            $tarifs = $this->model->getByPressing($pressingCode);
        } else {
            $tarifs = $this->model->getAllWithDetails();
        }

        $data = [];

        foreach ($tarifs as $t) {
            $idCrypte = $this->validator->crypter($t['id_tarif']);
            $data[] = [
                'code' => $t['code_tarif'],
                'pressing' => $t['libelle_pressing'] ?? ($t['pressing_code'] ?? ''),
                'article' => $t['libelle_article'] ?? ($t['article_code'] ?? ''),
                'service' => $t['libelle_service'] ?? ($t['service_code'] ?? ''),
                'prix' => $t['prix_tarif'] ?? 0,
                'statut' => $t['statut_tarif'],
                'id' => $t['id_tarif'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $pressingCode = $this->getCurrentPressingCode();
        if (empty($pressingCode)) {
            $pressingCode = $this->post('pressing_code') ?: 'PRS-001';
        }

        $this->requireActiveAbonnement($pressingCode, 'créer des tarifs articles');

        $notEmpty = Validator::validateRequiredFields([
            'article_code' => $_POST['article_code'] ?? '',
            'service_code' => $_POST['service_code'] ?? '',
            'prix_tarif' => $_POST['prix_tarif'] ?? ''
        ]);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs requis !');
            return;
        }

        $articleCode = $this->post('article_code');
        $serviceCode = $this->post('service_code');
        $prixTarif = (float)$this->post('prix_tarif');

        // Vérifier si un tarif existe déjà pour ce pressing + article + service
        $existing = $this->model->getCon()->prepare("
            SELECT id_tarif FROM " . TABLES::TARIFS_ARTICLES . " 
            WHERE pressing_code = ? AND article_code = ? AND service_code = ? LIMIT 1
        ");
        $existing->execute([$pressingCode, $articleCode, $serviceCode]);
        $existingId = $existing->fetchColumn();

        if ($existingId) {
            // Mettre à jour le tarif existant
            $stmtUp = $this->model->getCon()->prepare("
                UPDATE " . TABLES::TARIFS_ARTICLES . " 
                SET prix_tarif = ?, statut_tarif = 'actif', updated_at_tarif = NOW() 
                WHERE id_tarif = ?
            ");
            $stmtUp->execute([$prixTarif, $existingId]);
            $this->success('Tarif mis à jour avec succès !');
            return;
        }

        $code = $this->post('code_tarif') ?: $this->validator->generateCode(TABLES::TARIFS_ARTICLES, 'code_tarif', 'TAR-', 6);

        $data = [
            'code_tarif' => $code,
            'pressing_code' => $pressingCode,
            'article_code' => $articleCode,
            'service_code' => $serviceCode,
            'prix_tarif' => $prixTarif,
            'statut_tarif' => 'actif',
            'created_at_tarif' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Tarif enregistré avec succès !');
        } else {
            $this->error('Erreur lors de l\'enregistrement du tarif');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id_tarif');
        $item = $this->model->getById($id);
        if (!$item) {
            $this->error('Tarif introuvable');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            if (($item['pressing_code'] ?? '') !== $pressingCode) {
                $this->error('Accès refusé', 403);
                return;
            }
        } else {
            $pressingCode = $this->post('pressing_code') ?: $item['pressing_code'];
        }

        $this->requireActiveAbonnement($pressingCode, 'modifier vos tarifs articles');

        $notEmpty = Validator::validateRequiredFields([
            'article_code' => $_POST['article_code'] ?? '',
            'service_code' => $_POST['service_code'] ?? '',
            'prix_tarif' => $_POST['prix_tarif'] ?? ''
        ]);

        if ($notEmpty !== true) {
            $this->error('Veuillez renseigner tous les champs requis !');
            return;
        }

        $statut = ($this->post('actif') == 1) ? 'actif' : 'inactif';

        $data = [
            'id_tarif' => $id,
            'pressing_code' => $pressingCode,
            'article_code' => $this->post('article_code'),
            'service_code' => $this->post('service_code'),
            'prix_tarif' => (float)$this->post('prix_tarif'),
            'statut_tarif' => $statut,
            'updated_at_tarif' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data)) {
            $this->success('Tarif modifié avec succès !');
        } else {
            $this->error('Erreur lors de la modification');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = $this->post('id');
        $item = $id ? $this->model->getById($id) : null;
        if ($item) {
            $this->requireActiveAbonnement($item['pressing_code'] ?? null, 'activer ou désactiver des tarifs');
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut modifié avec succès!', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur');
            }
        } else {
            $this->error('Tarif introuvable!');
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'tarif/list');
                exit();
            }
            $encryptedId = $this->validator->crypter($id);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'tarif/list');
            exit();
        }

        $this->loadView('../views/tarifs_articles/details.php', [
            'tarif' => $item,
            'encryptedId' => $encryptedId
        ]);
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById($id);
            if (!$item) {
                header('Location: ' . RACINE . 'tarif/list');
                exit();
            }
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'tarif/list');
            exit();
        }

        $this->loadFormDataAndRender($item);
    }

    public function formulaire()
    {
        $this->requireAuth();
        $this->loadFormDataAndRender([]);
    }

    private function loadFormDataAndRender(array $tarif): void
    {
        $pressingCode = $this->getCurrentPressingCode();
        if (empty($pressingCode) && !empty($tarif['pressing_code'])) {
            $pressingCode = $tarif['pressing_code'];
        }

        $articleModel = new ModelArticle();
        if (!empty($pressingCode)) {
            $articles = $articleModel->getAllWithCategory($pressingCode);
        } else {
            $articles = $articleModel->getAllWithCategory();
        }

        $serviceModel = new ModelService();
        if (!empty($pressingCode)) {
            $services = $serviceModel->getByPressing($pressingCode);
        } else {
            $services = $serviceModel->getByStatus('actif');
        }

        $pressingModel = new ModelPressing();
        $pressings = $pressingModel->getByStatus('actif');

        $this->loadView('../views/tarifs_articles/edit.php', [
            'tarif' => $tarif,
            'articles' => $articles,
            'services' => $services,
            'pressings' => $pressings
        ]);
    }
}
