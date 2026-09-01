<?php

class ModelGalerie extends BaseModel
{
    protected string $table = 'galeries';
    protected string $primaryKey = 'id_galerie';
    protected ?string $statusField = 'statut_galerie';
    protected ?string $createdAtField = 'created_at_galerie';
}
