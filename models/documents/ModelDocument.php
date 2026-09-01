<?php

class ModelDocument extends BaseModel
{
    protected string $table = 'documents';
    protected string $primaryKey = 'id_document';
    protected ?string $statusField = 'statut_document';
    
}
