<?php

class ModelQuartier extends BaseModel
{
    protected string $table = 'quartiers';
    protected string $primaryKey = 'id_quartier';
    protected ?string $statusField = 'statut_quartier';
    protected ?string $createdAtField = 'created_at_quartier';
}
