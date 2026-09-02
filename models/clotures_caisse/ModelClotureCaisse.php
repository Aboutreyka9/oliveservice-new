<?php

class ModelClotureCaisse extends BaseModel
{
    protected string $table = 'caisses';
    protected string $primaryKey = 'id_caisse';
    protected ?string $statusField = 'decission_caisse';
    protected ?string $createdAtField = 'created_at_caisse';
}
