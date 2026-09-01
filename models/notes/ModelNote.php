<?php

class ModelNote extends BaseModel
{
    protected string $table = 'notes';
    protected string $primaryKey = 'id_note';
    protected ?string $statusField = 'statut_note';
    protected ?string $createdAtField = 'created_at_note';
}
