<?php

class ModelRolePermission extends BaseModel
{
    protected string $table = 'roles_permissions';
    protected string $primaryKey = 'id_role_permission';
    protected ?string $statusField = 'statut_role_permission';
}
