<?php

class PanierDetailController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelPanierDetail();
    }

    public function list()
    {
        $this->requireAuth();
        $this->loadView('../views/panier_details/list.php');
    }

    public function apiList()
    {
        $this->requireAuth();
        $this->error('Non implemente');
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
