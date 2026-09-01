<?php

class RetraitController extends BaseController
{
    use PressingAware;

    protected function resolveModel()
    {
        return new ModelRetrait();
    }

    public function list()
    {
        $this->requireAuth();

        if ($this->isPressing()) {
            $pressingCode = $this->getCurrentPressingCode();
            $solde = $this->model->getSoldeDetails($pressingCode ?? '');
            $retraits = $this->model->getRetraitsByPressing($pressingCode ?? '');
            $config = GeniusPayConfig::get();

            $this->loadView('../views/retraits/pressing_wallet.php', [
                'solde' => $solde,
                'retraits' => $retraits,
                'minRetrait' => (float)($config['minimum_retrait'] ?? 2000),
                'pressingCode' => $pressingCode
            ]);
        } else {
            // Super Admin
            $retraits = $this->model->getAllRetraits();
            $config = GeniusPayConfig::get();
            $this->loadView('../views/retraits/admin_list.php', [
                'retraits' => $retraits,
                'mode' => $config['mode']
            ]);
        }
    }

    public function apiSolde()
    {
        $this->requireAuth();
        $pressingCode = $this->getCurrentPressingCode();
        if (!$pressingCode) {
            $this->error('Pressing introuvable');
            return;
        }

        $solde = $this->model->getSoldeDetails($pressingCode);
        $this->json(['status' => 1, 'data' => $solde]);
    }

    public function apiList()
    {
        $this->requireAuth();
        if ($this->isPressing()) {
            $pressingCode = $this->getCurrentPressingCode();
            $data = $this->model->getRetraitsByPressing($pressingCode ?? '');
        } else {
            $data = $this->model->getAllRetraits();
        }

        $this->json(['data' => $data]);
    }

    public function demander()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isPressing()) {
            $this->error('Seuls les pressings peuvent effectuer des demandes de retrait.');
            return;
        }

        $pressingCode = $this->getCurrentPressingCode();
        if (!$pressingCode) {
            $this->error('Code pressing non associé au compte.');
            return;
        }

        $montant = (float)$this->post('montant');
        $operateur = trim($this->post('operateur') ?? 'wave');
        $telephone = trim($this->post('telephone') ?? '');
        $nomBeneficiaire = trim($this->post('nom_beneficiaire') ?? '');

        if ($montant <= 0 || empty($telephone)) {
            $this->error('Veuillez renseigner un montant et un numéro de téléphone valides.');
            return;
        }

        $validOps = ['wave', 'orange_money', 'mtn_money', 'moov_money'];
        if (!in_array($operateur, $validOps, true)) {
            $this->error('Opérateur non valide.');
            return;
        }

        $result = $this->model->createRetrait($pressingCode, $montant, $operateur, $telephone, $nomBeneficiaire);

        if ($result['success']) {
            // Notifier le super admin
            NotificationService::notifyPressing(
                $pressingCode,
                'retrait',
                'Demande de retrait enregistrée',
                "Votre demande de retrait de " . number_format($montant, 0, ',', ' ') . " FCFA a été transmise à l'administrateur pour validation.",
                $result['code_retrait']
            );

            $this->success($result['message'], ['code_retrait' => $result['code_retrait']]);
        } else {
            $this->error($result['message']);
        }
    }

    public function changerStatut()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée à l\'administrateur.');
            return;
        }

        $id = (int)$this->post('id_retrait');
        $nouveauStatut = trim($this->post('statut') ?? '');
        $reference = trim($this->post('reference') ?? '');
        $motif = trim($this->post('motif') ?? '');

        $validStatuts = ['approuve', 'complete', 'rejete', 'echoue'];
        if (!in_array($nouveauStatut, $validStatuts, true)) {
            $this->error('Statut invalide.');
            return;
        }

        // Récupérer le retrait
        $stmt = $this->model->getCon()->prepare("SELECT * FROM retraits_pressings WHERE id_retrait = ? LIMIT 1");
        $stmt->execute([$id]);
        $retrait = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$retrait) {
            $this->error('Demande de retrait introuvable.');
            return;
        }

        // Si approbation par l'administrateur : Déclencher le Cashout GeniusPay
        if ($nouveauStatut === 'approuve') {
            $cashoutResult = GeniusPayService::initiateCashout($retrait);

            if (empty($cashoutResult['success'])) {
                $this->error('Échec du virement GeniusPay : ' . ($cashoutResult['message'] ?? 'Erreur API GeniusPay'));
                return;
            }

            $reference = $cashoutResult['reference'] ?? ('CASHOUT-' . strtoupper($retrait['operateur_retrait']) . '-' . rand(1000, 9999));

            if ($this->model->changerStatutRetrait($id, 'approuve', $reference ?: null, null)) {
                // Notifier le pressing que le virement est en cours
                NotificationService::notifyPressing(
                    $retrait['pressing_code'],
                    'retrait',
                    'Retrait Approuvé • Virement en cours',
                    "Votre demande de retrait de " . number_format((float)$retrait['montant_demande'], 0, ',', ' ') . " FCFA a été approuvée par l'administrateur. Le transfert Mobile Money (" . strtoupper($retrait['operateur_retrait']) . ") est en cours d'exécution.",
                    $retrait['code_retrait']
                );

                $this->success('Demande approuvée et virement Mobile Money GeniusPay déclenché avec succès !', [
                    'reference' => $reference,
                    'status' => 'approuve'
                ]);
                return;
            }
        }

        if ($this->model->changerStatutRetrait($id, $nouveauStatut, $reference ?: null, $motif ?: null)) {
            if ($nouveauStatut === 'rejete') {
                NotificationService::notifyPressing(
                    $retrait['pressing_code'],
                    'retrait',
                    'Demande de retrait rejetée',
                    "Votre demande de retrait de " . number_format((float)$retrait['montant_demande'], 0, ',', ' ') . " FCFA a été rejetée. Motif : " . ($motif ?: 'Non spécifié') . ". Les fonds restent sur votre solde.",
                    $retrait['code_retrait']
                );
            }
            $this->success('Statut du retrait mis à jour avec succès.');
        } else {
            $this->error('Erreur lors de la mise à jour.');
        }
    }

    public function simulerWebhookCashout()
    {
        $this->requirePost(false);
        $this->requireAuth();

        if (!$this->isSuperAdmin()) {
            $this->error('Action réservée à l\'administrateur.');
            return;
        }

        $codeRetrait = trim($this->post('code_retrait') ?? '');
        $statut = trim($this->post('statut') ?? 'complete');

        if (empty($codeRetrait)) {
            $this->error('Code de retrait manquant.');
            return;
        }

        $stmt = $this->model->getCon()->prepare("SELECT * FROM retraits_pressings WHERE code_retrait = ? LIMIT 1");
        $stmt->execute([$codeRetrait]);
        $retrait = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$retrait) {
            $this->error('Demande de retrait introuvable.');
            return;
        }

        if ($statut === 'complete') {
            $ref = 'CASHOUT-' . strtoupper($retrait['operateur_retrait']) . '-' . substr(time(), -4) . rand(10, 99);
            $this->model->changerStatutRetrait((int)$retrait['id_retrait'], 'complete', $ref, null);

            NotificationService::notifyPressing(
                $retrait['pressing_code'],
                'retrait',
                'Retrait complété avec succès !',
                "Votre virement de " . number_format((float)$retrait['montant_demande'], 0, ',', ' ') . " FCFA vers le {$retrait['telephone_beneficiaire']} a été crédité avec succès par le réseau " . strtoupper($retrait['operateur_retrait']) . ".",
                $codeRetrait
            );

            $this->success('Webhook GeniusPay cashout.completed simulé avec succès !', ['reload' => true]);
        } else {
            $this->model->changerStatutRetrait((int)$retrait['id_retrait'], 'echoue', null, 'Échec réseau Mobile Money simulé');
            $this->success('Webhook GeniusPay cashout.failed simulé avec succès !', ['reload' => true]);
        }
    }
}
