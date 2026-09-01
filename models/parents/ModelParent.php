<?php

class ModelParent extends BaseModel
{
    protected string $table = 'parents';
    protected string $primaryKey = 'id_parent';
    
    protected ?string $createdAtField = 'created_at_parent';
}
