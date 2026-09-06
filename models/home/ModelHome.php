<?php

class ModelHome extends BaseModel
{
    protected string $table = 'clients';
    protected string $primaryKey = 'id_client';

    public function __construct()
    {
        parent::__construct();
    }

    public function getStats(?string $anneeCode = null, ?string $userCode = null, ?string $roleCode = null): array
    {
        try {
            $db = $this->pdo->getCon();

            if (!$anneeCode) {
                $anneeCode = $_SESSION['annee_active_code'] ?? null;
            }
            if (!$anneeCode) {
                $stmtA = $db->query("SELECT code_annee, libelle_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
                $activeRow = $stmtA->fetch(PDO::FETCH_ASSOC);
                if ($activeRow) {
                    $anneeCode = $activeRow['code_annee'];
                    $_SESSION['annee_active_code'] = $activeRow['code_annee'];
                    $_SESSION['annee_active_libelle'] = $activeRow['libelle_annee'];
                }
            }

            $userCodeFilter = Context::isCommercial() ? Context::user() : ($userCode ?? null);
            $etabCode = Context::etablissement();
            $zoneCode = Context::zone();

            // 1. Clients
            $sqlClients = "SELECT COUNT(*) FROM clients WHERE 1=1";
            $pClients = [];
            if ($etabCode) {
                $sqlClients .= " AND etablissement_code = ?";
                $pClients[] = $etabCode;
            }
            if ($zoneCode) {
                $sqlClients .= " AND zone_code = ?";
                $pClients[] = $zoneCode;
            }
            if ($userCodeFilter) {
                $sqlClients .= " AND user_code = ?";
                $pClients[] = $userCodeFilter;
            }
            $stmt = $db->prepare($sqlClients);
            $stmt->execute($pClients);
            $totalClients = (int)$stmt->fetchColumn();

            // 2. Packs & Articles
            $sqlPacks = "SELECT COUNT(*) FROM packs WHERE statut_pack = 'actif'";
            $pPacks = [];
            $sqlPacksCond = [];
            Context::applyTripleFilter('', $sqlPacksCond, $pPacks);
            if (!empty($sqlPacksCond)) $sqlPacks .= " AND " . implode(' AND ', $sqlPacksCond);
            $stmtPacks = $db->prepare($sqlPacks);
            $stmtPacks->execute($pPacks);
            $totalPacks = (int)$stmtPacks->fetchColumn();

            $totalArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'")->fetchColumn();

            // 3. Souscriptions
            $sqlSouscr = "SELECT COUNT(*) FROM souscriptions WHERE statut_souscription IN ('valide', 'reconduite')";
            $pSouscr = [];
            $condsSouscr = [];
            Context::applyTripleFilter('', $condsSouscr, $pSouscr, true);
            if (!empty($condsSouscr)) $sqlSouscr .= " AND " . implode(' AND ', $condsSouscr);
            $stmt = $db->prepare($sqlSouscr);
            $stmt->execute($pSouscr);
            $totalSouscriptions = (int)$stmt->fetchColumn();

            $sqlSoldees = "SELECT COUNT(*) FROM souscriptions WHERE statut_souscription = 'solde'";
            $pSoldees = [];
            $condsSoldees = [];
            Context::applyTripleFilter('', $condsSoldees, $pSoldees, true);
            if (!empty($condsSoldees)) $sqlSoldees .= " AND " . implode(' AND ', $condsSoldees);
            $stmt = $db->prepare($sqlSoldees);
            $stmt->execute($pSoldees);
            $totalSouscriptionsSoldees = (int)$stmt->fetchColumn();

            // 4. Cotisations
            $sqlCotis = "SELECT COALESCE(SUM(montant_cautisation_client), 0) FROM cautisation_clients WHERE statut_cautisation_client != 'ennule'";
            $pCotis = [];
            $condsCotis = [];
            Context::applyTripleFilter('', $condsCotis, $pCotis, true, false);
            if (!empty($condsCotis)) $sqlCotis .= " AND " . implode(' AND ', $condsCotis);
            $stmt = $db->prepare($sqlCotis);
            $stmt->execute($pCotis);
            $totalCotisations = (float)($stmt->fetchColumn() ?: 0);

            // Table paiements : optionnelle, peut ne pas encore exister
            $totalPaiements = 0.0;
            try {
                $totalPaiements = (float)($db->query("SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE statut_paiement = 'confirme'")->fetchColumn() ?: 0);
            } catch (Exception $e) {
                // Table paiements absente ou non disponible — on ignore
            }
            $caEncaisse = $totalCotisations + $totalPaiements;

            // 5. Versements
            $sqlVersVal = "SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'valide'";
            $pVersVal = [];
            $condsVersVal = [];
            Context::applyTripleFilter('', $condsVersVal, $pVersVal, true, false);
            if (!empty($condsVersVal)) $sqlVersVal .= " AND " . implode(' AND ', $condsVersVal);
            $stmt = $db->prepare($sqlVersVal);
            $stmt->execute($pVersVal);
            $totalVersements = (float)($stmt->fetchColumn() ?: 0);

            $sqlVersAtt = "SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'En attente'";
            $pVersAtt = [];
            $condsVersAtt = [];
            Context::applyTripleFilter('', $condsVersAtt, $pVersAtt, true, false);
            if (!empty($condsVersAtt)) $sqlVersAtt .= " AND " . implode(' AND ', $condsVersAtt);
            $stmt = $db->prepare($sqlVersAtt);
            $stmt->execute($pVersAtt);
            $totalVersementsEnAttente = (float)($stmt->fetchColumn() ?: 0);

            // 6. Dépenses & Solde Net
            $sqlDepenses = "SELECT COALESCE(SUM(montant_depense), 0) FROM depenses WHERE statut_depense != 'inactif'";
            $pDepenses = [];
            $condsDepenses = [];
            Context::applyTripleFilter('', $condsDepenses, $pDepenses, false);
            if (!empty($condsDepenses)) $sqlDepenses .= " AND " . implode(' AND ', $condsDepenses);
            $stmtDep = $db->prepare($sqlDepenses);
            $stmtDep->execute($pDepenses);
            $totalDepenses = (float)($stmtDep->fetchColumn() ?: 0);
            $soldeNet = $caEncaisse - $totalDepenses;

            // 7. Distributions
            $sqlDist = "SELECT COUNT(*) FROM distributions WHERE 1=1";
            $pDist = [];
            $condsDist = [];
            Context::applyTripleFilter('', $condsDist, $pDist, false);
            if (!empty($condsDist)) $sqlDist .= " AND " . implode(' AND ', $condsDist);
            $stmtDist = $db->prepare($sqlDist);
            $stmtDist->execute($pDist);
            $totalDistributions = (int)$stmtDist->fetchColumn();

            $sqlDistVal = "SELECT COUNT(*) FROM distributions WHERE statut_distribution = 'valide'";
            $pDistVal = [];
            $condsDistVal = [];
            Context::applyTripleFilter('', $condsDistVal, $pDistVal, false);
            if (!empty($condsDistVal)) $sqlDistVal .= " AND " . implode(' AND ', $condsDistVal);
            $stmtDistVal = $db->prepare($sqlDistVal);
            $stmtDistVal->execute($pDistVal);
            $totalDistributionsValidees = (int)$stmtDistVal->fetchColumn();

            return [
                'annee_code' => $anneeCode,
                'total_clients' => $totalClients,
                'total_packs' => $totalPacks,
                'total_souscriptions' => $totalSouscriptions,
                'total_souscriptions_soldees' => $totalSouscriptionsSoldees,
                'total_articles' => $totalArticles,
                'total_cotisations' => $totalCotisations,
                'total_paiements' => $totalPaiements,
                'ca_encaisse' => $caEncaisse,
                'total_versements' => $totalVersements,
                'total_versements_en_attente' => $totalVersementsEnAttente,
                'total_depenses' => $totalDepenses,
                'total_distributions' => $totalDistributions,
                'total_distributions_validees' => $totalDistributionsValidees,
                'solde_net' => $soldeNet
            ];
        } catch (Exception $e) {
            error_log("ModelHome::getStats error: " . $e->getMessage());
            return [
                'annee_code' => $anneeCode,
                'total_clients' => 0,
                'total_packs' => 0,
                'total_souscriptions' => 0,
                'total_souscriptions_soldees' => 0,
                'total_articles' => 0,
                'total_cotisations' => 0,
                'total_paiements' => 0,
                'ca_encaisse' => 0,
                'total_versements' => 0,
                'total_versements_en_attente' => 0,
                'total_depenses' => 0,
                'total_distributions' => 0,
                'total_distributions_validees' => 0,
                'solde_net' => 0
            ];
        }
    }

    public function getRecentCotisations(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT c.*, cl.nom_client, cl.telephone_client, s.code_souscription
                    FROM cautisation_clients c
                    LEFT JOIN souscriptions s ON s.code_souscription = c.souscription_code
                    LEFT JOIN clients cl ON cl.code_client = s.client_code
                    WHERE 1=1";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('c', $conds, $params, true, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $limitInt = max(1, (int)$limit);
            $sql .= " ORDER BY c.id_cautisation_client DESC LIMIT $limitInt";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentCotisations error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentVersements(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT v.*, u.nom_user as nom_commercial, u.prenom_user as prenom_commercial, z.libelle_zone
                    FROM versements_commerciaux v
                    LEFT JOIN users u ON u.code_user = v.commercial_code
                    LEFT JOIN zones z ON z.code_zone = v.zone_code
                    WHERE 1=1";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('v', $conds, $params, true, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $limitInt = max(1, (int)$limit);
            $sql .= " ORDER BY v.id_versement DESC LIMIT $limitInt";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentVersements error: " . $e->getMessage());
            return [];
        }
    }

    public function getRecentDepenses(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT d.*, td.libelle_type_depense
                    FROM depenses d
                    LEFT JOIN type_depenses td ON td.code_type_depense = d.type_depense_code
                    WHERE 1=1";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('d', $conds, $params, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $limitInt = max(1, (int)$limit);
            $sql .= " ORDER BY d.id_depense DESC LIMIT $limitInt";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentDepenses error: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingVersements(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT v.*, u.nom_user as nom_commercial, u.prenom_user as prenom_commercial, z.libelle_zone
                    FROM versements_commerciaux v
                    LEFT JOIN users u ON u.code_user = v.commercial_code
                    LEFT JOIN zones z ON z.code_zone = v.zone_code
                    WHERE v.statut_versement = 'En attente'";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('v', $conds, $params, true, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $limitInt = max(1, (int)$limit);
            $sql .= " ORDER BY v.id_versement DESC LIMIT $limitInt";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getPendingVersements error: " . $e->getMessage());
            return [];
        }
    }

    public function getPendingDistributions(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT d.*, cl.nom_client, cl.telephone_client, p.libelle_pack
                    FROM distributions d
                    LEFT JOIN souscriptions s ON s.code_souscription = d.souscription_code
                    LEFT JOIN clients cl ON cl.code_client = s.client_code
                    LEFT JOIN packs p ON p.code_pack = s.pack_code
                    WHERE 1=1";
            $params = [];
            $conds = [];
            Context::applyTripleFilter('d', $conds, $params, false);
            if (!empty($conds)) $sql .= " AND " . implode(' AND ', $conds);
            $limitInt = max(1, (int)$limit);
            $sql .= " ORDER BY d.id_distribution DESC LIMIT $limitInt";
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getPendingDistributions error: " . $e->getMessage());
            return [];
        }
    }
}
