<?php

class ModelLivreur extends BaseModel
{
    protected string $table = 'livreurs';
    protected string $primaryKey = 'id_livreur';
    protected ?string $statusField = 'statut_livreur';
    protected ?string $createdAtField = 'created_at_livreur';

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE pressing_code = ? ORDER BY {$this->primaryKey} DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelLivreur::getByPressing] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Met à jour la position GPS en temps réel d'un livreur
     */
    public function updatePosition(string $livreurCode, float $lat, float $lng): bool
    {
        try {
            $sql = "UPDATE {$this->table} SET latitude_actuelle = ?, longitude_actuelle = ?, derniere_maj_gps = NOW(), en_ligne_gps = 1 WHERE code_livreur = ?";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$lat, $lng, $livreurCode]);
        } catch (Exception $e) {
            error_log('[ModelLivreur::updatePosition] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les positions GPS en direct des livreurs connectés
     */
    public function getLivePositions(?string $pressingCode = null): array
    {
        try {
            $sql = "
                SELECT 
                    l.id_livreur,
                    l.code_livreur,
                    l.nom_livreur,
                    l.prenom_livreur,
                    l.telephone_livreur,
                    l.latitude_actuelle,
                    l.longitude_actuelle,
                    l.derniere_maj_gps,
                    l.en_ligne_gps,
                    TIMESTAMPDIFF(SECOND, l.derniere_maj_gps, NOW()) as secondes_depuis_derniere_maj,
                    m.code_mission,
                    m.type_mission,
                    m.statut_mission,
                    c.code_commande,
                    cl.nom_client,
                    cl.telephone_client,
                    cl.adresse_client
                FROM {$this->table} l
                LEFT JOIN missions m ON m.livreur_code = l.code_livreur AND m.statut_mission = 'en_cours'
                LEFT JOIN commandes c ON m.commande_code = c.code_commande
                LEFT JOIN clients cl ON c.client_code = cl.code_client
                WHERE l.statut_livreur = 'actif' AND l.latitude_actuelle IS NOT NULL AND l.longitude_actuelle IS NOT NULL
            ";

            if ($pressingCode !== null && $pressingCode !== '') {
                $sql .= " AND (l.pressing_code = ? OR l.pressing_code IS NULL OR l.pressing_code = '')";
                $stmt = $this->getCon()->prepare($sql);
                $stmt->execute([$pressingCode]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelLivreur::getLivePositions] ' . $e->getMessage());
            return [];
        }
    }
}
