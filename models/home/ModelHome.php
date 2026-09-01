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

            $totalClients = (int)$db->query("SELECT COUNT(*) FROM clients")->fetchColumn();
            $totalPacks = (int)$db->query("SELECT COUNT(*) FROM packs WHERE statut_pack = 'actif'")->fetchColumn();
            $totalSouscriptions = (int)$db->query("SELECT COUNT(*) FROM souscriptions WHERE statut_souscription IN ('valide', 'reconduite')")->fetchColumn();
            $totalSouscriptionsSoldees = (int)$db->query("SELECT COUNT(*) FROM souscriptions WHERE statut_souscription = 'solde'")->fetchColumn();
            $totalArticles = (int)$db->query("SELECT COUNT(*) FROM articles WHERE statut_article = 'actif'")->fetchColumn();

            $totalCotisations = (float)($db->query("SELECT COALESCE(SUM(montant_cautisation_client), 0) FROM cautisation_clients WHERE statut_cautisation_client != 'ennule'")->fetchColumn() ?: 0);
            $totalPaiements = (float)($db->query("SELECT COALESCE(SUM(montant_paiement), 0) FROM paiements WHERE statut_paiement = 'confirme'")->fetchColumn() ?: 0);
            $caEncaisse = $totalCotisations + $totalPaiements;

            $totalVersements = (float)($db->query("SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'valide'")->fetchColumn() ?: 0);
            $totalVersementsEnAttente = (float)($db->query("SELECT COALESCE(SUM(montant_versement), 0) FROM versements_commerciaux WHERE statut_versement = 'En attente'")->fetchColumn() ?: 0);

            $totalDepenses = (float)($db->query("SELECT COALESCE(SUM(montant_depense), 0) FROM depenses WHERE statut_depense != 'inactif'")->fetchColumn() ?: 0);

            $soldeNet = $caEncaisse - $totalDepenses;

            $totalDistributions = (int)$db->query("SELECT COUNT(*) FROM distributions")->fetchColumn();
            $totalDistributionsValidees = (int)$db->query("SELECT COUNT(*) FROM distributions WHERE statut_distribution = 'valide'")->fetchColumn();

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
                    ORDER BY c.id_cautisation_client DESC
                    LIMIT $limit";
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
                    ORDER BY v.id_versement DESC
                    LIMIT $limit";
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
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
                    ORDER BY d.id_depense DESC
                    LIMIT $limit";
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("ModelHome::getRecentDepenses error: " . $e->getMessage());
            return [];
        }
    }
}
