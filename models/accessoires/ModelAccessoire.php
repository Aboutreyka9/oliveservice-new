<?php

class ModelAccessoire extends BaseModel
{
    protected string $table = 'accessoires';
    protected string $primaryKey = 'id_accessoire';
    protected ?string $statusField = 'statut_accessoire';
    protected ?string $createdAtField = 'created_at_accessoire';

    public function getDistributions(string $filter = 'all'): array
    {
        $db = $this->getCon();
        $where = "WHERE (ai.statut_accessoire_inscription = 'actif' OR ai.statut_accessoire_inscription IS NULL)";
        $params = [];

        if ($filter === 'en_attente') {
            $where .= " AND ai.etat_retrait = 'en_attente'";
        } elseif ($filter === 'retire') {
            $where .= " AND ai.etat_retrait = 'retire'";
        }

        $sql = "
            SELECT ai.*, 
                   a.libelle_accessoire,
                   COALESCE(e.matricule_etudiant, '-') as matricule_etudiant,
                   COALESCE(e.nom_etudiant, '') as nom_etudiant,
                   COALESCE(e.prenom_etudiant, '') as prenom_etudiant,
                   COALESCE(e.telephone_etudiant, '-') as telephone_etudiant,
                   CONCAT(COALESCE(e.nom_etudiant, ''), ' ', COALESCE(e.prenom_etudiant, '')) as nom_complet,
                   COALESCE(c.libelle_classe, 'Non assignée') as libelle_classe
            FROM accessoire_inscription ai
            LEFT JOIN accessoires a ON a.code_accessoire = ai.accessoire_code
            LEFT JOIN inscriptions i ON i.code_inscription = ai.inscription_code
            LEFT JOIN etudiants e ON e.code_etudiant = i.etudiant_code
            LEFT JOIN classes c ON c.code_classe = i.classe_code
            {$where}
            GROUP BY ai.id_accessoire_inscription
            ORDER BY ai.id_accessoire_inscription DESC
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStats(): array
    {
        $db = $this->getCon();
        $total = (int)$db->query("SELECT COUNT(*) FROM accessoire_inscription WHERE statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL")->fetchColumn();
        $enAttente = (int)$db->query("SELECT COUNT(*) FROM accessoire_inscription WHERE (statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'en_attente'")->fetchColumn();
        $retire = (int)$db->query("SELECT COUNT(*) FROM accessoire_inscription WHERE (statut_accessoire_inscription = 'actif' OR statut_accessoire_inscription IS NULL) AND etat_retrait = 'retire'")->fetchColumn();
        $totalTypes = (int)$db->query("SELECT COUNT(*) FROM accessoires WHERE statut_accessoire = 'actif'")->fetchColumn();
        $taux = $total > 0 ? round(($retire / $total) * 100) : 0;

        return [
            'total' => $total,
            'en_attente' => $enAttente,
            'retire' => $retire,
            'taux' => $taux,
            'total_types' => $totalTypes
        ];
    }
}
