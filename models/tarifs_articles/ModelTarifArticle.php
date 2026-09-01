<?php

class ModelTarifArticle extends BaseModel
{
    protected string $table = 'tarifs_articles';
    protected string $primaryKey = 'id_tarif';
    protected ?string $statusField = 'statut_tarif';
    protected ?string $createdAtField = 'created_at_tarif';

    public function getByPressing(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT 
                    t.*, 
                    COALESCE(a.libelle_article, 'Article') as libelle_article,
                    COALESCE(s.libelle_service, 'Service') as libelle_service,
                    COALESCE(cat.libelle_categorie_article, 'Catégorie') as libelle_categorie
                FROM {$this->table} t
                LEFT JOIN " . TABLES::ARTICLES_PRESSINGS . " a ON t.article_code = a.code_article
                LEFT JOIN " . TABLES::SERVICES . " s ON t.service_code = s.code_service
                LEFT JOIN " . TABLES::CATEGORIES_ARTICLES . " cat ON a.categorie_article_code = cat.code_categorie_article
                WHERE t.pressing_code = ?
                ORDER BY cat.libelle_categorie_article ASC, a.libelle_article ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelTarifArticle::getByPressing] ' . $e->getMessage());
            return [];
        }
    }

    public function getAllWithDetails(): array
    {
        try {
            $sql = "
                SELECT 
                    t.*, 
                    COALESCE(p.libelle_pressing, t.pressing_code) as libelle_pressing,
                    COALESCE(a.libelle_article, 'Article') as libelle_article,
                    COALESCE(s.libelle_service, 'Service') as libelle_service,
                    COALESCE(cat.libelle_categorie_article, 'Catégorie') as libelle_categorie
                FROM {$this->table} t
                LEFT JOIN " . TABLES::PRESSINGS . " p ON t.pressing_code = p.code_pressing
                LEFT JOIN " . TABLES::ARTICLES_PRESSINGS . " a ON t.article_code = a.code_article
                LEFT JOIN " . TABLES::SERVICES . " s ON t.service_code = s.code_service
                LEFT JOIN " . TABLES::CATEGORIES_ARTICLES . " cat ON a.categorie_article_code = cat.code_categorie_article
                ORDER BY p.libelle_pressing ASC, cat.libelle_categorie_article ASC, a.libelle_article ASC
            ";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelTarifArticle::getAllWithDetails] ' . $e->getMessage());
            return [];
        }
    }
}
