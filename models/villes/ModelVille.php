<?php

class ModelVille extends BaseModel
{
    protected string $table = 'villes';
    protected string $primaryKey = 'id_ville';
    protected ?string $statusField = 'statut_ville';
    protected ?string $createdAtField = 'created_at_ville';

    /**
     * Récupère toutes les villes avec le décompte de leurs quartiers
     */
    public function getAllWithQuartiersCount(): array
    {
        try {
            $sql = "SELECT v.*, COUNT(q.id_quartier) AS total_quartiers
                    FROM " . TABLES::VILLES . " v
                    LEFT JOIN " . TABLES::QUARTIERS . " q ON q.ville_code = v.code_ville
                    GROUP BY v.id_ville
                    ORDER BY v.libelle_ville ASC";
            $stmt = $this->getCon()->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return $this->getAll();
        }
    }

    /**
     * Récupère la liste des quartiers pour un code ville donné
     */
    public function getQuartiersByVille(string $villeCode): array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::QUARTIERS . " WHERE ville_code = ? ORDER BY libelle_quartier ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$villeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}
