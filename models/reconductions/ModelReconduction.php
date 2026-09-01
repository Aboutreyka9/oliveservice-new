<?php

class ModelReconduction extends BaseModel
{
    protected string $table = 'souscriptions';
    protected string $primaryKey = 'id_souscription';
    protected ?string $statusField = 'statut_souscription';
    protected ?string $createdAtField = 'created_at_souscription';
}
