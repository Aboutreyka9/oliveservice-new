<?php

class ModelUserPressing extends BaseModel
{
    protected string $table = 'users_pressings';
    protected string $primaryKey = 'id_user_pressing';
    protected ?string $statusField = 'statut_user_pressing';
}
