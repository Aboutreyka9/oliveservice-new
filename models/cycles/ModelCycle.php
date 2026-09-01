<?php

class ModelCycle extends BaseModel
{
    protected string $table = 'cycles';
    protected string $primaryKey = 'id_cycle';
    protected ?string $statusField = 'statut_cycle';
    protected ?string $createdAtField = 'created_at_cycle';
}
