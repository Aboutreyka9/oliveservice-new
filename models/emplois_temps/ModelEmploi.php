<?php

class ModelEmploi extends BaseModel
{
    protected string $table = 'emplois_temps';
    protected string $primaryKey = 'id_emploi';
    protected ?string $statusField = 'statut_emploi';
    protected ?string $createdAtField = 'created_at_emploi';
}
