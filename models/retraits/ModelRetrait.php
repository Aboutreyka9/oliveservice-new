<?php

class ModelRetrait extends BaseModel
{
    protected string $table = 'retraits_pressings';
    protected string $primaryKey = 'id_retrait';
    protected ?string $statusField = 'statut_retrait';
    protected ?string $createdAtField = 'created_at_retrait';

    public function getSoldeDetails(string $pressingCode): array
    {
        $pdo = $this->getCon();

        // 1. Total encaissé en ligne via GeniusPay / Mobile Money sur les commandes du pressing
        $sqlOnline = "
            SELECT COALESCE(SUM(p.montant_paiement), 0) as total_online
            FROM " . TABLES::PAIEMENTS . " p
            INNER JOIN " . TABLES::COMMANDES . " c ON c.code_commande = p.commande_code
            WHERE c.pressing_code = ?
              AND p.statut_paiement = 'valide'
              AND p.mode_paiement IN ('wave', 'orange_money', 'mtn_money', 'moov_money', 'carte_bancaire', 'geniuspay', 'online')
        ";
        $stmt = $pdo->prepare($sqlOnline);
        $stmt->execute([$pressingCode]);
        $totalOnline = (float) $stmt->fetchColumn();

        // 2. Total des retraits déjà complétés
        $sqlCompleted = "
            SELECT COALESCE(SUM(montant_demande), 0)
            FROM retraits_pressings
            WHERE pressing_code = ? AND statut_retrait = 'complete'
        ";
        $stmt = $pdo->prepare($sqlCompleted);
        $stmt->execute([$pressingCode]);
        $totalCompleted = (float) $stmt->fetchColumn();

        // 3. Total des retraits en cours (bloqués en attente d'approbation ou de virement)
        $sqlPending = "
            SELECT COALESCE(SUM(montant_demande), 0)
            FROM retraits_pressings
            WHERE pressing_code = ? AND statut_retrait IN ('en_attente', 'approuve')
        ";
        $stmt = $pdo->prepare($sqlPending);
        $stmt->execute([$pressingCode]);
        $totalPending = (float) $stmt->fetchColumn();

        $soldeDisponible = max(0, $totalOnline - ($totalCompleted + $totalPending));

        return [
            'total_online' => $totalOnline,
            'total_completed' => $totalCompleted,
            'total_pending' => $totalPending,
            'solde_disponible' => $soldeDisponible,
        ];
    }

    public function getRetraitsByPressing(string $pressingCode): array
    {
        $sql = "
            SELECT r.*, p.libelle_pressing
            FROM retraits_pressings r
            LEFT JOIN " . TABLES::PRESSINGS . " p ON p.code_pressing = r.pressing_code
            WHERE r.pressing_code = ?
            ORDER BY r.created_at_retrait DESC
        ";
        $stmt = $this->getCon()->prepare($sql);
        $stmt->execute([$pressingCode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAllRetraits(): array
    {
        $sql = "
            SELECT r.*, p.libelle_pressing, p.telephone_pressing
            FROM retraits_pressings r
            LEFT JOIN " . TABLES::PRESSINGS . " p ON p.code_pressing = r.pressing_code
            ORDER BY r.created_at_retrait DESC
        ";
        return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createRetrait(string $pressingCode, float $montant, string $operateur, string $telephone, ?string $nomBeneficiaire = null): array
    {
        $config = GeniusPayConfig::get();
        $minRetrait = (float)($config['minimum_retrait'] ?? 2000);

        if ($montant < $minRetrait) {
            return ['success' => false, 'message' => "Le montant minimum de retrait est de " . number_format($minRetrait, 0, ',', ' ') . " FCFA."];
        }

        $solde = $this->getSoldeDetails($pressingCode);
        if ($montant > $solde['solde_disponible']) {
            return ['success' => false, 'message' => "Solde insuffisant. Votre solde disponible est de " . number_format($solde['solde_disponible'], 0, ',', ' ') . " FCFA."];
        }

        $codeRetrait = 'RET-' . strtoupper(bin2hex(random_bytes(3)));
        $frais = 0.00;
        $montantNet = $montant - $frais;

        $stmt = $this->getCon()->prepare("
            INSERT INTO retraits_pressings (
                code_retrait, pressing_code, montant_demande, frais_retrait,
                montant_net, operateur_retrait, telephone_beneficiaire,
                nom_beneficiaire, statut_retrait, created_at_retrait
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'en_attente', NOW())
        ");

        $ok = $stmt->execute([
            $codeRetrait,
            $pressingCode,
            $montant,
            $frais,
            $montantNet,
            $operateur,
            $telephone,
            $nomBeneficiaire
        ]);

        if ($ok) {
            return [
                'success' => true,
                'code_retrait' => $codeRetrait,
                'message' => 'Demande de retrait enregistrée avec succès.'
            ];
        }

        return ['success' => false, 'message' => 'Erreur lors de la création de la demande de retrait.'];
    }

    public function changerStatutRetrait(int $idRetrait, string $nouveauStatut, ?string $reference = null, ?string $motif = null): bool
    {
        $sql = "UPDATE retraits_pressings SET statut_retrait = ?, reference_geniuspay = COALESCE(?, reference_geniuspay), motif_rejet = COALESCE(?, motif_rejet) WHERE id_retrait = ?";
        $stmt = $this->getCon()->prepare($sql);
        return $stmt->execute([$nouveauStatut, $reference, $motif, $idRetrait]);
    }
}
