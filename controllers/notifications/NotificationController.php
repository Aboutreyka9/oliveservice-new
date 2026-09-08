<?php

class NotificationController extends BaseController
{
    protected function resolveModel()
    {
        return new ModelNotification();
    }

    /**
     * Page principale du centre de notifications
     */
    public function list()
    {
        $this->requireAuth();
        $userCode  = Context::user();
        $roles     = Context::roles();
        $etabCode  = Context::etablissement();
        $zoneCode  = Context::zone();

        $stats = $this->model->getStats($userCode, $roles, $etabCode, $zoneCode);

        $this->loadView('../views/notifications/list.php', [
            'stats' => $stats
        ]);
    }

    /**
     * API: Récupère la liste des notifications pour DataTables
     */
    public function apiList()
    {
        $this->requireAuth();
        $userCode  = Context::user();
        $roles     = Context::roles();
        $etabCode  = Context::etablissement();
        $zoneCode  = Context::zone();

        $items = $this->model->getAllForUser($userCode, $roles, $etabCode, $zoneCode, 300);
        $data = [];

        foreach ($items as $i) {
            $id = (int)$i['id_notification'];
            $idCrypte = $this->validator->crypter($id);
            $isLu = ((int)$i['lu_notification'] === 1);

            $destinataire = 'Tous (Établissement)';
            if (!empty($i['user_code'])) {
                $nom = trim(($i['nom_user'] ?? '') . ' ' . ($i['prenom_user'] ?? ''));
                $destinataire = !empty($nom) ? $nom : $i['user_code'];
            } elseif (!empty($i['role_target'])) {
                $destinataire = str_replace('ROLE_', '', $i['role_target']);
            }

            $data[] = [
                'id'                 => $id,
                'editId'             => $idCrypte,
                'code'               => $i['code_notification'],
                'type'               => $i['type_notification'] ?? 'cotisation',
                'titre'              => $i['titre_notification'] ?? '',
                'message'            => $i['message_notification'] ?? '',
                'reference'          => $i['reference_code'] ?? '',
                'url'                => $i['url_notification'] ?? '',
                'destinataire'       => $destinataire,
                'lu'                 => $isLu ? 1 : 0,
                'statut'             => $i['statut_notification'] ?? 'actif',
                'created_at'         => !empty($i['created_at_notification']) ? date('d/m/Y H:i', strtotime($i['created_at_notification'])) : '-',
                'created_at_raw'     => $i['created_at_notification'] ?? ''
            ];
        }

        $this->json(['data' => $data]);
    }

    /**
     * API: Marque une notification comme lue
     */
    public function marquerLu()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)($this->post('id') ?? 0);
        if ($id <= 0 && !empty($_GET['id'])) {
            $id = (int)$_GET['id'];
        }

        if ($id > 0 && $this->model->markAsRead($id)) {
            $this->success('Notification marquée comme lue !');
        } else {
            $this->error('Impossible de marquer la notification comme lue.');
        }
    }

    /**
     * API: Marque toutes les notifications de l'utilisateur comme lues
     */
    public function marquerToutLu()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $userCode  = Context::user();
        $roles     = Context::roles();
        $etabCode  = Context::etablissement();
        $zoneCode  = Context::zone();

        if ($this->model->markAllAsRead($userCode, $roles, $etabCode, $zoneCode)) {
            $this->success('Toutes les notifications ont été marquées comme lues !');
        } else {
            $this->error('Erreur lors de la mise à jour des notifications.');
        }
    }

    /**
     * Bascule le statut lu / non lu
     */
    public function changer()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id');
        if ($id && $this->model->toggleRead($id)) {
            $this->success('Statut de lecture mis à jour !', ['reload' => true]);
        } else {
            $this->error('Notification introuvable.');
        }
    }

    /**
     * Supprime une notification
     */
    public function delete()
    {
        $this->requirePost(false);
        $this->requireAuth();

        $id = (int)$this->post('id');
        if ($id && $this->model->deleteNotification($id)) {
            $this->success('Notification supprimée avec succès !');
        } else {
            $this->error('Erreur lors de la suppression.');
        }
    }

    /**
     * API: Retourne les compteurs stats en JSON
     */
    public function stats()
    {
        $this->requireAuth();
        $userCode  = Context::user();
        $roles     = Context::roles();
        $etabCode  = Context::etablissement();
        $zoneCode  = Context::zone();

        $stats = $this->model->getStats($userCode, $roles, $etabCode, $zoneCode);
        $this->json($stats);
    }

    /**
     * Envoi manuel d'une notification système (Admin)
     */
    public function add()
    {
        $this->requirePost(false);
        $this->requirePermission('ADMIN_MANAGE_USERS');

        $titre = trim($this->post('titre_notification') ?? '');
        $message = trim($this->post('message_notification') ?? '');
        $type = $this->post('type_notification') ?: 'systeme';
        $roleTarget = $this->post('role_target') ?: null;

        if (empty($titre) || empty($message)) {
            $this->error('Veuillez renseigner le titre et le message de la notification !');
            return;
        }

        $code = NotificationService::send([
            'user_code'            => null,
            'role_target'          => $roleTarget,
            'type_notification'    => $type,
            'titre_notification'   => $titre,
            'message_notification' => $message,
            'etablissement_code'   => Context::etablissement(),
            'zone_code'            => Context::zone() ?: null,
            'annee_code'           => Context::annee() ?: null
        ]);

        if ($code) {
            $this->success('Notification diffusée avec succès !');
        } else {
            $this->error('Erreur lors de la diffusion de la notification.');
        }
    }
}
