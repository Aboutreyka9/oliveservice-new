<?php

class ModelCommandeDetail extends BaseModel
{
    protected string $table = 'commande_details';
    protected string $primaryKey = 'id_commande_detail';
    protected ?string $statusField = null;
    protected ?string $createdAtField = 'created_at_commande_detail';

    public function __construct()
    {
        parent::__construct();
    }

    public function getByCommande(string $commandeCode): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE commande_code = ? ORDER BY created_at_commande_detail DESC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$commandeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommandeDetail::getByCommande] ' . $e->getMessage());
            return [];
        }
    }

    public function getByCommandeWithArticles(string $commandeCode): array
    {
        try {
            $sql = "SELECT cd.*, a.libelle_article, s.libelle_service 
                    FROM {$this->table} cd
                    LEFT JOIN articles_pressings a ON a.code_article = cd.article_code
                    LEFT JOIN services s ON s.code_service = cd.service_code
                    WHERE cd.commande_code = ? 
                    ORDER BY cd.created_at_commande_detail ASC";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$commandeCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelCommandeDetail::getByCommandeWithArticles] ' . $e->getMessage());
            return $this->getByCommande($commandeCode);
        }
    }

    public function getByCommandeCode(string $commandeCode): array
    {
        return $this->getByCommandeWithArticles($commandeCode);
    }
}
