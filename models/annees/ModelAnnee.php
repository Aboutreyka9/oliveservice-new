<?php

class ModelAnnee extends BaseModel
{
    protected string $table = 'annees';
    protected string $primaryKey = 'id_annee';
    protected ?string $statusField = 'statut_annee';
    protected ?string $createdAtField = 'created_at_annee';

    public function getAllWithDetails(): array
    {
        try {
            $sql = "
                SELECT 
                    a.*,
                    z.libelle_zone,
                    e.libelle_etablissement,
                    u.nom_user,
                    u.prenom_user,
                    (SELECT COUNT(*) FROM souscriptions s WHERE s.annee_code = a.code_annee AND s.statut_souscription != 'annule') as nb_souscriptions,
                    (SELECT COUNT(*) FROM sessions sess WHERE sess.annee_code = a.code_annee) as nb_sessions
                FROM annees a
                LEFT JOIN zones z ON z.code_zone = a.zone_code
                LEFT JOIN etablissements e ON e.code_etablissement = a.etablissement_code
                LEFT JOIN users u ON u.code_user = a.user_code
                WHERE 1=1
            ";
            $params = [];
            $etab = Context::etablissement();
            if (!empty($etab)) {
                $sql .= " AND a.etablissement_code = ?";
                $params[] = $etab;
            }
            $sql .= " ORDER BY a.id_annee DESC";
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\Exception $e) {
            error_log("ModelAnnee::getAllWithDetails error: " . $e->getMessage());
            return $this->getAll();
        }
    }

    public function updateStatut(int $id, string $newStatut): bool
    {
        try {
            if (!in_array($newStatut, ['planifie', 'actif', 'cloture'], true)) {
                return false;
            }
            $item = $this->getById($id);
            if (!$item) return false;

            if ($newStatut === 'actif' && !empty($item['etablissement_code'])) {
                $stmtClose = $this->pdo->getCon()->prepare("
                    UPDATE annees SET statut_annee = 'cloture', updated_at_annee = NOW() WHERE etablissement_code = ? AND id_annee != ? AND statut_annee = 'actif'
                ");
                $stmtClose->execute([$item['etablissement_code'], $id]);
            }

            $sql = "UPDATE annees SET statut_annee = ?, updated_at_annee = NOW() WHERE id_annee = ?";
            return $this->pdo->getCon()->prepare($sql)->execute([$newStatut, $id]);
        } catch (\Exception $e) {
            error_log("ModelAnnee::updateStatut error: " . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus(int $id, ?string $targetStatus = null): bool
    {
        if ($targetStatus && in_array($targetStatus, ['planifie', 'actif', 'cloture'], true)) {
            return $this->updateStatut($id, $targetStatus);
        }
        $item = $this->getById($id);
        if (!$item) return false;
        $newStatus = ($item['statut_annee'] === 'actif') ? 'cloture' : 'actif';
        return $this->updateStatut($id, $newStatus);
    }
}
