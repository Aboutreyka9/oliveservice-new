<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../config/const.php';
require_once __DIR__ . '/../models/notifications/ModelNotification.php';

class NotificationService
{
    private static ?ModelNotification $model = null;

    private static function getModel(): ModelNotification
    {
        if (self::$model === null) {
            self::$model = new ModelNotification();
        }
        return self::$model;
    }

    /**
     * Envoie / Enregistre une notification en base
     */
    public static function send(array $data): ?string
    {
        return self::getModel()->createNotification($data);
    }

    /**
     * Notification lors de l'enregistrement d'une cotisation client
     */
    public static function notifyCotisationClient(array $params): ?string
    {
        $montant = (float)($params['montant'] ?? 0);
        $montantFmt = number_format($montant, 0, ',', ' ');
        $clientNom = !empty($params['client_nom']) ? $params['client_nom'] : 'Client';
        $sousCode = $params['souscription_code'] ?? '';
        $refCode = $params['reference_code'] ?? '';
        $etabCode = $params['etablissement_code'] ?? '';
        $zoneCode = $params['zone_code'] ?? null;
        $anneeCode = $params['annee_code'] ?? null;
        $userCode = $params['user_code'] ?? null;

        $url = !empty($sousCode) 
            ? RACINE . 'cautisation-payment/situation/' . urlencode($sousCode)
            : RACINE . 'cautisation/list';

        return self::send([
            'user_code'            => null, // Visible par les profils concernés
            'role_target'          => 'ROLE_FINANCE', // Notifie en priorité la finance et les admins
            'type_notification'    => 'cotisation',
            'titre_notification'   => 'Nouvelle cotisation collectée',
            'message_notification' => "Cotisation de {$montantFmt} FCFA collectée pour {$clientNom} (Réf : {$refCode}).",
            'reference_code'       => $refCode ?: $sousCode,
            'url_notification'     => $url,
            'lu_notification'      => 0,
            'statut_notification'  => 'actif',
            'etablissement_code'   => $etabCode,
            'zone_code'            => $zoneCode,
            'annee_code'           => $anneeCode
        ]);
    }

    /**
     * Notification lorsqu'une souscription est 100% soldée (éligible au pack / livraison)
     */
    public static function notifySouscriptionSoldee(array $params): ?string
    {
        $clientNom = !empty($params['client_nom']) ? $params['client_nom'] : 'Client';
        $refCode = $params['reference_code'] ?? '';
        $montantTotal = (float)($params['montant_total'] ?? 0);
        $montantFmt = number_format($montantTotal, 0, ',', ' ');
        $etabCode = $params['etablissement_code'] ?? '';
        $zoneCode = $params['zone_code'] ?? null;
        $anneeCode = $params['annee_code'] ?? null;

        $url = RACINE . 'distribution/list';

        return self::send([
            'user_code'            => null,
            'role_target'          => 'ROLE_GESTIONNAIRE', // Gestionnaire de stock / distribution & Admin
            'type_notification'    => 'souscription',
            'titre_notification'   => 'Souscription 100% Soldée 🎉',
            'message_notification' => "La souscription {$refCode} de {$clientNom} ({$montantFmt} FCFA) est entièrement soldée. Le pack peut être distribué.",
            'reference_code'       => $refCode,
            'url_notification'     => $url,
            'lu_notification'      => 0,
            'statut_notification'  => 'actif',
            'etablissement_code'   => $etabCode,
            'zone_code'            => $zoneCode,
            'annee_code'           => $anneeCode
        ]);
    }

    /**
     * Notification lors de la soumission d'un versement commercial
     */
    public static function notifyVersementSoumis(array $params): ?string
    {
        $montant = (float)($params['montant'] ?? 0);
        $montantFmt = number_format($montant, 0, ',', ' ');
        $commercialNom = !empty($params['commercial_nom']) ? $params['commercial_nom'] : 'Un agent commercial';
        $refCode = $params['reference_code'] ?? '';
        $etabCode = $params['etablissement_code'] ?? '';
        $zoneCode = $params['zone_code'] ?? null;
        $anneeCode = $params['annee_code'] ?? null;

        return self::send([
            'user_code'            => null,
            'role_target'          => 'ROLE_FINANCE',
            'type_notification'    => 'versement',
            'titre_notification'   => 'Nouveau versement de caisse',
            'message_notification' => "{$commercialNom} a transmis un versement de {$montantFmt} FCFA ({$refCode}) en attente de validation.",
            'reference_code'       => $refCode,
            'url_notification'     => RACINE . 'versement/list',
            'lu_notification'      => 0,
            'statut_notification'  => 'actif',
            'etablissement_code'   => $etabCode,
            'zone_code'            => $zoneCode,
            'annee_code'           => $anneeCode
        ]);
    }

    /**
     * Notification au commercial après validation ou rejet de son versement
     */
    public static function notifyVersementValide(array $params): ?string
    {
        $montant = (float)($params['montant'] ?? 0);
        $montantFmt = number_format($montant, 0, ',', ' ');
        $commercialCode = $params['commercial_code'] ?? null;
        $statut = $params['statut'] ?? 'valide';
        $refCode = $params['reference_code'] ?? '';
        $etabCode = $params['etablissement_code'] ?? '';
        $zoneCode = $params['zone_code'] ?? null;
        $anneeCode = $params['annee_code'] ?? null;

        $isValide = in_array(strtolower($statut), ['valide', 'validé'], true);
        $titre = $isValide ? 'Versement validé ✅' : 'Versement rejeté ⚠️';
        $actionText = $isValide ? 'validé' : 'rejeté';

        return self::send([
            'user_code'            => $commercialCode, // Directement adressé au commercial
            'role_target'          => null,
            'type_notification'    => 'versement',
            'titre_notification'   => $titre,
            'message_notification' => "Votre versement de {$montantFmt} FCFA ({$refCode}) a été {$actionText} par la comptabilité.",
            'reference_code'       => $refCode,
            'url_notification'     => RACINE . 'versement/list',
            'lu_notification'      => 0,
            'statut_notification'  => 'actif',
            'etablissement_code'   => $etabCode,
            'zone_code'            => $zoneCode,
            'annee_code'           => $anneeCode
        ]);
    }

    /**
     * Notification d'alerte système lorsqu'aucune année d'activité n'est configurée ou active
     */
    public static function notifyAnneeNonActive(array $params = []): ?string
    {
        $etabCode = !empty($params['etablissement_code']) ? $params['etablissement_code'] : (self::getModel()->getCon()->query("SELECT code_etablissement FROM etablissements WHERE statut_etablissement = 'actif' LIMIT 1")->fetchColumn() ?: null);
        $zoneCode = $params['zone_code'] ?? null;
        $userCode = $params['user_code'] ?? null;
        $blockedUserNom = !empty($params['blocked_user_nom']) ? $params['blocked_user_nom'] : null;

        $titre = "Configuration requise : Aucune année active ⚠️";
        if (!empty($blockedUserNom)) {
            $message = "Attention : Une tentative de connexion de {$blockedUserNom} a été bloquée car aucune année académique / d'activité n'est active. Veuillez activer ou créer une année.";
        } else {
            $message = "Attention : Aucune année académique / d'activité n'est actuellement activée dans le système. Veuillez vous rendre dans le module Années pour activer ou créer une année afin de débloquer l'accès pour l'ensemble des utilisateurs.";
        }

        return self::send([
            'user_code'            => $userCode,
            'role_target'          => 'ROLE_ADMIN',
            'type_notification'    => 'systeme',
            'titre_notification'   => $titre,
            'message_notification' => $message,
            'reference_code'       => 'ANNEE_INACTIVE',
            'url_notification'     => RACINE . 'annee/list',
            'lu_notification'      => 0,
            'statut_notification'  => 'actif',
            'etablissement_code'   => $etabCode,
            'zone_code'            => $zoneCode,
            'annee_code'           => null
        ]);
    }

    /**
     * Marquer comme lues et résolues les alertes d'année inactive lorsqu'une année est activée
     */
    public static function resolveAnneeNonActive(?string $etabCode = null): bool
    {
        try {
            $pdo = self::getModel()->getCon();
            $sql = "UPDATE notifications SET lu_notification = 1 WHERE reference_code = 'ANNEE_INACTIVE' AND lu_notification = 0";
            $params = [];
            if (!empty($etabCode)) {
                $sql .= " AND (etablissement_code = ? OR etablissement_code IS NULL)";
                $params[] = $etabCode;
            }
            $stmt = $pdo->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("resolveAnneeNonActive error: " . $e->getMessage());
            return false;
        }
    }
}
