<?php

class ModelAbsence extends BaseModel
{
    protected string $table = 'absences';
    protected string $primaryKey = 'id_absence';
    
    protected ?string $createdAtField = 'created_at_absence';
}
