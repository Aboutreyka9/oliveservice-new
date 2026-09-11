<?php

class ModelCotisation extends BaseModel
{
    protected string $table = 'cautisation_clients';
    protected string $primaryKey = 'id_cautisation_client';
    protected ?string $statusField = 'statut_cautisation_client';
    protected ?string $createdAtField = 'created_at_cautisation_client';

    public function getAllWithDetails(): array
    {
        try {
            $sql = "
                SELECT c.*, 
                       cli.nom_client, cli.telephone_client,
                       s.code_souscription, s.montant_cotisation_journaliere, s.statut_souscription,
                       (SELECT p.libelle_pack FROM pack_souscriptions ps JOIN packs p ON p.code_pack = ps.pack_code WHERE ps.souscription_code = c.souscription_code LIMIT 1) as libelle_pack,
                       u.nom_user as nom_commercial, u.prenom_user as prenom_commercial
                FROM cautisation_clients c
                LEFT JOIN clients cli ON cli.code_client = c.client_code
                LEFT JOIN souscriptions s ON s.code_souscription = c.souscription_code
                LEFT JOIN users u ON u.code_user = c.commercial_code
                WHERE 1=1
            ";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('c', $conds, $params, true, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY c.date_cautisation DESC, c.id_cautisation_client DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelCotisation::getAllWithDetails error: " . $e->getMessage());
            return [];
        }
    }

    public function getBySouscription(string $souscriptionCode): array
    {
        try {
            $sql = "
                SELECT c.*, 
                       u.nom_user as nom_commercial
                FROM cautisation_clients c
                LEFT JOIN users u ON u.code_user = c.commercial_code
                WHERE c.souscription_code = ?
            ";
            $params = [$souscriptionCode];
            $conds = [];
            Context::applyTripleFilter('c', $conds, $params);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY c.date_cautisation DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelCotisation::getBySouscription error: " . $e->getMessage());
            return [];
        }
    }

    public function createCotisation(array $data): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $data['etablissement_code'] = $data['etablissement_code'] ?? Context::etablissement();
            $data['zone_code'] = $data['zone_code'] ?? Context::zone();
            $data['annee_code'] = $data['annee_code'] ?? Context::annee();
            $data['user_code'] = $data['user_code'] ?? Context::user();

            if (empty($data['etablissement_code']) || empty($data['zone_code']) || empty($data['annee_code']) || empty($data['user_code'])) {
                throw new Exception("Champs obligatoires manquants pour l'insertion de la cotisation (zone, année, établissement ou utilisateur null).");
            }

            $cols = $this->getCon()->query("DESCRIBE cautisation_clients")->fetchAll(PDO::FETCH_COLUMN);
            $filteredData = array_intersect_key($data, array_flip($cols));

            $colsStr = implode(',', array_keys($filteredData));
            $paramsStr = implode(',', array_fill(0, count($filteredData), '?'));

            $stmt = $this->getCon()->prepare("INSERT INTO cautisation_clients ({$colsStr}) VALUES ({$paramsStr})");
            $stmt->execute(array_values($filteredData));

            $souscriptionCode = $data['souscription_code'];
            $montant = (float)($data['montant_cautisation_client'] ?? $data['montant_cautisation'] ?? 0);
            $nbJours = (int)($data['nombre_jour'] ?? $data['nombre_jour_paye'] ?? 1);

            if ($souscriptionCode) {
                $stmtUpd = $this->getCon()->prepare("
                    UPDATE souscriptions s
                    SET s.montant_total_cotise = (
                            SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) 
                            FROM cautisation_clients mc 
                            WHERE mc.souscription_code = s.code_souscription 
                              AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)
                        ),
                        s.nombre_jour_cotise = (
                            SELECT COALESCE(SUM(mc.nombre_jour), 0) 
                            FROM cautisation_clients mc 
                            WHERE mc.souscription_code = s.code_souscription 
                              AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)
                        ),
                        s.statut_souscription = CASE 
                            WHEN (
                                SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) 
                                FROM cautisation_clients mc 
                                WHERE mc.souscription_code = s.code_souscription 
                                  AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)
                            ) >= s.montant_total_prevu 
                            OR (
                                SELECT COALESCE(SUM(mc.nombre_jour), 0) 
                                FROM cautisation_clients mc 
                                WHERE mc.souscription_code = s.code_souscription 
                                  AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)
                            ) >= s.nombre_jour_total 
                            THEN 'solde' 
                            ELSE 'valide'
                        END,
                        s.updated_at_souscription = NOW()
                    WHERE s.code_souscription = ?
                ");
                $stmtUpd->execute([$souscriptionCode]);
            }

            $this->getCon()->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelCotisation::createCotisation error: " . $e->getMessage());
            return false;
        }
    }
}
