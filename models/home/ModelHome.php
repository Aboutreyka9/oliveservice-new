<?php

class ModelHome extends BaseModel
{
    protected string $table = 'clients';
    protected string $primaryKey = 'id_client';

    public function __construct()
    {
        parent::__construct();
    }

    // ─── Helpers défensifs ────────────────────────────────────────────────────
    // Chaque requête est isolée : une erreur SQL (table manquante, colonne
    // absente, etc.) n'affecte que la métrique concernée, pas tout le dashboard.

    private function safeCount(\PDO $db, string $sql, array $params = []): int
    {
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (\Exception $e) {
            error_log('[ModelHome::safeCount] ' . $e->getMessage() . ' — SQL: ' . $sql);
            return 0;
        }
    }

    private function safeSum(\PDO $db, string $sql, array $params = []): float
    {
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            return (float)($stmt->fetchColumn() ?: 0);
        } catch (\Exception $e) {
            error_log('[ModelHome::safeSum] ' . $e->getMessage() . ' — SQL: ' . $sql);
            return 0.0;
        }
    }

    // ─────────────────────────────────────────────────────────────────────────

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
                    $_SESSION['annee_active_code']    = $activeRow['code_annee'];
                    $_SESSION['annee_active_libelle'] = $activeRow['libelle_annee'];
                }
            }

            $userCodeFilter = Context::isCommercial() ? Context::user() : ($userCode ?? null);

            // ── 1. Clients ────────────────────────────────────────────────────
            $sqlClients = "SELECT COUNT(*) FROM clients WHERE 1=1";
            $pClients = [];
            if (Context::etablissement()) { $sqlClients .= " AND etablissement_code = ?"; $pClients[] = Context::etablissement(); }
            if (Context::zone())          { $sqlClients .= " AND zone_code = ?";          $pClients[] = Context::zone(); }
            if ($userCodeFilter)          { $sqlClients .= " AND user_code = ?";          $pClients[] = $userCodeFilter; }
            $totalClients = $this->safeCount($db, $sqlClients, $pClients);

            // ── 2. Packs & Articles ───────────────────────────────────────────
            $sqlPacks = "SELECT COUNT(*) FROM packs WHERE statut_pack = 'actif'";
            $pPacks = []; $condsPacks = [];
            Context::applyTripleFilter('', $condsPacks, $pPacks);
            if (!empty($condsPacks)) $sqlPacks .= " AND " . implode(' AND ', $condsPacks);
            $totalPacks = $this->safeCount($db, $sqlPacks, $pPacks);

            $totalArticles = $this->safeCount($db, "SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'");

            // ── 3. Souscriptions ──────────────────────────────────────────────
            $sqlSouscr = "SELECT COUNT(*) FROM souscriptions WHERE statut_souscription IN ('valide', 'reconduite')";
            $pSouscr = []; $condsSouscr = [];
            Context::applyTripleFilter('', $condsSouscr, $pSouscr, true);
            if (!empty($condsSouscr)) $sqlSouscr .= " AND " . implode(' AND ', $condsSouscr);
            $totalSouscriptions = $this->safeCount($db, $sqlSouscr, $pSouscr);

            $sqlSoldees = "SELECT COUNT(*) FROM souscriptions WHERE statut_souscription = 'solde'";
            $pSoldees = []; $condsSoldees = [];
            Context::applyTripleFilter('', $condsSoldees, $pSoldees, true);
            if (!empty($condsSoldees)) $sqlSoldees .= " AND " . implode(' AND ', $condsSoldees);
            $totalSouscriptionsSoldees = $this->safeCount($db, $sqlSoldees, $pSoldees);

            // ── 4. Cotisations ────────────────────────────────────────────────
            $sqlCotis = "SELECT COALESCE(SUM(montant_cautisation_client), 0) FROM cautisation_clients WHERE statut_cautisation_client != 'ennule'";
            $pCotis = []; $condsCotis = [];
            Context::applyTripleFilter('', $condsCotis, $pCotis, true, false);
            if (!empty($condsCotis)) $sqlCotis .= " AND " . implode(' AND ', $condsCotis);
            $totalCotisations = $this->safeSum($db, $sqlCotis, $pCotis);

            // Table paiements : optionnelle (peut ne pas encore exister)
            $totalPaiements = $this->safeSum($db, "SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE statut_paiement = 'confirme'");
            $caEncaisse = $totalCotisations + $totalPaiements;

            // ── 5. Versements ─────────────────────────────────────────────────
            $sqlVersVal = "SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'valide'";
            $pVersVal = []; $condsVersVal = [];
            Context::applyTripleFilter('', $condsVersVal, $pVersVal, true, false);
            if (!empty($condsVersVal)) $sqlVersVal .= " AND " . implode(' AND ', $condsVersVal);
            $totalVersements = $this->safeSum($db, $sqlVersVal, $pVersVal);

            $sqlVersAtt = "SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'En attente'";
            $pVersAtt = []; $condsVersAtt = [];
            Context::applyTripleFilter('', $condsVersAtt, $pVersAtt, true, false);
            if (!empty($condsVersAtt)) $sqlVersAtt .= " AND " . implode(' AND ', $condsVersAtt);
            $totalVersementsEnAttente = $this->safeSum($db, $sqlVersAtt, $pVersAtt);

            // ── 6. Dépenses & Solde Net ───────────────────────────────────────
            $sqlDepenses = "SELECT COALESCE(SUM(montant_depense), 0) FROM depenses WHERE statut_depense != 'inactif'";
            $pDepenses = []; $condsDepenses = [];
            Context::applyTripleFilter('', $condsDepenses, $pDepenses, false);
            if (!empty($condsDepenses)) $sqlDepenses .= " AND " . implode(' AND ', $condsDepenses);
            $totalDepenses = $this->safeSum($db, $sqlDepenses, $pDepenses);
            $soldeNet = $caEncaisse - $totalDepenses;

            // ── 7. Distributions ──────────────────────────────────────────────
            $sqlDist = "SELECT COUNT(*) FROM distributions WHERE 1=1";
            $pDist = []; $condsDist = [];
            Context::applyTripleFilter('', $condsDist, $pDist, false);
            if (!empty($condsDist)) $sqlDist .= " AND " . implode(' AND ', $condsDist);
            $totalDistributions = $this->safeCount($db, $sqlDist, $pDist);

            $sqlDistVal = "SELECT COUNT(*) FROM distributions WHERE statut_distribution = 'valide'";
            $pDistVal = []; $condsDistVal = [];
            Context::applyTripleFilter('', $condsDistVal, $pDistVal, false);
            if (!empty($condsDistVal)) $sqlDistVal .= " AND " . implode(' AND ', $condsDistVal);
            $totalDistributionsValidees = $this->safeCount($db, $sqlDistVal, $pDistVal);

            return [
                'annee_code'                  => $anneeCode,
                'total_clients'               => $totalClients,
                'total_packs'                 => $totalPacks,
                'total_souscriptions'         => $totalSouscriptions,
                'total_souscriptions_soldees' => $totalSouscriptionsSoldees,
                'total_articles'              => $totalArticles,
                'total_cotisations'           => $totalCotisations,
                'total_paiements'             => $totalPaiements,
                'ca_encaisse'                 => $caEncaisse,
                'total_versements'            => $totalVersements,
                'total_versements_en_attente' => $totalVersementsEnAttente,
                'total_depenses'              => $totalDepenses,
                'total_distributions'         => $totalDistributions,
                'total_distributions_validees'=> $totalDistributionsValidees,
                'solde_net'                   => $soldeNet,
            ];

        } catch (\Exception $e) {
            error_log('ModelHome::getStats fatal error: ' . $e->getMessage());
            return [
                'annee_code' => $anneeCode, 'total_clients' => 0, 'total_packs' => 0,
                'total_souscriptions' => 0, 'total_souscriptions_soldees' => 0,
                'total_articles' => 0, 'total_cotisations' => 0, 'total_paiements' => 0,
                'ca_encaisse' => 0, 'total_versements' => 0, 'total_versements_en_attente' => 0,
                'total_depenses' => 0, 'total_distributions' => 0,
                'total_distributions_validees' => 0, 'solde_net' => 0,
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
            // souscriptions n'a pas de colonne pack_code directe.
            // Le lien passe par la table de liaison pack_souscriptions.
            $sql = "SELECT d.*, cl.nom_client, cl.telephone_client, p.libelle_pack
                    FROM distributions d
                    LEFT JOIN souscriptions s  ON s.code_souscription = d.souscription_code
                    LEFT JOIN clients cl       ON cl.code_client       = s.client_code
                    LEFT JOIN pack_souscriptions ps ON ps.souscription_code = s.code_souscription
                                                   AND ps.statut_pack_souscription = 'actif'
                    LEFT JOIN packs p          ON p.code_pack           = ps.pack_code
                    WHERE 1=1";
            $params = [];
            $conds  = [];
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
