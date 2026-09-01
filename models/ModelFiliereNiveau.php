<?php

class ModelFiliereNiveau
{
    private $con;

    public function __construct()
    {
        $this->con = (new Database())->getCon();
    }

    public function getCon()
    {
        return $this->con;
    }

    public function getAll()
    {
        $sql = "
            SELECT fn.*, f.libelle_filiere, n.libelle_niveau 
            FROM filiere_niveaux fn
            LEFT JOIN filieres f ON fn.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON fn.niveau_code = n.code_niveau
            ORDER BY f.libelle_filiere ASC, n.libelle_niveau ASC
        ";
        return $this->con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id)
    {
        $stmt = $this->con->prepare("
            SELECT fn.*, f.libelle_filiere, n.libelle_niveau 
            FROM filiere_niveaux fn
            LEFT JOIN filieres f ON fn.filiere_code = f.code_filiere
            LEFT JOIN niveaux n ON fn.niveau_code = n.code_niveau
            WHERE fn.id_filiere_niveau = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create(array $data)
    {
        $cols = array_keys($data);
        $sql = "INSERT INTO filiere_niveaux (" . implode(', ', array_map(function($c){ return "`$c`"; }, $cols)) . ") 
                VALUES (" . implode(', ', array_fill(0, count($cols), '?')) . ")";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute(array_values($data));
    }

    public function update(array $data, $id)
    {
        $sets = [];
        $params = [];
        foreach ($data as $col => $val) {
            $sets[] = "`$col` = ?";
            $params[] = $val;
        }
        $params[] = $id;
        $sql = "UPDATE filiere_niveaux SET " . implode(', ', $sets) . " WHERE id_filiere_niveau = ?";
        $stmt = $this->con->prepare($sql);
        return $stmt->execute($params);
    }

    public function toggleStatus($id)
    {
        $item = $this->getById($id);
        if (!$item) return false;
        $newStatus = ($item['statut_filiere_niveau'] === 'actif') ? 'inactif' : 'actif';
        $stmt = $this->con->prepare("UPDATE filiere_niveaux SET statut_filiere_niveau = ? WHERE id_filiere_niveau = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}