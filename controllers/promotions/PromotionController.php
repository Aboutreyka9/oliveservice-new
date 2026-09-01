<?php

class PromotionController extends BaseController
{
    private ModelPromotion $promotionModel;

    public function __construct()
    {
        $this->promotionModel = new ModelPromotion();
        parent::__construct();
    }

    protected function resolveModel()
    {
        return new ModelPromotion();
    }

    public function list()
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $_SESSION['error_msg'] = "Accès réservé au Super Admin.";
            header('Location: ' . RACINE);
            exit();
        }

        $promotions = $this->promotionModel->getAllPromotions();

        $data = [
            'titre' => 'Gestion des Promotions & Codes Promo',
            'promotions' => $promotions
        ];

        $this->loadView('../views/promotions/list.php', $data);
    }

    public function add()
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $_SESSION['error_msg'] = "Accès réservé au Super Admin.";
            header('Location: ' . RACINE);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $this->promotionModel->createPromotion($_POST);
            if ($res['success']) {
                $_SESSION['success_msg'] = "Code promo créé avec succès !";
                header('Location: ' . RACINE . 'promotion/list');
                exit();
            } else {
                $_SESSION['error_msg'] = $res['error'];
            }
        }

        $data = ['titre' => 'Créer un nouveau Code Promo'];
        $this->loadView('../views/promotions/add.php', $data);
    }

    public function edit($params = null)
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $_SESSION['error_msg'] = "Accès réservé au Super Admin.";
            header('Location: ' . RACINE);
            exit();
        }

        $id = is_array($params) ? ($params['id'] ?? $params[0] ?? null) : $params;
        if (!$id) {
            header('Location: ' . RACINE . 'promotion/list');
            exit();
        }

        $promo = $this->promotionModel->getPromotionById((int)$id);
        if (!$promo) {
            $_SESSION['error_msg'] = "Promotion introuvable.";
            header('Location: ' . RACINE . 'promotion/list');
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $res = $this->promotionModel->updatePromotion((int)$id, $_POST);
            if ($res['success']) {
                $_SESSION['success_msg'] = "Code promo mis à jour avec succès !";
                header('Location: ' . RACINE . 'promotion/list');
                exit();
            } else {
                $_SESSION['error_msg'] = $res['error'];
            }
        }

        $data = [
            'titre' => 'Modifier le Code Promo ' . $promo['code_promo'],
            'promo' => $promo
        ];
        $this->loadView('../views/promotions/edit.php', $data);
    }

    public function changer()
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'msg' => 'Accès refusé']);
            exit();
        }

        $id = $_POST['id'] ?? null;
        if ($id) {
            $ok = $this->promotionModel->toggleStatus((int)$id);
            header('Content-Type: application/json');
            echo json_encode(['status' => $ok, 'msg' => $ok ? 'Statut mis à jour' : 'Erreur']);
            exit();
        }

        header('Content-Type: application/json');
        echo json_encode(['status' => false, 'msg' => 'ID manquant']);
        exit();
    }

    public function delete($params = null)
    {
        $this->requireAuth();
        if (!$this->isSuperAdmin()) {
            $_SESSION['error_msg'] = "Accès réservé au Super Admin.";
            header('Location: ' . RACINE);
            exit();
        }

        $id = is_array($params) ? ($params['id'] ?? $params[0] ?? null) : $params;
        if ($id) {
            $this->promotionModel->deletePromotion((int)$id);
            $_SESSION['success_msg'] = "Code promo supprimé avec succès.";
        }

        header('Location: ' . RACINE . 'promotion/list');
        exit();
    }
}
