<?php

class ModelFiliere extends BaseModel
{
    protected string $table = 'filieres';
    protected string $primaryKey = 'id_filiere';
    protected ?string $statusField = 'statut_filiere';
    protected ?string $createdAtField = 'created_at_filiere';
}
