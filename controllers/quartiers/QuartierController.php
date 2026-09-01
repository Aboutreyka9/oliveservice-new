<?php

class QuartierController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelQuartier();
    }

    public function list()
    {
        $this->requireAuth();
        $villeModel = new ModelVille();
        $villes = $villeModel->getByStatus('actif');

        $this->loadView('../views/quartiers/list.php', [
            'villes' => $villes
        ]);
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $data = [];

        // Récupérer les libellés des villes
        $villeModel = new ModelVille();
        $villes = $villeModel->getAll();
        $villesMap = [];
        foreach ($villes as $v) {
            $villesMap[$v['code_ville']] = $v['libelle_ville'];
        }

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_quartier']);
            $nomVille = $villesMap[$i['ville_code']] ?? ($i['ville_code'] ?? '-');

            $data[] = [
                'code' => $i['code_quartier'],
                'libelle' => $i['libelle_quartier'],
                'ville_code' => $i['ville_code'],
                'ville_nom' => $nomVille,
                'statut' => $i['statut_quartier'] ?? 'actif',
                'id' => (int)$i['id_quartier'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $libelle = trim($this->post('libelle_quartier'));
        $villeCode = trim($this->post('ville_code'));

        if (empty($libelle) || empty($villeCode)) {
            $this->error('Veuillez renseigner le nom du quartier et sélectionner une ville !');
            return;
        }

        $code = trim($this->post('code_quartier'));
        if (empty($code)) {
            $code = $this->validator->generateCode(TABLES::QUARTIERS, 'code_quartier', 'QTR-', 5);
        }

        if ($this->validator->getByElement(TABLES::QUARTIERS, 'code_quartier', $code)) {
            $this->error('Ce code quartier existe déjà !');
            return;
        }

        $data = [
            'code_quartier' => $code,
            'ville_code' => $villeCode,
            'libelle_quartier' => $libelle,
            'statut_quartier' => 'actif',
            'created_at_quartier' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Quartier ajouté avec succès !', ['code_quartier' => $code]);
        } else {
            $this->error('Erreur lors de l\'ajout du quartier');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id_quartier');
        $libelle = trim($this->post('libelle_quartier'));
        $villeCode = trim($this->post('ville_code'));
        $statut = in_array($this->post('statut_quartier'), ['actif', 'inactif']) ? $this->post('statut_quartier') : 'actif';

        if (!$id || empty($libelle) || empty($villeCode)) {
            $this->error('Veuillez renseigner tous les champs obligatoires !');
            return;
        }

        $data = [
            'id_quartier' => $id,
            'ville_code' => $villeCode,
            'libelle_quartier' => $libelle,
            'statut_quartier' => $statut,
            'updated_at_quartier' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            $this->success('Quartier modifié avec succès !');
        } else {
            $this->error('Erreur lors de la modification du quartier');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut du quartier modifié avec succès !', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut');
            }
        } else {
            $this->error('Quartier introuvable !');
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById((int)$id);
            if (!$item) {
                header('Location: ' . RACINE . 'quartier/list');
                exit();
            }

            $villeModel = new ModelVille();
            $villes = $villeModel->getByStatus('actif');

            $encryptedId = $this->validator->crypter($id);
            $this->loadView('../views/quartiers/edit.php', [
                'quartier' => $item,
                'villes' => $villes,
                'encryptedId' => $encryptedId
            ]);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'quartier/list');
            exit();
        }
    }

    public function details($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById((int)$id);
            if (!$item) {
                $this->error('Quartier introuvable');
                return;
            }

            $this->json(['data' => $item]);
        } catch (Exception $e) {
            $this->error('Identifiant invalide');
        }
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner un quartier';
        foreach ($items as $i) {
            $options[$i['code_quartier']] = $i['libelle_quartier'];
        }
        $this->json(['options' => $options]);
    }
}
