<?php

class ModelAbonnementPressing extends BaseModel
{
    protected string $table = 'abonnements_pressings';
    protected string $primaryKey = 'id_abonnement_pressing';
    protected ?string $statusField = 'statut_abonnement_pressing';
    protected ?string $createdAtField = 'created_at_abonnement_pressing';

    public function getAllWithDetails(?string $pressingCode = null): array
    {
        try {
            $sql = "
                SELECT 
                    ap.*, 
                    COALESCE(p.libelle_pressing, ap.pressing_code) as libelle_pressing,
                    COALESCE(f.libelle_forfait, ap.forfait_code) as libelle_forfait,
                    COALESCE(ap.montant_abonnement, f.montant_forfait, 0) as montant_reel,
                    DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants
                FROM {$this->table} ap
                LEFT JOIN " . TABLES::PRESSINGS . " p ON ap.pressing_code = p.code_pressing
                LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
            ";

            if ($pressingCode !== null) {
                $sql .= " WHERE ap.pressing_code = ? ORDER BY ap.id_abonnement_pressing DESC";
                $stmt = $this->getCon()->prepare($sql);
                $stmt->execute([$pressingCode]);
                return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
            }

            $sql .= " ORDER BY ap.id_abonnement_pressing DESC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelAbonnementPressing::getAllWithDetails] ' . $e->getMessage());
            return [];
        }
    }
}
