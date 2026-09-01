<?php

class PanierController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelPanier();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/paniers/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $items = $this->model->getAll();
        $pressingCode = $this->getCurrentPressingCode();
        if ($pressingCode !== null) {
            $items = array_filter($items, function($i) use ($pressingCode) {
                return $i['pressing_code'] === $pressingCode;
            });
        }
        $data = [];

        foreach ($items as $i) {
            $idCrypte = $this->validator->crypter($i['id_panier']);
            $data[] = [
                'code' => $i['code_panier'],
                'client' => $i['client_code'] ?? '',
                'pressing' => $i['pressing_code'] ?? '',
                'statut' => $i['statut_panier'],
                'id' => $i['id_panier'],
                'editId' => $idCrypte
            ];
        }

        $this->json(['data' => $data]);
    }

    public function add()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function edit()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function details($details)
    {
        $this->requireAuth();
        $this->error('Non implemente');
    }

    public function edition($details)
    {
        $this->requireAuth();
        $this->error('Non implemente');
    }
}
