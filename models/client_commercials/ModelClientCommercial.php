<?php

class ModelClientCommercial extends BaseModel
{
    protected string $table = 'client_commercials';
    protected string $primaryKey = 'id_client_commercial';
    protected ?string $statusField = 'statut_client_commercial';
    protected ?string $createdAtField = 'created_at_client_commercials';
    protected ?string $updatedAtField = 'updated_at_client_commercials';

    /**
     * Récupère le commercial actuellement actif pour un client donné
     */
    public function getActiveCommercial(string $clientCode): ?array
    {
        try {
            $sql = "
                SELECT cc.*, 
                       u.nom_user, u.prenom_user, u.telephone_user, u.email_user, u.matricule_user, u.commission,
                       z.libelle_zone
                FROM client_commercials cc
                LEFT JOIN users u ON u.code_user = cc.commercial_code
                LEFT JOIN zones z ON z.code_zone = cc.zone_code
                WHERE cc.client_code = ? AND cc.statut_client_commercial = 'actif'
                ORDER BY cc.id_client_commercial DESC
                LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$clientCode]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelClientCommercial::getActiveCommercial error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Récupère l'historique complet des affectations pour un client
     */
    public function getHistoryByClient(string $clientCode): array
    {
        try {
            $sql = "
                SELECT cc.*, 
                       u.nom_user as nom_commercial, u.prenom_user as prenom_commercial, 
                       u.telephone_user as telephone_commercial, u.matricule_user,
                       ua.nom_user as nom_admin, ua.prenom_user as prenom_admin,
                       z.libelle_zone
                FROM client_commercials cc
                LEFT JOIN users u ON u.code_user = cc.commercial_code
                LEFT JOIN users ua ON ua.code_user = cc.user_code
                LEFT JOIN zones z ON z.code_zone = cc.zone_code
                WHERE cc.client_code = ?
                ORDER BY cc.id_client_commercial DESC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$clientCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelClientCommercial::getHistoryByClient error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Affecte ou transfère un client à un nouveau commercial
     * Clôture l'ancienne affectation active (statut -> inactif, updated_at -> NOW())
     * et crée la nouvelle entrée active
     */
    public function assignClient(string $clientCode, string $newCommercialCode, string $adminUserCode, string $etabCode, ?string $zoneCode = null): bool
    {
        $pdo = $this->getCon();
        $shouldCommit = false;
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $shouldCommit = true;
        }

        try {
            // Vérifier la zone du client si non fournie
            if (empty($zoneCode)) {
                $stmtZ = $pdo->prepare("SELECT zone_code FROM clients WHERE code_client = ? LIMIT 1");
                $stmtZ->execute([$clientCode]);
                $zoneCode = $stmtZ->fetchColumn() ?: Context::zone();
            }

            // 1. Clôturer toute affectation active existante pour ce client
            $stmtClose = $pdo->prepare("
                UPDATE client_commercials 
                SET statut_client_commercial = 'inactif', 
                    updated_at_client_commercials = NOW() 
                WHERE client_code = ? AND statut_client_commercial = 'actif'
            ");
            $stmtClose->execute([$clientCode]);

            // 2. Insérer la nouvelle affectation active
            $stmtInsert = $pdo->prepare("
                INSERT INTO client_commercials 
                (commercial_code, client_code, user_code, statut_client_commercial, etablissement_code, zone_code, created_at_client_commercials, updated_at_client_commercials)
                VALUES (?, ?, ?, 'actif', ?, ?, NOW(), NULL)
            ");
            $stmtInsert->execute([
                $newCommercialCode,
                $clientCode,
                $adminUserCode,
                $etabCode,
                $zoneCode
            ]);

            // 3. Mettre à jour le pointeur user_code dans clients pour compatibilité descendante
            $stmtUpdClient = $pdo->prepare("
                UPDATE clients 
                SET user_code = ?, updated_at_client = NOW() 
                WHERE code_client = ?
            ");
            $stmtUpdClient->execute([$newCommercialCode, $clientCode]);

            if ($shouldCommit) {
                $pdo->commit();
            }
            return true;
        } catch (Exception $e) {
            if ($shouldCommit && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("ModelClientCommercial::assignClient error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Affectation en masse de plusieurs clients à un commercial
     */
    public function assignMultipleClients(array $clientCodes, string $newCommercialCode, string $adminUserCode, string $etabCode): array
    {
        $succes = 0;
        $echecs = 0;

        foreach ($clientCodes as $cCode) {
            $cCode = trim((string)$cCode);
            if (!empty($cCode)) {
                if ($this->assignClient($cCode, $newCommercialCode, $adminUserCode, $etabCode)) {
                    $succes++;
                } else {
                    $echecs++;
                }
            }
        }

        return [
            'total' => count($clientCodes),
            'succes' => $succes,
            'echecs' => $echecs
        ];
    }

    /**
     * Synchronise les clients existants qui n'ont pas encore d'entrée dans client_commercials
     * (Initialisation transparente sans altération de table)
     */
    public function initMissingActiveAssignments(string $etabCode): void
    {
        try {
            $sql = "
                INSERT INTO client_commercials 
                    (commercial_code, client_code, user_code, statut_client_commercial, etablissement_code, zone_code, created_at_client_commercials)
                SELECT c.user_code, c.code_client, c.user_code, 'actif', c.etablissement_code, c.zone_code, c.created_at_client
                FROM clients c
                INNER JOIN user_roles ur ON ur.user_code = c.user_code AND ur.role_code = 'ROLE_COMMERCIAL'
                LEFT JOIN client_commercials cc ON cc.client_code = c.code_client
                WHERE c.etablissement_code = ? AND cc.id_client_commercial IS NULL
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$etabCode]);
        } catch (Exception $e) {
            error_log("ModelClientCommercial::initMissingActiveAssignments error: " . $e->getMessage());
        }
    }
}
