<?php

class ModelEvenement extends BaseModel
{
    protected string $table = 'evenements';
    protected string $primaryKey = 'id_evenement';
    protected ?string $statusField = 'statut_evenement';
    protected ?string $createdAtField = 'date_creation_evenement';
}
