<?php

class ModelPanierDetail extends BaseModel
{
    protected string $table = 'panier_details';
    protected string $primaryKey = 'id_panier_detail';
    protected ?string $createdAtField = 'created_at_panier_detail';
}
