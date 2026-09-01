<?php

class ModelSemestre extends BaseModel
{
    protected string $table = 'semestres';
    protected string $primaryKey = 'id_semestre';
    protected ?string $statusField = 'statut_semestre';
    protected ?string $createdAtField = 'created_at_semestre';
}
