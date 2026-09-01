<?php

class ModelFavori extends BaseModel
{
    protected string $table = 'favoris';
    protected string $primaryKey = 'id_favori';
    protected ?string $statusField = 'statut_favori';
    protected ?string $createdAtField = 'created_at_favori';
}
