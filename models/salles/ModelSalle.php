<?php

class ModelSalle extends BaseModel
{
    protected string $table = 'salles';
    protected string $primaryKey = 'id_salle';
    protected ?string $statusField = 'statut_salle';
    protected ?string $createdAtField = 'created_at';
}
