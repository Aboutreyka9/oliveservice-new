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

            // ── 1. Clients ────────────────────────────────────────────────────
            $sqlClients = "SELECT COUNT(*) FROM clients c WHERE 1=1";
            $pClients = [];

            if (Context::etablissement()) {
                $sqlClients .= " AND c.etablissement_code = ?";
                $pClients[] = Context::etablissement();
            }

            if (Context::isCommercial()) {
                $sqlClients .= " AND (
                    EXISTS (SELECT 1 FROM client_commercials cc WHERE cc.client_code = c.code_client AND cc.commercial_code = ? AND cc.statut_client_commercial = 'actif')
                    OR (
                        NOT EXISTS (SELECT 1 FROM client_commercials cc_any WHERE cc_any.client_code = c.code_client)
                        AND c.user_code = ?
                    )
                )";
                $pClients[] = Context::user();
                $pClients[] = Context::user();
            } elseif (Context::isGestionnaire() && Context::zone()) {
                $sqlClients .= " AND (c.zone_code = ? OR EXISTS (SELECT 1 FROM souscriptions sub WHERE sub.client_code = c.code_client AND sub.zone_code = ? AND sub.etablissement_code = ? AND sub.annee_code = ?))";
                $pClients[] = Context::zone();
                $pClients[] = Context::zone();
                $pClients[] = Context::etablissement();
                $pClients[] = Context::annee();
            } elseif (!empty(Context::zone()) && !Context::isSuperAdmin() && !Context::isAdmin()) {
                $sqlClients .= " AND c.zone_code = ?";
                $pClients[] = Context::zone();
            }
            $totalClients = $this->safeCount($db, $sqlClients, $pClients);

            // ── 2. Packs & Articles ───────────────────────────────────────────
            $sqlPacks = "SELECT COUNT(*) FROM packs WHERE statut_pack = 'actif'";
            $pPacks = []; $condsPacks = [];
            Context::applyTripleFilter('', $condsPacks, $pPacks);
            if (!empty($condsPacks)) $sqlPacks .= " AND " . implode(' AND ', $condsPacks);
            $totalPacks = $this->safeCount($db, $sqlPacks, $pPacks);

            $totalArticles = $this->safeCount($db, "SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'");

            // ── 3. Souscriptions ──────────────────────────────────────────────
            $buildSubQuery = function(string $statutCondition = "") {
                $sql = "SELECT COUNT(*) FROM souscriptions sub JOIN clients c ON c.code_client = sub.client_code WHERE 1=1";
                if ($statutCondition !== '') {
                    $sql .= " AND " . $statutCondition;
                }
                $params = [];
                $conds = [];
                Context::applyTripleFilter('sub', $conds, $params, false);
                if (!empty($conds)) {
                    $sql .= " AND " . implode(' AND ', $conds);
                }
                if (Context::isCommercial()) {
                    $sql .= " AND (
                        EXISTS (SELECT 1 FROM client_commercials cc WHERE cc.client_code = sub.client_code AND cc.commercial_code = ? AND cc.statut_client_commercial = 'actif')
                        OR (NOT EXISTS (SELECT 1 FROM client_commercials cc_any WHERE cc_any.client_code = sub.client_code) AND c.user_code = ?)
                    )";
                    $params[] = Context::user();
                    $params[] = Context::user();
                }
                return [$sql, $params];
            };

            // Souscriptions du portefeuille actif (non annulées)
            [$sqlAll, $pAll] = $buildSubQuery("sub.statut_souscription != 'annule'");
            $totalSouscriptions = $this->safeCount($db, $sqlAll, $pAll);

            // Souscriptions globales totales (tous statuts confondus)
            [$sqlTotalGlobal, $pTotalGlobal] = $buildSubQuery("");
            $totalSouscriptionsGlobal = $this->safeCount($db, $sqlTotalGlobal, $pTotalGlobal);

            // Souscriptions en cours / actives
            [$sqlVal, $pVal] = $buildSubQuery("sub.statut_souscription = 'valide'");
            $totalSouscriptionsValides = $this->safeCount($db, $sqlVal, $pVal);

            // Souscriptions soldées
            [$sqlSolde, $pSolde] = $buildSubQuery("sub.statut_souscription = 'solde'");
            $totalSouscriptionsSoldees = $this->safeCount($db, $sqlSolde, $pSolde);

            // Souscriptions reconduites
            [$sqlRecond, $pRecond] = $buildSubQuery("sub.statut_souscription = 'reconduite'");
            $totalSouscriptionsReconduites = $this->safeCount($db, $sqlRecond, $pRecond);

            // Souscriptions annulées
            [$sqlAnnule, $pAnnule] = $buildSubQuery("sub.statut_souscription = 'annule'");
            $totalSouscriptionsAnnulees = $this->safeCount($db, $sqlAnnule, $pAnnule);

            // ── 4. Cotisations ────────────────────────────────────────────────
            // Cotisations Validées (Prises en compte dans le CA et le Solde Net)
            // Uniquement pour les souscriptions valides ou soldées
            $sqlCotisVal = "
                SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) 
                FROM cautisation_clients cc
                JOIN souscriptions sub ON sub.code_souscription = cc.souscription_code AND sub.etablissement_code = cc.etablissement_code
                WHERE cc.statut_cautisation_client = 'valide'
                  AND LOWER(TRIM(sub.statut_souscription)) IN ('valide', 'solde')
            ";
            $pCotisVal = []; $condsCotisVal = [];
            Context::applyTripleFilter('cc', $condsCotisVal, $pCotisVal, true, false);
            if (!empty($condsCotisVal)) $sqlCotisVal .= " AND " . implode(' AND ', $condsCotisVal);
            $totalCotisationsValidees = $this->safeSum($db, $sqlCotisVal, $pCotisVal);

            // Cotisations En Attente de Validation (sur souscriptions valides ou soldées)
            $sqlCotisAtt = "
                SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) 
                FROM cautisation_clients cc
                JOIN souscriptions sub ON sub.code_souscription = cc.souscription_code AND sub.etablissement_code = cc.etablissement_code
                WHERE (cc.statut_cautisation_client = 'en_attente' OR cc.statut_cautisation_client IS NULL OR cc.statut_cautisation_client = '')
                  AND LOWER(TRIM(sub.statut_souscription)) IN ('valide', 'solde')
            ";
            $pCotisAtt = []; $condsCotisAtt = [];
            Context::applyTripleFilter('cc', $condsCotisAtt, $pCotisAtt, true, false);
            if (!empty($condsCotisAtt)) $sqlCotisAtt .= " AND " . implode(' AND ', $condsCotisAtt);
            $totalCotisationsEnAttente = $this->safeSum($db, $sqlCotisAtt, $pCotisAtt);

            // Total Général des Encaissements (Toutes cotisations non annulées sur souscriptions valides ou soldées)
            $sqlCotisAll = "
                SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) 
                FROM cautisation_clients cc
                JOIN souscriptions sub ON sub.code_souscription = cc.souscription_code AND sub.etablissement_code = cc.etablissement_code
                WHERE (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)
                  AND LOWER(TRIM(sub.statut_souscription)) IN ('valide', 'solde')
            ";
            $pCotisAll = []; $condsCotisAll = [];
            Context::applyTripleFilter('cc', $condsCotisAll, $pCotisAll, true, false);
            if (!empty($condsCotisAll)) $sqlCotisAll .= " AND " . implode(' AND ', $condsCotisAll);
            $totalCotisationsGlobales = $this->safeSum($db, $sqlCotisAll, $pCotisAll);

            // Pour le Commercial : Il voit l'ensemble de ce qu'il a encaissé sur le terrain
            // Pour la Finance / Admin : Le CA Encaissé et le Solde Net se basent STRICTEMENT sur les fonds VALIDÉS
            if (Context::isCommercial()) {
                $totalCotisations = $totalCotisationsGlobales;
                $caEncaisse = $totalCotisationsGlobales;
            } else {
                $totalCotisations = $totalCotisationsValidees;
                $caEncaisse = $totalCotisationsValidees;
            }

            $totalPaiements = 0.0;

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

            // Solde Net = CA Encaisse Valide - Dépenses
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

            // ── 8. Utilisateurs & Commerciaux ─────────────────────────────────────
            $totalUsers = $this->safeCount($db, "SELECT COUNT(*) FROM users WHERE statut_user = 'actif'");
            $totalCommerciaux = $this->safeCount($db, "
                SELECT COUNT(DISTINCT u.code_user) 
                FROM users u 
                INNER JOIN user_roles ur ON ur.user_code = u.code_user 
                WHERE ur.role_code = 'ROLE_COMMERCIAL' AND u.statut_user = 'actif'
            ");

            // ── 9. Catégories Packs & Sessions ───────────────────────────────────
            $sqlCat = "SELECT COUNT(*) FROM categorie_packs WHERE statut_categorie_pack = 'actif'";
            $pCat = [];
            $etabCode = Context::etablissement();
            if (!empty($etabCode)) {
                $sqlCat .= " AND etablissement_code = ?";
                $pCat[] = $etabCode;
            }
            $zoneCode = Context::zone();
            if (!empty($zoneCode)) {
                $sqlCat .= " AND (zone_code = ? OR zone_code = '' OR zone_code IS NULL)";
                $pCat[] = $zoneCode;
            }
            $totalCategories = $this->safeCount($db, $sqlCat, $pCat);

            $sqlSess = "SELECT COUNT(*) FROM sessions WHERE statut_session = 'actif'";
            $pSess = []; $condsSess = [];
            Context::applyTripleFilter('', $condsSess, $pSess, false);
            if (!empty($condsSess)) $sqlSess .= " AND " . implode(' AND ', $condsSess);
            $totalSessions = $this->safeCount($db, $sqlSess, $pSess);

            // ── 10. Montants Attendus (Journée & Année) ──────────────────────────
            // Montant total attendu pour une journée d'activité (formule dynamique pure)
            $sqlAttJour = "
                SELECT COALESCE(SUM(
                    (SELECT COALESCE(SUM(p.prix_cotisation_pack), 0)
                     FROM pack_souscriptions ps
                     JOIN packs p ON p.code_pack = ps.pack_code
                     WHERE ps.souscription_code = s.code_souscription)
                ), 0)
                FROM souscriptions s
                WHERE s.statut_souscription IN ('valide', 'reconduite')
            ";
            $pAttJour = []; $condsAttJour = [];
            Context::applyTripleFilter('s', $condsAttJour, $pAttJour, true);
            if (!empty($condsAttJour)) $sqlAttJour .= " AND " . implode(' AND ', $condsAttJour);
            $montantAttenduJournee = $this->safeSum($db, $sqlAttJour, $pAttJour);

            // Montant total attendu pour toute l'année d'activité (formule dynamique pure : cotisations packs x jours session)
            $sqlAttAnn = "
                SELECT COALESCE(SUM(
                    (SELECT COALESCE(SUM(p.prix_cotisation_pack), 0)
                     FROM pack_souscriptions ps
                     JOIN packs p ON p.code_pack = ps.pack_code
                     WHERE ps.souscription_code = s.code_souscription)
                    * COALESCE(sess.nombre_jour_session, 0)
                ), 0)
                FROM souscriptions s
                LEFT JOIN sessions sess ON sess.code_session = s.session_code
                WHERE s.statut_souscription IN ('valide', 'solde', 'reconduite')
            ";
            $pAttAnn = []; $condsAttAnn = [];
            Context::applyTripleFilter('s', $condsAttAnn, $pAttAnn, true);
            if (!empty($condsAttAnn)) $sqlAttAnn .= " AND " . implode(' AND ', $condsAttAnn);
            $montantAttenduAnnee = $this->safeSum($db, $sqlAttAnn, $pAttAnn);

            // Total encaisse aujourd'hui (pour valider si l'objectif du jour est atteint sur souscriptions valides ou soldées)
            $sqlCotisToday = "
                SELECT COALESCE(SUM(cc.montant_cautisation_client), 0) 
                FROM cautisation_clients cc
                JOIN souscriptions sub ON sub.code_souscription = cc.souscription_code AND sub.etablissement_code = cc.etablissement_code
                WHERE DATE(COALESCE(cc.date_cautisation, cc.created_at_cautisation_client)) = CURDATE() 
                  AND (cc.statut_cautisation_client != 'annule' OR cc.statut_cautisation_client IS NULL)
                  AND LOWER(TRIM(sub.statut_souscription)) IN ('valide', 'solde')
            ";
            $pCotisToday = []; $condsCotisToday = [];
            Context::applyTripleFilter('cc', $condsCotisToday, $pCotisToday, true, false);
            if (!empty($condsCotisToday)) $sqlCotisToday .= " AND " . implode(' AND ', $condsCotisToday);
            $totalCotisationsAujourdhui = $this->safeSum($db, $sqlCotisToday, $pCotisToday);

            return [
                'annee_code'                      => $anneeCode,
                'total_clients'                   => $totalClients,
                'total_packs'                     => $totalPacks,
                'total_souscriptions'             => $totalSouscriptions,
                'total_souscriptions_global'      => $totalSouscriptionsGlobal,
                'total_souscriptions_valides'     => $totalSouscriptionsValides,
                'total_souscriptions_soldees'     => $totalSouscriptionsSoldees,
                'total_souscriptions_reconduites' => $totalSouscriptionsReconduites,
                'total_souscriptions_annulees'    => $totalSouscriptionsAnnulees,
                'total_articles'                  => $totalArticles,
                'total_cotisations'               => $totalCotisations,
                'total_cotisations_validees'      => $totalCotisationsValidees,
                'total_cotisations_en_attente'    => $totalCotisationsEnAttente,
                'total_cotisations_globales'      => $totalCotisationsGlobales,
                'total_paiements'                 => $totalPaiements,
                'ca_encaisse'                     => $caEncaisse,
                'total_versements'                => $totalVersements,
                'total_versements_en_attente'     => $totalVersementsEnAttente,
                'total_depenses'                  => $totalDepenses,
                'total_distributions'             => $totalDistributions,
                'total_distributions_validees'    => $totalDistributionsValidees,
                'solde_net'                       => $soldeNet,
                'total_users'                     => $totalUsers,
                'total_commerciaux'               => $totalCommerciaux,
                'total_categories'                => $totalCategories,
                'total_sessions'                  => $totalSessions,
                'montant_attendu_journee'         => $montantAttenduJournee,
                'montant_attendu_annee'           => $montantAttenduAnnee,
                'total_cotisations_aujourdhui'    => $totalCotisationsAujourdhui,
            ];

        } catch (\Exception $e) {
            error_log('ModelHome::getStats fatal error: ' . $e->getMessage());
            return [
                'annee_code' => $anneeCode, 'total_clients' => 0, 'total_packs' => 0,
                'total_souscriptions' => 0, 'total_souscriptions_global' => 0,
                'total_souscriptions_valides' => 0, 'total_souscriptions_soldees' => 0,
                'total_souscriptions_reconduites' => 0, 'total_souscriptions_annulees' => 0,
                'total_articles' => 0, 'total_cotisations' => 0,
                'total_cotisations_validees' => 0, 'total_cotisations_en_attente' => 0,
                'total_cotisations_globales' => 0, 'total_paiements' => 0,
                'ca_encaisse' => 0, 'total_versements' => 0, 'total_versements_en_attente' => 0,
                'total_depenses' => 0, 'total_distributions' => 0,
                'total_distributions_validees' => 0, 'solde_net' => 0,
                'total_users' => 0, 'total_commerciaux' => 0,
                'total_categories' => 0, 'total_sessions' => 0,
                'montant_attendu_journee' => 0.0, 'montant_attendu_annee' => 0.0,
                'total_cotisations_aujourdhui' => 0.0,
            ];
        }
    }

    public function getRecentCotisations(int $limit = 5): array
    {
        try {
            $db = $this->pdo->getCon();
            $sql = "SELECT c.*, cl.nom_client, cl.telephone_client, s.code_souscription
                    FROM cautisation_clients c
                    JOIN souscriptions s ON s.code_souscription = c.souscription_code AND s.etablissement_code = c.etablissement_code
                    JOIN clients cl ON cl.code_client = s.client_code
                    WHERE s.statut_souscription IN ('valide', 'solde')";
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
