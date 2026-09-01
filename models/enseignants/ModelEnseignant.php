<?php

class ModelEnseignant extends BaseModel
{
    protected string $table = 'enseignants';
    protected string $primaryKey = 'id_enseignant';
    protected ?string $statusField = 'statut_enseignant';
    protected ?string $createdAtField = 'created_at_enseignant';
}
