<?php

class ModelMission extends BaseModel
{
    protected string $table = 'missions';
    protected string $primaryKey = 'id_mission';
    protected ?string $statusField = 'statut_mission';
    protected ?string $createdAtField = 'created_at_mission';

    public function getByLivreur(string $livreurCode): array
    {
        try {
            $sql = "
                SELECT 
                    m.*, 
                    c.code_commande, 
                    c.montant_total_commande, 
                    c.statut_suivi_commande,
                    c.type_commande,
                    c.nb_sacs_colis,
                    COALESCE(cl.nom_client, 'Client') as nom_client,
                    COALESCE(cl.telephone_client, '-') as telephone_client,
                    COALESCE(cl.adresse_client, m.adresse_mission) as adresse_client,
                    COALESCE(p.libelle_pressing, 'Pressing') as libelle_pressing,
                    COALESCE(p.adresse_pressing, '') as adresse_pressing,
                    COALESCE(p.telephone_pressing, '') as telephone_pressing
                FROM {$this->table} m
                JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                LEFT JOIN " . TABLES::PRESSINGS . " p ON c.pressing_code = p.code_pressing
                WHERE m.livreur_code = ?
                ORDER BY m.id_mission DESC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$livreurCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelMission::getByLivreur] ' . $e->getMessage());
            return [];
        }
    }

    public function getWithDetails(int $id): array
    {
        try {
            $sql = "
                SELECT 
                    m.*, 
                    c.code_commande, 
                    c.montant_total_commande, 
                    c.statut_suivi_commande,
                    c.type_commande,
                    c.nb_sacs_colis,
                    c.observation_commande,
                    COALESCE(cl.nom_client, 'Client') as nom_client,
                    COALESCE(cl.telephone_client, '-') as telephone_client,
                    COALESCE(cl.adresse_client, m.adresse_mission) as adresse_client,
                    COALESCE(p.libelle_pressing, 'Pressing') as libelle_pressing,
                    COALESCE(p.adresse_pressing, '') as adresse_pressing,
                    COALESCE(p.telephone_pressing, '') as telephone_pressing,
                    COALESCE(l.nom_livreur, 'Livreur') as nom_livreur,
                    COALESCE(l.telephone_livreur, '-') as telephone_livreur
                FROM {$this->table} m
                JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                LEFT JOIN " . TABLES::PRESSINGS . " p ON c.pressing_code = p.code_pressing
                LEFT JOIN " . TABLES::LIVREURS . " l ON m.livreur_code = l.code_livreur
                WHERE m.{$this->primaryKey} = ?
                LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelMission::getWithDetails] ' . $e->getMessage());
            return [];
        }
    }
}
