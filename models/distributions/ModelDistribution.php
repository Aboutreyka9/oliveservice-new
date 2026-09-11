<?php

class ModelDistribution extends BaseModel
{
    protected string $table = 'distributions';
    protected string $primaryKey = 'id_distribution';
    protected ?string $statusField = 'statut_distribution';
    protected ?string $createdAtField = 'created_at_distribution';

    public function getAllWithDetails(): array
    {
        try {
            $sql = "
                SELECT d.*, 
                       c.nom_client, c.telephone_client, c.code_client,
                       s.code_souscription, s.statut_souscription, s.montant_total_prevu,
                       u.nom_user as nom_livreur, u.prenom_user as prenom_livreur,
                       z.libelle_zone,
                       GROUP_CONCAT(DISTINCT p.libelle_pack SEPARATOR ', ') as libelle_pack
                FROM distributions d
                LEFT JOIN clients c ON c.code_client = d.client_code OR c.code_client = (SELECT client_code FROM souscriptions WHERE code_souscription = d.souscription_code LIMIT 1)
                LEFT JOIN souscriptions s ON s.code_souscription = d.souscription_code
                LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
                LEFT JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN users u ON u.code_user = d.user_code
                LEFT JOIN zones z ON z.code_zone = d.zone_code
                WHERE 1=1
            ";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('d', $conds, $params, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " GROUP BY d.id_distribution ORDER BY d.created_at_distribution DESC";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDistribution::getAllWithDetails error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère la liste des packs et le cumul d'articles attendus pour une souscription donnée
     */
    public function getPacksDetailsForSouscription(string $souscriptionCode): array
    {
        try {
            $sql = "
                SELECT ps.pack_code, p.libelle_pack, p.prix_cotisation_pack, p.categorie_pack_code, cp.libelle_categorie_pack,
                       COALESCE(SUM(pa.quantite_article), 0) as quantite_article_attendue
                FROM pack_souscriptions ps
                JOIN packs p ON p.code_pack = ps.pack_code
                LEFT JOIN categorie_packs cp ON cp.code_categorie_pack = p.categorie_pack_code
                LEFT JOIN pack_articles pa ON pa.pack_code = p.code_pack
                WHERE ps.souscription_code = ?
                GROUP BY ps.pack_code, p.libelle_pack, p.prix_cotisation_pack, p.categorie_pack_code, cp.libelle_categorie_pack
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$souscriptionCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDistribution::getPacksDetailsForSouscription error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Crée une distribution ainsi que les détails dans distribution_packs et met à jour la souscription
     */
    public function createDistributionWithPacks(array $distributionData, array $packsItems): bool
    {
        try {
            $this->getCon()->beginTransaction();

            $etabCode = !empty($distributionData['etablissement_code']) ? $distributionData['etablissement_code'] : Context::etablissement();
            $zoneCode = !empty($distributionData['zone_code']) ? $distributionData['zone_code'] : Context::zone();
            $anneeCode = !empty($distributionData['annee_code']) ? $distributionData['annee_code'] : Context::annee();
            $userCode = !empty($distributionData['user_code']) ? $distributionData['user_code'] : Context::user();

            if (empty($etabCode) || empty($zoneCode) || empty($anneeCode) || empty($userCode)) {
                throw new Exception("Champs obligatoires manquants pour l'enregistrement de la distribution (établissement, zone, année ou utilisateur nul).");
            }

            $distributionData['etablissement_code'] = $etabCode;
            $distributionData['zone_code'] = $zoneCode;
            $distributionData['annee_code'] = $anneeCode;
            $distributionData['user_code'] = $userCode;

            $cols = $this->getCon()->query("DESCRIBE distributions")->fetchAll(PDO::FETCH_COLUMN);
            $filteredData = array_intersect_key($distributionData, array_flip($cols));

            $colsStr = implode(',', array_keys($filteredData));
            $paramsStr = implode(',', array_fill(0, count($filteredData), '?'));

            $stmt = $this->getCon()->prepare("INSERT INTO distributions ({$colsStr}) VALUES ({$paramsStr})");
            $stmt->execute(array_values($filteredData));

            $distCode = $distributionData['code_distribution'];
            $etabCode = $distributionData['etablissement_code'];
            $zoneCode = $distributionData['zone_code'];
            $anneeCode = $distributionData['annee_code'];
            $userCode = $distributionData['user_code'];

            // Insertion dans distribution_packs
            $stmtPack = $this->getCon()->prepare("
                INSERT INTO distribution_packs (
                    distribution_code, pack_code, quantite_article_attendue, quantite_article_livree,
                    etablissement_code, zone_code, annee_code, user_code, created_at_distribution_pack
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");

            foreach ($packsItems as $item) {
                $stmtPack->execute([
                    $distCode,
                    $item['pack_code'],
                    (int)($item['quantite_article_attendue'] ?? 0),
                    (int)($item['quantite_article_livree'] ?? 0),
                    $etabCode,
                    $zoneCode,
                    $anneeCode,
                    $userCode,
                    date('Y-m-d H:i:s')
                ]);
            }

            // Mise à jour de la souscription : statut_distribution = 'valide'
            $souscriptionCode = $distributionData['souscription_code'] ?? null;
            if ($souscriptionCode) {
                $statut = $distributionData['statut_distribution'] ?? 'valide';
                $stmtUpd = $this->getCon()->prepare("
                    UPDATE souscriptions 
                    SET statut_distribution = ?,
                        updated_at_souscription = ?
                    WHERE code_souscription = ?
                ");
                $stmtUpd->execute([$statut, date('Y-m-d H:i:s'), $souscriptionCode]);
            }

            $this->getCon()->commit();
            return true;
        } catch (Exception $e) {
            if ($this->getCon()->inTransaction()) {
                $this->getCon()->rollBack();
            }
            error_log("ModelDistribution::createDistributionWithPacks error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les packs enregistrés pour une distribution
     */
    public function getDistributionPacks(string $distributionCode): array
    {
        try {
            $sql = "
                SELECT dp.*, p.libelle_pack, cp.libelle_categorie_pack
                FROM distribution_packs dp
                JOIN packs p ON p.code_pack = dp.pack_code
                LEFT JOIN categorie_packs cp ON cp.code_categorie_pack = p.categorie_pack_code
                WHERE dp.distribution_code = ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$distributionCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelDistribution::getDistributionPacks error: " . $e->getMessage());
            return [];
        }
    }

    public function getBySouscription(string $souscriptionCode): ?array
    {
        try {
            $sql = "SELECT * FROM distributions WHERE souscription_code = ?";
            $params = [$souscriptionCode];
            $conds = [];
            Context::applyTripleFilter('', $conds, $params, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $sql .= " ORDER BY created_at_distribution DESC LIMIT 1";

            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Exception $e) {
            error_log("ModelDistribution::getBySouscription error: " . $e->getMessage());
            return null;
        }
    }
}
