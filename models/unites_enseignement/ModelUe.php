<?php

class ModelUe extends BaseModel
{
    protected string $table = 'unites_enseignement';
    protected string $primaryKey = 'id_ue';
    protected ?string $statusField = 'statut_ue';
    protected ?string $createdAtField = 'created_at_ue';
}
