<?php

class ModelTranche extends BaseModel
{
    protected string $table = 'tranches_scolarite';
    protected string $primaryKey = 'id_tranche';
    protected ?string $statusField = 'statut_tranche';
    protected ?string $createdAtField = 'created_at_tranche';

    public function getAll(): array
    {
        $sql = "
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN filieres f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN niveaux n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN annees a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            ORDER BY t.id_tranche DESC
        ";
        return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id): array
    {
        $stmt = $this->getCon()->prepare("
            SELECT t.*, 
                   s.montant_scolarite, 
                   s.affectation_etat,
                   f.libelle_filiere, 
                   n.libelle_niveau, 
                   a.libelle_annee
            FROM tranches_scolarite t
            LEFT JOIN scolarites s ON t.scolarite_code = s.code_scolarite
            LEFT JOIN filieres f ON (s.filiere_code = f.code_filiere OR t.filiere_code = f.code_filiere)
            LEFT JOIN niveaux n ON (s.niveau_code = n.code_niveau OR t.niveau_code = n.code_niveau)
            LEFT JOIN annees a ON (s.annee_code = a.code_annee OR t.annee_code = a.code_annee)
            WHERE t.id_tranche = ?
            LIMIT 1
        ");
        $stmt->execute([(int)$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }
}
