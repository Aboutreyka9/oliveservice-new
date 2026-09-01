<?php

class ModelForfait extends BaseModel
{
    protected string $table = 'forfaits';
    protected string $primaryKey = 'id_forfait';
    protected ?string $statusField = 'statut_forfait';
    protected ?string $createdAtField = 'created_at_forfait';

    public function getAvantagesByCode(string $codeForfait): array
    {
        $db = $this->getCon();
        $stmt = $db->prepare("SELECT libelle_avantage FROM forfaits_avantages WHERE code_forfait = ? AND statut_avantage = 'actif' ORDER BY ordre_affichage ASC, id_avantage ASC");
        $stmt->execute([$codeForfait]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
    }

    public function syncAvantages(string $codeForfait, array $advantages): bool
    {
        try {
            $db = $this->getCon();
            $stmtDel = $db->prepare("DELETE FROM forfaits_avantages WHERE code_forfait = ?");
            $stmtDel->execute([$codeForfait]);

            if (!empty($advantages)) {
                $stmtIns = $db->prepare("INSERT INTO forfaits_avantages (code_forfait, libelle_avantage, ordre_affichage, statut_avantage) VALUES (?, ?, ?, 'actif')");
                foreach ($advantages as $idx => $libelle) {
                    $lib = trim($libelle);
                    if (!empty($lib)) {
                        $stmtIns->execute([$codeForfait, $lib, $idx + 1]);
                    }
                }
            }
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
