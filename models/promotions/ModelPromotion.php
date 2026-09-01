<?php

class ModelPromotion extends BaseModel
{
    protected string $table = 'promotions';
    protected string $primaryKey = 'id_promo';
    protected ?string $statusField = 'statut_promo';
    protected ?string $createdAtField = 'created_at_promo';

    public function __construct()
    {
        parent::__construct();
    }

    public function getAllPromotions(): array
    {
        $sql = "
            SELECT p.*, COUNT(u.id_utilisation) AS total_utilisations
            FROM promotions p
            LEFT JOIN promotions_utilisations u ON p.code_promo = u.promo_code
            GROUP BY p.id_promo
            ORDER BY p.id_promo DESC
        ";
        $stmt = $this->pdo->getCon()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPromotionById(int $id): ?array
    {
        $stmt = $this->pdo->getCon()->prepare("SELECT * FROM promotions WHERE id_promo = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function createPromotion(array $data): array
    {
        $code = strtoupper(trim($data['code_promo'] ?? ''));
        if (empty($code)) {
            return ['success' => false, 'error' => 'Le code promo est obligatoire'];
        }

        // Vérifier doublon
        $stmtCheck = $this->pdo->getCon()->prepare("SELECT COUNT(*) FROM promotions WHERE code_promo = :code");
        $stmtCheck->execute([':code' => $code]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'Le code promo ' . $code . ' existe déjà'];
        }

        $now = Validator::dateActuelle();

        $stmt = $this->pdo->getCon()->prepare("
            INSERT INTO promotions (
                code_promo, description_promo, type_reduction, valeur_reduction,
                montant_minimum_commande, reduction_max, premiere_commande_uniquement,
                limite_utilisations_globale, limite_utilisations_par_client,
                date_debut, date_fin, statut_promo, created_at_promo, updated_at_promo
            ) VALUES (
                :code_promo, :description_promo, :type_reduction, :valeur_reduction,
                :montant_minimum_commande, :reduction_max, :premiere_commande_uniquement,
                :limite_utilisations_globale, :limite_utilisations_par_client,
                :date_debut, :date_fin, :statut_promo, :now1, :now2
            )
        ");

        $success = $stmt->execute([
            ':code_promo' => $code,
            ':description_promo' => !empty($data['description_promo']) ? trim($data['description_promo']) : null,
            ':type_reduction' => in_array($data['type_reduction'] ?? '', ['pourcentage', 'montant_fixe']) ? $data['type_reduction'] : 'pourcentage',
            ':valeur_reduction' => (float)($data['valeur_reduction'] ?? 0),
            ':montant_minimum_commande' => (float)($data['montant_minimum_commande'] ?? 0),
            ':reduction_max' => !empty($data['reduction_max']) ? (float)$data['reduction_max'] : null,
            ':premiere_commande_uniquement' => isset($data['premiere_commande_uniquement']) ? 1 : 0,
            ':limite_utilisations_globale' => !empty($data['limite_utilisations_globale']) ? (int)$data['limite_utilisations_globale'] : null,
            ':limite_utilisations_par_client' => !empty($data['limite_utilisations_par_client']) ? (int)$data['limite_utilisations_par_client'] : 1,
            ':date_debut' => !empty($data['date_debut']) ? $data['date_debut'] : null,
            ':date_fin' => !empty($data['date_fin']) ? $data['date_fin'] : null,
            ':statut_promo' => in_array($data['statut_promo'] ?? '', ['actif', 'inactif']) ? $data['statut_promo'] : 'actif',
            ':now1' => $now,
            ':now2' => $now
        ]);

        return $success ? ['success' => true] : ['success' => false, 'error' => 'Erreur lors de la création de la promotion'];
    }

    public function updatePromotion(int $id, array $data): array
    {
        $code = strtoupper(trim($data['code_promo'] ?? ''));
        if (empty($code)) {
            return ['success' => false, 'error' => 'Le code promo est obligatoire'];
        }

        // Vérifier doublon sauf lui-même
        $stmtCheck = $this->pdo->getCon()->prepare("SELECT COUNT(*) FROM promotions WHERE code_promo = :code AND id_promo != :id");
        $stmtCheck->execute([':code' => $code, ':id' => $id]);
        if ((int)$stmtCheck->fetchColumn() > 0) {
            return ['success' => false, 'error' => 'Un autre code promo porte déjà ce nom'];
        }

        $now = Validator::dateActuelle();

        $stmt = $this->pdo->getCon()->prepare("
            UPDATE promotions SET
                code_promo = :code_promo,
                description_promo = :description_promo,
                type_reduction = :type_reduction,
                valeur_reduction = :valeur_reduction,
                montant_minimum_commande = :montant_minimum_commande,
                reduction_max = :reduction_max,
                premiere_commande_uniquement = :premiere_commande_uniquement,
                limite_utilisations_globale = :limite_utilisations_globale,
                limite_utilisations_par_client = :limite_utilisations_par_client,
                date_debut = :date_debut,
                date_fin = :date_fin,
                statut_promo = :statut_promo,
                updated_at_promo = :now
            WHERE id_promo = :id
        ");

        $success = $stmt->execute([
            ':code_promo' => $code,
            ':description_promo' => !empty($data['description_promo']) ? trim($data['description_promo']) : null,
            ':type_reduction' => in_array($data['type_reduction'] ?? '', ['pourcentage', 'montant_fixe']) ? $data['type_reduction'] : 'pourcentage',
            ':valeur_reduction' => (float)($data['valeur_reduction'] ?? 0),
            ':montant_minimum_commande' => (float)($data['montant_minimum_commande'] ?? 0),
            ':reduction_max' => !empty($data['reduction_max']) ? (float)$data['reduction_max'] : null,
            ':premiere_commande_uniquement' => isset($data['premiere_commande_uniquement']) ? 1 : 0,
            ':limite_utilisations_globale' => !empty($data['limite_utilisations_globale']) ? (int)$data['limite_utilisations_globale'] : null,
            ':limite_utilisations_par_client' => !empty($data['limite_utilisations_par_client']) ? (int)$data['limite_utilisations_par_client'] : 1,
            ':date_debut' => !empty($data['date_debut']) ? $data['date_debut'] : null,
            ':date_fin' => !empty($data['date_fin']) ? $data['date_fin'] : null,
            ':statut_promo' => in_array($data['statut_promo'] ?? '', ['actif', 'inactif']) ? $data['statut_promo'] : 'actif',
            ':now' => $now,
            ':id' => $id
        ]);

        return $success ? ['success' => true] : ['success' => false, 'error' => 'Erreur lors de la mise à jour'];
    }

    public function toggleStatus(int $id): bool
    {
        $promo = $this->getPromotionById($id);
        if (!$promo) return false;
        $newStatus = $promo['statut_promo'] === 'actif' ? 'inactif' : 'actif';
        $stmt = $this->pdo->getCon()->prepare("UPDATE promotions SET statut_promo = :status, updated_at_promo = NOW() WHERE id_promo = :id");
        return $stmt->execute([':status' => $newStatus, ':id' => $id]);
    }

    public function deletePromotion(int $id): bool
    {
        $stmt = $this->pdo->getCon()->prepare("DELETE FROM promotions WHERE id_promo = :id");
        return $stmt->execute([':id' => $id]);
    }
}
