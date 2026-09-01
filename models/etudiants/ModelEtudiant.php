<?php

class ModelEtudiant extends BaseModel
{
    protected string $table = 'etudiants';
    protected string $primaryKey = 'id_etudiant';
    protected ?string $statusField = 'statut_etudiant';
    protected ?string $createdAtField = 'created_at_etudiant';
}
