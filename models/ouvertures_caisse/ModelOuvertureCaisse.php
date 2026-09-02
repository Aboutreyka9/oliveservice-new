<?php

class ModelOuvertureCaisse extends BaseModel
{
    protected string $table = 'caisses';
    protected string $primaryKey = 'id_caisse';

    public function getActiveOuvertureForToday(string $userCode, string $date = null)
    {
        if (!$date) $date = date('Y-m-d');
        $stmt = $this->getCon()->prepare("
            SELECT * FROM caisses 
            WHERE user_code = ? AND DATE(date_ouverture) = ? AND statut_caisse = 'ouverte' 
            ORDER BY id_caisse DESC LIMIT 1
        ");
        $stmt->execute([$userCode, $date]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
