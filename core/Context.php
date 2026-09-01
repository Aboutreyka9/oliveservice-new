<?php

class Context
{
    public static function annee(): string
    {
        return $_SESSION['annee_active_code'] ?? '0GklBk07waYoLB6pHwY';
    }

    public static function etablissement(): string
    {
        return $_SESSION['etablissement_active_code'] ?? '5454544456';
    }

    public static function zone(): ?string
    {
        return $_SESSION['zone_active_code'] ?? null;
    }

    public static function user(): ?string
    {
        return $_SESSION[USERS_AUTH]['code_user'] ?? null;
    }

    public static function userId(): ?int
    {
        return $_SESSION[USERS_AUTH]['id_user'] ?? null;
    }

    public static function role(): string
    {
        return $_SESSION[USERS_AUTH]['role_code'] ?? 'ROLE_USER';
    }

    public static function isSuperAdmin(): bool
    {
        return in_array(self::role(), ['ROLE_SUPERADMIN', 'ROLE_DIR_GENERAL'], true);
    }

    public static function all(): array
    {
        return [
            'annee_code' => self::annee(),
            'etablissement_code' => self::etablissement(),
            'zone_code' => self::zone(),
            'user_code' => self::user(),
            'role_code' => self::role(),
            'is_super_admin' => self::isSuperAdmin(),
        ];
    }

    public static function applyTo(array &$data, array $fields = ['annee_code', 'etablissement_code', 'zone_code', 'user_code']): void
    {
        $map = [
            'annee_code' => self::annee(),
            'etablissement_code' => self::etablissement(),
            'zone_code' => self::zone(),
            'user_code' => self::user(),
        ];

        foreach ($fields as $field) {
            if (array_key_exists($field, $map) && (!isset($data[$field]) || $data[$field] === '' || $data[$field] === null)) {
                $data[$field] = $map[$field];
            }
        }
    }
}
