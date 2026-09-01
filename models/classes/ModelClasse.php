<?php

class ModelClasse extends BaseModel
{
    protected string $table = 'classes';
    protected string $primaryKey = 'id_classe';
    protected ?string $statusField = 'statut_classe';
    protected ?string $createdAtField = 'created_at_classe';
}
