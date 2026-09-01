<?php

class ModelCommande extends BaseModel
{
    protected string $table = 'commandes';
    protected string $primaryKey = 'id_commande';
    protected ?string $statusField = 'statut_commande';
    protected ?string $createdAtField = 'created_at_commande';

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllWithDetails(): array
    {
        try {
            $sql = "SELECT cmd.*, cl.nom_client, cl.telephone_client, cl.quartier_client, cl.adresse_client,
                           p.libelle_pressing, p.telephone_pressing
                    FROM {$this->table} cmd
                    LEFT JOIN clients cl ON cl.code_client = cmd.client_code
                    LEFT JOIN pressings p ON p.code_pressing = cmd.pressing_code
                    ORDER BY cmd.created_at_commande DESC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getAllWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    public function getByPressingWithDetails(string $pressingCode): array
    {
        try {
            $sql = "SELECT cmd.*, cl.nom_client, cl.telephone_client, cl.quartier_client, cl.adresse_client,
                           p.libelle_pressing, p.telephone_pressing
                    FROM {$this->table} cmd
                    LEFT JOIN clients cl ON cl.code_client = cmd.client_code
                    LEFT JOIN pressings p ON p.code_pressing = cmd.pressing_code
                    WHERE cmd.pressing_code = ?
                    ORDER BY cmd.created_at_commande DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getByPressingWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    public function getWithDetails(int $id): array
    {
        try {
            $sql = "SELECT cmd.*, cl.nom_client, cl.telephone_client, cl.quartier_client, cl.adresse_client,
                           p.libelle_pressing, p.telephone_pressing, p.adresse_pressing
                    FROM {$this->table} cmd
                    LEFT JOIN clients cl ON cl.code_client = cmd.client_code
                    LEFT JOIN pressings p ON p.code_pressing = cmd.pressing_code
                    WHERE cmd.{$this->primaryKey} = ?";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    public function getByCodeWithDetails(string $code): array
    {
        try {
            $sql = "SELECT cmd.*, cl.nom_client, cl.telephone_client, cl.quartier_client, cl.adresse_client,
                           p.libelle_pressing, p.telephone_pressing, p.adresse_pressing
                    FROM {$this->table} cmd
                    LEFT JOIN clients cl ON cl.code_client = cmd.client_code
                    LEFT JOIN pressings p ON p.code_pressing = cmd.pressing_code
                    WHERE cmd.code_commande = ? LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$code]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getByCodeWithDetails] ' . $e->getMessage());
            return [];
        }
    }

    public function getByClient(string $clientCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE client_code = ? ORDER BY created_at_commande DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$clientCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommande::getByClient] ' . $e->getMessage());
            return [];
        }
    }

    public function getByPressing(string $pressingCode): array
    {
        return $this->getByPressingWithDetails($pressingCode);
    }
}
