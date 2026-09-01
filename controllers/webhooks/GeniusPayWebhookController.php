<?php

class GeniusPayWebhookController
{
    public function handle()
    {
        header('Content-Type: application/json');

        $rawBody = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';
        $timestamp = $_SERVER['HTTP_X_WEBHOOK_TIMESTAMP'] ?? '';
        $event = $_SERVER['HTTP_X_WEBHOOK_EVENT'] ?? '';
        $environment = $_SERVER['HTTP_X_WEBHOOK_ENVIRONMENT'] ?? 'sandbox';

        if (empty($signature) || empty($timestamp) || empty($event)) {
            http_response_code(400);
            echo json_encode([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Required webhook headers are missing.',
            ]);
            exit();
        }

        // Vérification du timestamp (anti-replay attack, tolérance 5 min)
        $currentTime = time();
        $timeDiff = abs($currentTime - (int)$timestamp);
        if ($timeDiff > 300) {
            http_response_code(400);
            echo json_encode([
                'type' => 'about:blank',
                'title' => 'Bad Request',
                'status' => 400,
                'detail' => 'Timestamp too old or invalid.',
            ]);
            exit();
        }

        // Vérification de la signature HMAC SHA-256
        $config = GeniusPayConfig::get();
        $secretsToTry = array_unique(array_filter([
            $config['webhook_secret'] ?? '',
            $config['webhook_secret_alt'] ?? '',
            getenv('GENIUSPAY_WEBHOOK_SECRET_SANDBOX') ?: '',
            getenv('GENIUSPAY_WEBHOOK_SECRET_LIVE') ?: ''
        ]));

        $signatureValid = false;
        foreach ($secretsToTry as $s) {
            if ($this->verifySignature($signature, $rawBody, $timestamp, $s)) {
                $signatureValid = true;
                break;
            }
        }

        if (!$signatureValid) {
            http_response_code(401);
            echo json_encode([
                'type' => 'about:blank',
                'title' => 'Unauthorized',
                'status' => 401,
                'detail' => 'Invalid webhook signature.',
            ]);
            exit();
        }

        $payload = json_decode($rawBody, true);
        if (!$payload) {
            http_response_code(400);
            echo json_encode(['status' => 400, 'detail' => 'Invalid JSON body']);
            exit();
        }

        try {
            $this->processEvent($event, $payload, $environment);

            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Webhook processed successfully',
                'event' => $event
            ]);
            exit();
        } catch (Exception $e) {
            error_log('GeniusPay Webhook Error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'type' => 'about:blank',
                'title' => 'Internal Server Error',
                'status' => 500,
                'detail' => 'Failed to process webhook: ' . $e->getMessage(),
            ]);
            exit();
        }
    }

    private function verifySignature(string $signature, string $rawBody, string $timestamp, string $secret): bool
    {
        if (empty($secret)) {
            return false;
        }
        $data = $timestamp . '.' . $rawBody;
        $expectedSignature = hash_hmac('sha256', $data, $secret);
        return hash_equals($expectedSignature, $signature);
    }

    private function processEvent(string $event, array $payload, string $environment): void
    {
        $data = $payload['data'] ?? [];
        $pdo = (new Database())->getCon();

        switch ($event) {
            case 'payment.success':
                $this->handlePaymentSuccess($pdo, $data, $environment);
                break;

            case 'payment.failed':
                $this->handlePaymentFailed($pdo, $data, $environment);
                break;

            case 'payment.refunded':
                $this->handlePaymentRefunded($pdo, $data, $environment);
                break;

            case 'cashout.completed':
                $this->handleCashoutCompleted($pdo, $data, $environment);
                break;

            case 'cashout.failed':
                $this->handleCashoutFailed($pdo, $data, $environment);
                break;

            case 'webhook.test':
                error_log('GeniusPay Webhook test received successfully.');
                break;

            default:
                error_log('GeniusPay Webhook: unhandled event ' . $event);
        }
    }

    private function handlePaymentSuccess(PDO $pdo, array $data, string $environment): void
    {
        $orderCode = $data['metadata']['order_id'] ?? ($data['reference'] ?? null);
        $amount = (float)($data['amount'] ?? 0);
        $provider = strtolower($data['provider'] ?? 'wave');
        $reference = $data['reference'] ?? ('GP-' . uniqid());

        if (!$orderCode) {
            return;
        }

        // 1. Récupérer les informations de la commande
        $stmt = $pdo->prepare("SELECT * FROM " . TABLES::COMMANDES . " WHERE code_commande = ? LIMIT 1");
        $stmt->execute([$orderCode]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$commande) {
            return;
        }

        $pressingCode = $commande['pressing_code'];
        $clientCode = $commande['client_code'];

        // 2. Mettre à jour ou insérer dans la table paiements
        $stmtPay = $pdo->prepare("SELECT id_paiement FROM " . TABLES::PAIEMENTS . " WHERE commande_code = ? LIMIT 1");
        $stmtPay->execute([$orderCode]);
        $existingPayment = $stmtPay->fetch(PDO::FETCH_ASSOC);

        if ($existingPayment) {
            $updateStmt = $pdo->prepare("
                UPDATE " . TABLES::PAIEMENTS . "
                SET montant_paiement = ?,
                    mode_paiement = ?,
                    reference_geniuspay = ?,
                    provider_paiement = ?,
                    environment_paiement = ?,
                    statut_paiement = 'valide',
                    updated_at_paiement = NOW()
                WHERE id_paiement = ?
            ");
            $updateStmt->execute([
                $amount ?: $commande['montant_total_commande'],
                $provider,
                $reference,
                $provider,
                $environment,
                $existingPayment['id_paiement']
            ]);
        } else {
            $codePay = 'PAY-' . strtoupper(bin2hex(random_bytes(3)));
            $insertStmt = $pdo->prepare("
                INSERT INTO " . TABLES::PAIEMENTS . " (
                    code_paiement, commande_code, montant_paiement, mode_paiement,
                    reference_geniuspay, provider_paiement, environment_paiement,
                    statut_paiement, created_at_paiement
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'valide', NOW())
            ");
            $insertStmt->execute([
                $codePay,
                $orderCode,
                $amount ?: $commande['montant_total_commande'],
                $provider,
                $reference,
                $provider,
                $environment
            ]);
        }

        // 3. Notifications en temps réel
        $nomProvider = strtoupper($provider);
        $montantFormate = number_format($amount ?: (float)$commande['montant_total_commande'], 0, ',', ' ');

        if ($clientCode) {
            NotificationService::notifyClient(
                $clientCode,
                'paiement',
                'Paiement validé !',
                "Votre paiement de {$montantFormate} FCFA via {$nomProvider} a été confirmé pour la commande #{$orderCode}.",
                $orderCode
            );
        }

        if ($pressingCode) {
            NotificationService::notifyPressing(
                $pressingCode,
                'paiement',
                'Nouveau paiement en ligne',
                "La commande #{$orderCode} a été payée en ligne via {$nomProvider} ({$montantFormate} FCFA). Votre solde a été crédité.",
                $orderCode
            );
        }
    }

    private function handlePaymentFailed(PDO $pdo, array $data, string $environment): void
    {
        $orderCode = $data['metadata']['order_id'] ?? ($data['reference'] ?? null);
        if (!$orderCode) return;

        $stmt = $pdo->prepare("SELECT * FROM " . TABLES::COMMANDES . " WHERE code_commande = ? LIMIT 1");
        $stmt->execute([$orderCode]);
        $commande = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$commande) return;

        $clientCode = $commande['client_code'];
        if ($clientCode) {
            NotificationService::notifyClient(
                $clientCode,
                'paiement',
                'Échec du paiement',
                "Le paiement pour votre commande #{$orderCode} n'a pas pu aboutir. Veuillez réessayer.",
                $orderCode
            );
        }
    }

    private function handlePaymentRefunded(PDO $pdo, array $data, string $environment): void
    {
        $orderCode = $data['metadata']['order_id'] ?? ($data['reference'] ?? null);
        if (!$orderCode) return;

        $stmt = $pdo->prepare("UPDATE " . TABLES::PAIEMENTS . " SET statut_paiement = 'annule' WHERE commande_code = ?");
        $stmt->execute([$orderCode]);
    }

    private function handleCashoutCompleted(PDO $pdo, array $data, string $environment): void
    {
        $retraitCode = $data['metadata']['retrait_code'] ?? null;
        $reference = $data['reference'] ?? null;
        $amount = (float)($data['amount'] ?? 0);

        $retrait = null;
        if (!empty($retraitCode)) {
            $stmt = $pdo->prepare("SELECT * FROM retraits_pressings WHERE code_retrait = ? LIMIT 1");
            $stmt->execute([$retraitCode]);
            $retrait = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$retrait && !empty($reference)) {
            $stmt = $pdo->prepare("SELECT * FROM retraits_pressings WHERE reference_geniuspay = ? LIMIT 1");
            $stmt->execute([$reference]);
            $retrait = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$retrait) return;

        $pdo->prepare("
            UPDATE retraits_pressings
            SET statut_retrait = 'complete',
                reference_geniuspay = COALESCE(?, reference_geniuspay),
                updated_at_retrait = NOW()
            WHERE id_retrait = ?
        ")->execute([$reference, $retrait['id_retrait']]);

        $pressingCode = $retrait['pressing_code'];
        $montantFormate = number_format($amount ?: (float)$retrait['montant_demande'], 0, ',', ' ');
        $tel = $retrait['telephone_beneficiaire'];

        NotificationService::notifyPressing(
            $pressingCode,
            'retrait',
            'Retrait complété avec succès !',
            "Votre demande de retrait de {$montantFormate} FCFA vers le {$tel} a été transférée avec succès.",
            $retrait['code_retrait']
        );
    }

    private function handleCashoutFailed(PDO $pdo, array $data, string $environment): void
    {
        $retraitCode = $data['metadata']['retrait_code'] ?? null;
        $reference = $data['reference'] ?? null;
        $reason = $data['failure_reason'] ?? 'Échec du transfert Mobile Money';

        $retrait = null;
        if (!empty($retraitCode)) {
            $stmt = $pdo->prepare("SELECT * FROM retraits_pressings WHERE code_retrait = ? LIMIT 1");
            $stmt->execute([$retraitCode]);
            $retrait = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        if (!$retrait && !empty($reference)) {
            $stmt = $pdo->prepare("SELECT * FROM retraits_pressings WHERE reference_geniuspay = ? LIMIT 1");
            $stmt->execute([$reference]);
            $retrait = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        if (!$retrait) return;

        $pdo->prepare("
            UPDATE retraits_pressings
            SET statut_retrait = 'echoue',
                motif_rejet = ?,
                updated_at_retrait = NOW()
            WHERE id_retrait = ?
        ")->execute([$reason, $retrait['id_retrait']]);

        $pressingCode = $retrait['pressing_code'];
        $montantFormate = number_format((float)$retrait['montant_demande'], 0, ',', ' ');

        NotificationService::notifyPressing(
            $pressingCode,
            'retrait',
            'Échec du retrait',
            "Le virement de votre retrait de {$montantFormate} FCFA a échoué ({$reason}). Vos fonds restent disponibles sur votre solde.",
            $retrait['code_retrait']
        );
    }
}
