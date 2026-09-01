<?php

class ModelPanier extends BaseModel
{
    protected string $table = 'paniers';
    protected string $primaryKey = 'id_panier';
    protected ?string $statusField = 'statut_panier';
    protected ?string $createdAtField = 'created_at_panier';
}
