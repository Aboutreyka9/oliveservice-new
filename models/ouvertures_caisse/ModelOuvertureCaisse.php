<?php

class ModelOuvertureCaisse extends BaseModel
{
    protected string $table = 'ouvertures_caisse';
    protected string $primaryKey = 'id_ouverture';

    public function getActiveOuvertureForToday(string $date = null)
    {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->getCon()->prepare("
            SELECT * FROM ouvertures_caisse 
            WHERE date_ouverture = ? AND statut_ouverture = 'ouverte' 
            ORDER BY id_ouverture DESC LIMIT 1
        ");
        $stmt->execute([$date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
