<?php

class ModelCaisse extends BaseModel
{
    protected string $table = 'caisses';
    protected string $primaryKey = 'id_caisse';
    protected ?string $statusField = 'decission_caisse';
    protected ?string $createdAtField = 'created_at_caisse';

    /**
     * Récupérer la session de caisse ouverte aujourd'hui pour un utilisateur
     */
    public function getActiveOuvertureForToday(string $userCode, ?string $date = null)
    {
        if (!$date) $date = date('Y-m-d');
        $sql = "SELECT * FROM caisses WHERE user_code = ? AND DATE(date_ouverture) = ? AND statut_caisse = 'ouverte'";
        $params = [$userCode, $date];
        $conds = [];
        Context::applyTripleFilter('', $conds, $params, false);
        if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
        $sql .= " ORDER BY id_caisse DESC LIMIT 1";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la session de caisse actuellement ouverte pour un utilisateur
     */
    public function getCurrentCaisse(string $userCode)
    {
        $sql = "SELECT * FROM caisses WHERE user_code = ? AND statut_caisse = 'ouverte'";
        $params = [$userCode];
        $conds = [];
        Context::applyTripleFilter('', $conds, $params, false);
        if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
        $sql .= " ORDER BY id_caisse DESC LIMIT 1";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Récupérer la dernière caisse clôturée pour un utilisateur
     */
    public function getLastClosedCaisse(string $userCode)
    {
        $sql = "SELECT * FROM caisses WHERE user_code = ? AND statut_caisse = 'cloture'";
        $params = [$userCode];
        $conds = [];
        Context::applyTripleFilter('', $conds, $params, false);
        if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
        $sql .= " ORDER BY id_caisse DESC LIMIT 1";

        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
