<?php

class ModelClotureCaisse extends BaseModel
{
    protected string $table = 'clotures_caisse';
    protected string $primaryKey = 'id_cloture';
    protected ?string $statusField = 'statut_cloture';
    protected ?string $createdAtField = 'created_at_cloture';
}
