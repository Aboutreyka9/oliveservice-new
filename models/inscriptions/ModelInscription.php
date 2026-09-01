<?php

class ModelInscription extends BaseModel
{
    protected string $table = 'inscriptions';
    protected string $primaryKey = 'id_inscription';
    protected ?string $statusField = 'statut_inscription';
    protected ?string $createdAtField = 'created_at_inscription';
}
