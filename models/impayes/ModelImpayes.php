<?php

class ModelImpayes extends BaseModel
{
    protected string $table = 'relances_impayes';
    protected string $primaryKey = 'id_relance';
    protected ?string $createdAtField = 'created_at_relance';

    public function getAll(): array
    {
        try {
            $sql = "SELECT r.*, e.nom_etudiant, e.prenom_etudiant, e.matricule_etudiant 
                    FROM relances_impayes r
                    LEFT JOIN etudiants e ON r.etudiant_code = e.code_etudiant
                    ORDER BY r.id_relance DESC";
            return $this->getCon()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Get all relances_impayes: " . $e->getMessage());
            return [];
        }
    }
    
    public function getOverdueStudents()
    {
        $stmt = $this->getCon()->query("
            SELECT i.code_inscription, i.montant_scolarite_inscription, 
                   e.code_etudiant, e.matricule_etudiant, e.nom_etudiant, e.prenom_etudiant, e.telephone_etudiant,
                   c.libelle_classe,
                   COALESCE(p.nom_pere, p.nom_tuteur, p.nom_mere, 'Non renseigné') as nom_parent,
                   COALESCE(p.telephone_pere, p.telephone_tuteur, p.telephone_mere, e.telephone_etudiant) as tel_parent,
                   COALESCE(SUM(pay.montant_paiement), 0) as total_paye
            FROM inscriptions i
            JOIN etudiants e ON i.etudiant_code = e.code_etudiant
            LEFT JOIN classes c ON i.classe_code = c.code_classe
            LEFT JOIN parents p ON p.etudiant_code = e.code_etudiant
            LEFT JOIN paiements pay ON pay.inscription_code = i.code_inscription AND pay.statut_paiement != 'annule'
            GROUP BY i.id_inscription
            HAVING (i.montant_scolarite_inscription - total_paye) > 0
            ORDER BY e.nom_etudiant ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
