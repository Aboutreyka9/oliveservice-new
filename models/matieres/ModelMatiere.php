<?php

class ModelMatiere extends BaseModel
{
    protected string $table = 'matieres';
    protected string $primaryKey = 'id_matiere';
    protected ?string $statusField = 'statut_matiere';
    protected ?string $createdAtField = 'created_at_matiere';
}
