<?php

class ModelSouscription extends BaseModel
{
    protected string $table = 'souscriptions';
    protected string $primaryKey = 'id_souscription';
    protected ?string $statusField = 'statut_souscription';
    protected ?string $createdAtField = 'created_at_souscription';

    public function getAllWithDetails(?string $userCode = null, ?string $zoneCode = null, ?string $anneeCode = null, ?string $etabCode = null): array
    {
        try {
            $sql = "
                SELECT s.*, 
                       c.nom_client, c.telephone_client, c.sexe_client,
                       z.libelle_zone,
                       sess.libelle_session,
                       sess.nombre_jour_session,
                       (SELECT GROUP_CONCAT(DISTINCT p2.libelle_pack SEPARATOR ', ') FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as libelle_pack,
                       (SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as sum_prix_cotisation_pack,
                       ((SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) * COALESCE(sess.nombre_jour_session, 0)) as totale_souscription,
                       (SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as montant_total_cotise,
                       (SELECT COALESCE(SUM(mc.nombre_jour), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as nombre_jour_cotise,
                       sess.nombre_jour_session as nombre_jour_total
                 FROM souscriptions s
                 LEFT JOIN clients c ON c.code_client = s.client_code
                 LEFT JOIN zones z ON z.code_zone = s.zone_code
                 LEFT JOIN sessions sess ON sess.code_session = s.session_code
                 WHERE 1=1
            ";
            $params = [];

            $etab = $etabCode ?: Context::etablissement();
            if (!empty($etab)) {
                $sql .= " AND s.etablissement_code = ?";
                $params[] = $etab;
            }

            if (!empty($zoneCode)) {
                $sql .= " AND s.zone_code = ?";
                $params[] = $zoneCode;
            }

            $annee = $anneeCode ?: Context::annee();
            if (!empty($annee)) {
                $sql .= " AND s.annee_code = ?";
                $params[] = $annee;
            }

            if (!empty($userCode)) {
                $sql .= " AND s.user_code = ?";
                $params[] = $userCode;
            }

            $sql .= " ORDER BY s.created_at_souscription DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSouscription::getAllWithDetails error: " . $e->getMessage());
            return [];
        }
    }

    public function getPackSouscrit(string $souscriptionCode): ?array
    {
        try {
            $sql = "
                SELECT ps.*, p.libelle_pack, p.prix_cotisation_pack
                FROM pack_souscriptions ps
                LEFT JOIN packs p ON p.code_pack = ps.pack_code
                WHERE ps.souscription_code = ?
            ";
            $params = [$souscriptionCode];
            $conds = [];
            Context::applyTripleFilter('ps', $conds, $params);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " LIMIT 1";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelSouscription::getPackSouscrit error: " . $e->getMessage());
            return null;
        }
    }

    public function getByIdWithDetails(int $id): ?array
    {
        try {
            $sql = "
                SELECT s.*, 
                       c.nom_client, c.telephone_client, c.sexe_client, c.lieu_residence_client, c.email_client, c.profession_client,
                       z.libelle_zone,
                       sess.libelle_session,
                       sess.nombre_jour_session,
                       (SELECT GROUP_CONCAT(DISTINCT p2.libelle_pack SEPARATOR ', ') FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as libelle_pack,
                       (SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) as sum_prix_cotisation_pack,
                       ((SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) * COALESCE(sess.nombre_jour_session, 0)) as totale_souscription,
                       (SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as montant_total_cotise,
                       (SELECT COALESCE(SUM(mc.nombre_jour), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as nombre_jour_cotise,
                       sess.nombre_jour_session as nombre_jour_total
                 FROM souscriptions s
                 LEFT JOIN clients c ON c.code_client = s.client_code
                 LEFT JOIN zones z ON z.code_zone = s.zone_code
                 LEFT JOIN sessions sess ON sess.code_session = s.session_code
                 WHERE s.id_souscription = ?
            ";
            $params = [$id];
            $conds = [];
            Context::applyTripleFilter('s', $conds, $params);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelSouscription::getByIdWithDetails error: " . $e->getMessage());
            return null;
        }
    }

    public function getPacksSouscrits(string $souscriptionCode): array
    {
        try {
            $sql = "
                SELECT p.code_pack, p.libelle_pack, p.prix_cotisation_pack, p.nombre_articles, cp.libelle_categorie_pack, sess.nombre_jour_session
                FROM pack_souscriptions ps
                JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN categorie_packs cp ON cp.code_categorie_pack = p.categorie_pack_code
                LEFT JOIN sessions sess ON sess.code_session = p.session_code
                WHERE ps.souscription_code = ?
            ";
            $params = [$souscriptionCode];
            $conds = [];
            Context::applyTripleFilter('ps', $conds, $params);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSouscription::getPacksSouscrits error: " . $e->getMessage());
            return [];
        }
    }

    public function getByCode(string $code): ?array
    {
        $row = $this->getByElement('code_souscription', $code);
        return $row ?: null;
    }

    public function getSouscriptionsEnCours(): array
    {
        try {
            $sql = "
                SELECT s.*, c.nom_client, c.telephone_client,
                       p.libelle_pack, p.prix_cotisation_pack,
                       z.libelle_zone
                FROM souscriptions s
                LEFT JOIN clients c ON c.code_client = s.client_code
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
                LEFT JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                WHERE s.statut_souscription IN ('valide', 'reconduite')
            ";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('s', $conds, $params, true);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY s.created_at_souscription DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSouscription::getSouscriptionsEnCours error: " . $e->getMessage());
            return [];
        }
    }

    public function getSouscriptionsSoldees(): array
    {
        try {
            $sql = "
                SELECT s.*, c.nom_client, c.telephone_client,
                       p.libelle_pack, p.prix_cotisation_pack,
                       z.libelle_zone
                FROM souscriptions s
                LEFT JOIN clients c ON c.code_client = s.client_code
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
                LEFT JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN zones z ON z.code_zone = s.zone_code
                WHERE s.statut_souscription = 'solde'
            ";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('s', $conds, $params, true);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY s.created_at_souscription DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelSouscription::getSouscriptionsSoldees error: " . $e->getMessage());
            return [];
        }
    }

    public function createSouscriptionWithPack(array $souscriptionData, string $packCode, int $quantite = 1, float $prixUnitaire = 0): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $cols = $this->getCon()->query("DESCRIBE souscriptions")->fetchAll(PDO::FETCH_COLUMN);
            $filteredData = array_intersect_key($souscriptionData, array_flip($cols));

            $colsStr = implode(',', array_keys($filteredData));
            $paramsStr = implode(',', array_fill(0, count($filteredData), '?'));

            $stmt = $this->getCon()->prepare("INSERT INTO souscriptions ({$colsStr}) VALUES ({$paramsStr})");
            $stmt->execute(array_values($filteredData));

            $anneeCode = $souscriptionData['annee_code'] ?? Context::annee();
            $etabCode = $souscriptionData['etablissement_code'] ?? Context::etablissement();
            $zoneCode = $souscriptionData['zone_code'] ?? Context::zone();
            $userCode = $souscriptionData['user_code'] ?? Context::user();

            if (empty($anneeCode) || empty($etabCode) || empty($zoneCode) || empty($userCode)) {
                throw new Exception("Champs obligatoires manquants pour l'insertion de la souscription (zone, année, établissement ou utilisateur null).");
            }

            $stmtPack = $this->getCon()->prepare("
                INSERT INTO pack_souscriptions (souscription_code, pack_code, annee_code, etablissement_code, created_at_pack_souscription, user_code, zone_code)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmtPack->execute([
                $souscriptionData['code_souscription'],
                $packCode,
                $anneeCode,
                $etabCode,
                date('Y-m-d H:i:s'),
                $userCode,
                $zoneCode
            ]);

            $this->getCon()->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelSouscription::createSouscriptionWithPack error: " . $e->getMessage());
            return false;
        }
    }

    public function updateTotals(string $souscriptionCode, float $montantAjoute = 0, int $joursAjoutes = 0): bool
    {
        try {
            $sql = "
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
            ";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute([$souscriptionCode]);
        } catch (Exception $e) {
            error_log("ModelSouscription::updateTotals error: " . $e->getMessage());
            return false;
        }
    }

    public function getSoldeRestant(string $souscriptionCode): float
    {
        try {
            $sql = "
                SELECT ((SELECT COALESCE(SUM(p2.prix_cotisation_pack), 0) FROM pack_souscriptions ps2 JOIN packs p2 ON p2.code_pack = ps2.pack_code WHERE ps2.souscription_code = s.code_souscription) * COALESCE(sess.nombre_jour_session, 0)) as totale_souscription,
                       (SELECT COALESCE(SUM(mc.montant_cautisation_client), 0) FROM cautisation_clients mc WHERE mc.souscription_code = s.code_souscription AND (mc.statut_cautisation_client != 'annule' OR mc.statut_cautisation_client IS NULL)) as montant_total_cotise
                FROM souscriptions s
                LEFT JOIN sessions sess ON sess.code_session = s.session_code
                WHERE s.code_souscription = ? LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$souscriptionCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return 0;
            return max(0, (float)($row['totale_souscription'] ?? 0) - (float)($row['montant_total_cotise'] ?? 0));
        } catch (Exception $e) {
            error_log("ModelSouscription::getSoldeRestant error: " . $e->getMessage());
            return 0;
        }
    }

    public function getJoursRestants(string $souscriptionCode): int
    {
        try {
            $sql = "SELECT nombre_jour_total, nombre_jour_cotise FROM souscriptions WHERE code_souscription = ? LIMIT 1";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$souscriptionCode]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) return 0;
            return max(0, (int)($row['nombre_jour_total'] ?? 0) - (int)($row['nombre_jour_cotise'] ?? 0));
        } catch (Exception $e) {
            error_log("ModelSouscription::getJoursRestants error: " . $e->getMessage());
            return 0;
        }
    }

    public function createSouscriptionWithMultiplePacks(array $souscriptionData, array $packCodes): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $cols = $this->getCon()->query("DESCRIBE souscriptions")->fetchAll(PDO::FETCH_COLUMN);
            $filteredData = array_intersect_key($souscriptionData, array_flip($cols));
            $colsStr = implode(',', array_keys($filteredData));
            $paramsStr = implode(',', array_fill(0, count($filteredData), '?'));
            $stmt = $this->getCon()->prepare("INSERT INTO souscriptions ({$colsStr}) VALUES ({$paramsStr})");
            $stmt->execute(array_values($filteredData));

            $anneeCode = $souscriptionData['annee_code'] ?? Context::annee();
            $etabCode = $souscriptionData['etablissement_code'] ?? Context::etablissement();
            $zoneCode = $souscriptionData['zone_code'] ?? Context::zone();
            $userCode = $souscriptionData['user_code'] ?? Context::user();

            if (empty($anneeCode) || empty($etabCode) || empty($zoneCode) || empty($userCode)) {
                throw new Exception("Champs obligatoires manquants pour l'insertion de la souscription (zone, année, établissement ou utilisateur null).");
            }

            $stmtPack = $this->getCon()->prepare("
                INSERT INTO pack_souscriptions (souscription_code, pack_code, annee_code, etablissement_code, created_at_pack_souscription, user_code, zone_code)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($packCodes as $packCode) {
                $stmtPack->execute([
                    $souscriptionData['code_souscription'],
                    $packCode,
                    $anneeCode,
                    $etabCode,
                    date('Y-m-d H:i:s'),
                    $userCode,
                    $zoneCode
                ]);
            }

            $this->getCon()->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelSouscription::createSouscriptionWithMultiplePacks error: " . $e->getMessage());
            return false;
        }
    }
}
