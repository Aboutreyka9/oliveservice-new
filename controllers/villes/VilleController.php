<?php

class VilleController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelVille();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/villes/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAllWithQuartiersCount();
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_ville']);
            $data[] = [
                'code' => $i['code_ville'],
                'libelle' => $i['libelle_ville'],
                'total_quartiers' => (int)($i['total_quartiers'] ?? 0),
                'statut' => $i['statut_ville'] ?? 'actif',
                'id' => (int)$i['id_ville'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $libelle = trim($this->post('libelle_ville'));
        if (empty($libelle)) {
            $this->error('Veuillez renseigner le nom de la ville !');
            return;
        }

        if (!$this->checkUnique(TABLES::VILLES, 'libelle_ville', $libelle, 'nom de ville')) return;

        $code = trim($this->post('code_ville'));
        if (empty($code)) {
            $code = 'VIL-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $libelle), 0, 3));
            if (strlen($code) < 7) {
                $code = $this->validator->generateCode(TABLES::VILLES, 'code_ville', 'VIL-', 5);
            }
        }

        if ($this->validator->getByElement(TABLES::VILLES, 'code_ville', $code)) {
            $this->error('Ce code ville existe déjà !');
            return;
        }

        $data = [
            'code_ville' => $code,
            'libelle_ville' => $libelle,
            'statut_ville' => 'actif',
            'created_at_ville' => date('Y-m-d H:i:s')
        ];

        if ($this->model->create($data)) {
            $this->success('Ville ajoutée avec succès !', ['code_ville' => $code]);
        } else {
            $this->error('Erreur lors de l\'ajout de la ville');
        }
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id_ville');
        $libelle = trim($this->post('libelle_ville'));
        $statut = in_array($this->post('statut_ville'), ['actif', 'inactif']) ? $this->post('statut_ville') : 'actif';

        if (!$id || empty($libelle)) {
            $this->error('Veuillez renseigner tous les champs obligatoires !');
            return;
        }

        $data = [
            'id_ville' => $id,
            'libelle_ville' => $libelle,
            'statut_ville' => $statut,
            'updated_at_ville' => date('Y-m-d H:i:s')
        ];

        if ($this->model->update($data, $id)) {
            $this->success('Ville modifiée avec succès !');
        } else {
            $this->error('Erreur lors de la modification de la ville');
        }
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $id = (int)$this->post('id');

        if ($id && $this->model->getById($id)) {
            if ($this->model->toggleStatus($id)) {
                $this->success('Statut de la ville modifié avec succès !', ['id' => $id, 'reload' => true]);
            } else {
                $this->error('Erreur lors du changement de statut');
            }
        } else {
            $this->error('Ville introuvable !');
        }
    }

    public function edition($details)
    {
        $this->requireAuth();
        try {
            $id = $this->validator->decrypter($details);
            $item = $this->model->getById((int)$id);
            if (!$item) {
                header('Location: ' . RACINE . 'ville/list');
                exit();
            }

            $encryptedId = $this->validator->crypter($id);
            $quartiers = $this->model->getQuartiersByVille($item['code_ville']);

            foreach ($quartiers as &$q) {
                $q['editId'] = $this->validator->crypter($q['id_quartier']);
            }

            $this->loadView('../views/villes/edit.php', [
                'ville' => $item,
                'quartiers' => $quartiers,
                'encryptedId' => $encryptedId
            ]);
        } catch (Exception $e) {
            header('Location: ' . RACINE . 'ville/list');
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
                $this->error('Ville introuvable');
                return;
            }

            $quartiers = $this->model->getQuartiersByVille($item['code_ville']);
            $this->json([
                'data' => $item,
                'quartiers' => $quartiers
            ]);
        } catch (Exception $e) {
            $this->error('Identifiant invalide');
        }
    }

    public function getActive()
    {
        $this->requireAuth();
        $items = $this->model->getByStatus('actif');
        $options = [];
        $options[''] = 'Sélectionner une ville';
        foreach ($items as $i) {
            $options[$i['code_ville']] = $i['libelle_ville'];
        }
        $this->json(['options' => $options]);
    }
}
