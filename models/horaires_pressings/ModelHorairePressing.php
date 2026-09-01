<?php

class ModelHorairePressing extends BaseModel
{
    protected string $table = 'horaires_pressings';
    protected string $primaryKey = 'id_horaire';
    protected ?string $createdAtField = 'created_at';

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT h.*, COALESCE(p.libelle_pressing, h.pressing_code) as libelle_pressing 
                FROM {$this->table} h
                LEFT JOIN " . TABLES::PRESSINGS . " p ON h.pressing_code = p.code_pressing
                WHERE h.pressing_code = ? 
                ORDER BY FIELD(h.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelHorairePressing::getByPressing] ' . $e->getMessage());
            return [];
        }
    }

    public function getAllWithPressing(): array
    {
        try {
            $sql = "
                SELECT h.*, COALESCE(p.libelle_pressing, h.pressing_code) as libelle_pressing 
                FROM {$this->table} h
                LEFT JOIN " . TABLES::PRESSINGS . " p ON h.pressing_code = p.code_pressing
                ORDER BY p.libelle_pressing ASC, FIELD(h.jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')
            ";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelHorairePressing::getAllWithPressing] ' . $e->getMessage());
            return [];
        }
    }
}
