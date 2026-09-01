<?php

class ModelNiveau extends BaseModel
{
    protected string $table = 'niveaux';
    protected string $primaryKey = 'id_niveau';
    protected ?string $statusField = 'statut_niveau';
    protected ?string $createdAtField = 'created_at_niveau';
}
