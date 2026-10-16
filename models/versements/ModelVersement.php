<?php

class ModelVersement extends BaseModel
{
    protected string $table = 'versements_commerciaux';
    protected string $primaryKey = 'id_versement';
    protected ?string $statusField = 'statut_versement';
    protected ?string $createdAtField = 'created_at_versement';

    public function getAllWithDetails(): array
    {
        try {
            $sql = "
                SELECT v.*, 
                       u.nom_user as nom_commercial, u.prenom_user as prenom_commercial,
                       val.nom_user as nom_validator, val.prenom_user as prenom_validator,
                       z.libelle_zone
                FROM versements_commerciaux v
                LEFT JOIN users u ON u.code_user = v.commercial_code
                LEFT JOIN users val ON val.code_user = v.user_validate
                LEFT JOIN zones z ON z.code_zone = v.zone_code
                WHERE 1=1
            ";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('v', $conds, $params, true, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY v.created_at_versement DESC, v.id_versement DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelVersement::getAllWithDetails error: " . $e->getMessage());
            return [];
        }
    }

    public function validateVersement(int $id, string $userValidateCode, string $commentaire = '', string $statut = 'valide'): bool
    {
        try {
            $sql = "UPDATE versements_commerciaux 
                    SET statut_versement = ?, 
                        user_validate = ?, 
                        date_validation = NOW(), 
                        commentaire_validation = ?
                    WHERE id_versement = ?";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$statut, $userValidateCode, $commentaire, $id]);
        } catch (Exception $e) {
            error_log("ModelVersement::validateVersement error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère la liste des commerciaux actifs dans le périmètre de l'établissement/zone
     */
    public function getCommerciauxList(): array
    {
        try {
            $sql = "
                SELECT DISTINCT u.code_user, u.nom_user, u.prenom_user, u.matricule_user
                FROM users u
                LEFT JOIN user_roles ur ON ur.user_code = u.code_user
                WHERE (
                    ur.role_code = 'ROLE_COMMERCIAL'
                    OR u.code_user IN (
                        SELECT DISTINCT commercial_code 
                        FROM versements_commerciaux 
                        WHERE commercial_code IS NOT NULL AND commercial_code != ''
                    )
                )
                AND u.statut_user = 'actif'
            ";
            $params = [];

            $etab = Context::etablissement();
            if (!empty($etab)) {
                $sql .= " AND (u.etablissement_code = ? OR u.etablissement_code IS NULL OR u.etablissement_code = '')";
                $params[] = $etab;
            }

            if (Context::isGestionnaire() && !Context::hasJoker()) {
                $zone = Context::zone();
                if (!empty($zone)) {
                    $sql .= " AND (u.zone_code = ? OR u.zone_code IS NULL OR u.zone_code = '')";
                    $params[] = $zone;
                }
            }

            $sql .= " ORDER BY u.nom_user ASC, u.prenom_user ASC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelVersement::getCommerciauxList error: " . $e->getMessage());
            return [];
        }
    }
}
