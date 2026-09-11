<?php

class Context
{
    public const SCOPED_TABLES = [
        'caisses',
        'cautisation_clients',
        'depenses',
        'distributions',
        'pack_articles',
        'pack_souscriptions',
        'packs',
        'sessions',
        'souscriptions',
        'versements_commerciaux'
    ];

    public static function annee(): string
    {
        $code = $_SESSION['annee_active_code'] ?? '';
        if (empty($code) || is_numeric($code)) {
            try {
                $db = (new Database())->getCon();
                $stmt = $db->query("SELECT code_annee, libelle_annee FROM annees WHERE statut_annee = 'actif' ORDER BY id_annee DESC LIMIT 1");
                $active = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
                if ($active && !empty($active['code_annee'])) {
                    $_SESSION['annee_active_code'] = $active['code_annee'];
                    $_SESSION['annee_active_libelle'] = $active['libelle_annee'];
                    return $active['code_annee'];
                }
            } catch (\Throwable $e) {
                // Erreur silencieuse
            }
        }
        return $code;
    }

    public static function etablissement(): string
    {
        return $_SESSION['etablissement_active_code'] ?? '';
    }

    public static function zone(): string
    {
        return $_SESSION['zone_active_code'] ?? '';
    }

    public static function user(): ?string
    {
        return $_SESSION[USERS_AUTH]['code_user'] ?? ($_SESSION['code_user'] ?? null);
    }

    public static function userId(): ?int
    {
        return $_SESSION[USERS_AUTH]['id_user'] ?? null;
    }

    public static function roles(): array
    {
        $roles = $_SESSION[USERS_AUTH]['roles'] ?? [];
        if (empty($roles)) {
            $singleRole = $_SESSION[USERS_AUTH]['role_code'] ?? ($_SESSION['role_code'] ?? '');
            if (!empty($singleRole)) {
                $roles = [$singleRole];
            }
        }
        if (is_string($roles)) {
            $roles = [$roles];
        }
        return array_values(array_unique(array_filter($roles)));
    }

    public static function role(): string
    {
        $roles = self::roles();
        return $roles[0] ?? 'ROLE_COMMERCIAL';
    }

    public static function is(string|array $roles): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $targetRoles = is_array($roles) ? $roles : [$roles];
        return !empty(array_intersect(self::roles(), $targetRoles));
    }

    public static function isSuperAdmin(): bool
    {
        return !empty(array_intersect(self::roles(), ['ROLE_SUPERADMIN', 'ROLE_ADMIN', 'ROLE_DIR_GENERAL']));
    }

    public static function isCommercial(): bool
    {
        return in_array('ROLE_COMMERCIAL', self::roles(), true);
    }

    public static function isGestionnaire(): bool
    {
        return in_array('ROLE_GESTIONNAIRE', self::roles(), true);
    }

    public static function isFinance(): bool
    {
        return in_array('ROLE_FINANCE', self::roles(), true) || self::can('FINANCE_VALIDATE_VERSEMENT');
    }

    public static function isAdmin(): bool
    {
        return self::isSuperAdmin();
    }

    public static function hasJoker(): bool
    {
        $roles = self::roles();
        if (in_array('ROLE_SUPERADMIN', $roles, true) || in_array('ROLE_DIR_GENERAL', $roles, true)) {
            return true;
        }
        $perms = $_SESSION['permissions'] ?? self::permissions();
        return in_array('MAIN_ACCESS', $perms, true);
    }

    public static function permissions(): array
    {
        if (self::isSuperAdmin()) {
            return ['*'];
        }

        if (isset($_SESSION['user_permissions']) && is_array($_SESSION['user_permissions'])) {
            return $_SESSION['user_permissions'];
        }

        $roles = self::roles();
        if (empty($roles)) {
            return [];
        }

        try {
            $db = (new Database())->getCon();
            $inClause = implode(',', array_fill(0, count($roles), '?'));
            $stmt = $db->prepare("
                SELECT DISTINCT rp.permission_code 
                FROM role_permissions rp
                JOIN permissions p ON rp.permission_code = p.code_permission
                WHERE rp.role_code IN ($inClause)
                  AND p.statut_permission = 'actif'
            ");
            $stmt->execute($roles);
            $perms = $stmt->fetchAll(PDO::FETCH_COLUMN) ?: [];
            $_SESSION['user_permissions'] = $perms;
            return $perms;
        } catch (Exception $e) {
            error_log("Context::permissions error: " . $e->getMessage());
            return [];
        }
    }

    public static function hasPermission(string $perm): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        $perms = self::permissions();
        return in_array('*', $perms, true) || in_array($perm, $perms, true);
    }

    public static function hasAnyPermission(array $perms): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        foreach ($perms as $p) {
            if (self::hasPermission($p)) {
                return true;
            }
        }
        return false;
    }

    public static function can(string|array $perms, array $allowedRoles = []): bool
    {
        if (self::isSuperAdmin()) {
            return true;
        }
        if (!empty($allowedRoles) && !empty(array_intersect(self::roles(), $allowedRoles))) {
            return true;
        }
        if (is_array($perms)) {
            return self::hasAnyPermission($perms);
        }
        return self::hasPermission($perms);
    }

    public static function all(): array
    {
        return [
            'annee_code' => self::annee(),
            'etablissement_code' => self::etablissement(),
            'zone_code' => self::zone(),
            'user_code' => self::user(),
            'role_code' => self::role(),
            'roles' => self::roles(),
            'permissions' => self::permissions(),
            'is_super_admin' => self::isSuperAdmin(),
        ];
    }

    /**
     * Applique automatiquement le filtrage selon le rôle connecté :
     * - Commercial : Filtré strictement sur user_code + etablissement_code + annee_code
     * - Gestionnaire : Filtré sur etablissement_code + zone_code (si présente) + annee_code
     * - Finance : Filtré sur etablissement_code + annee_code
     * - Admin : Filtré facultativement sur etablissement_code
     */
    public static function applyScopeSQL(string $tableAlias, array &$conditions, array &$params, bool $userFieldAsCommercial = true): void
    {
        $prefix = !empty($tableAlias) ? rtrim($tableAlias, '.') . '.' : '';

        // Établissement
        if (self::etablissement()) {
            $conditions[] = "{$prefix}etablissement_code = ?";
            $params[] = self::etablissement();
        }

        // Si Commercial terrain -> Filtrer obligatoirement sur son code_user
        if (self::isCommercial()) {
            $userCol = $userFieldAsCommercial ? 'user_code' : 'commercial_code';
            $conditions[] = "{$prefix}{$userCol} = ?";
            $params[] = self::user();
        }

        // Si Gestionnaire -> Filtrer par zone si définie
        if (self::isGestionnaire() && self::zone()) {
            $conditions[] = "{$prefix}zone_code = ?";
            $params[] = self::zone();
        }

        // Année d'activité
        if (self::annee()) {
            $conditions[] = "{$prefix}annee_code = ?";
            $params[] = self::annee();
        }
    }

    /**
     * Applique systématiquement les 3 filtres : etablissement_code, zone_code, annee_code
     * (requis pour les 10 tables cibles) et optionnellement le filtre RBAC user_code.
     */
    public static function applyTripleFilter(string $tableAlias, ?array &$conditions, array &$params, bool $applyUserScope = false, bool $userFieldAsCommercial = true): void
    {
        $conditions = $conditions ?? [];
        $prefix = !empty($tableAlias) ? rtrim($tableAlias, '.') . '.' : '';

        $etab = self::etablissement();
        if (!empty($etab)) {
            $conditions[] = "{$prefix}etablissement_code = ?";
            $params[] = $etab;
        }

        $zone = self::zone();
        if (!empty($zone)) {
            $conditions[] = "{$prefix}zone_code = ?";
            $params[] = $zone;
        }

        $annee = self::annee();
        if (!empty($annee)) {
            $conditions[] = "{$prefix}annee_code = ?";
            $params[] = $annee;
        }

        if ($applyUserScope && self::isCommercial()) {
            $userCol = $userFieldAsCommercial ? 'user_code' : 'commercial_code';
            $conditions[] = "({$prefix}{$userCol} = ? OR {$prefix}user_code = ?)";
            $params[] = self::user();
            $params[] = self::user();
        }
    }

    /**
     * Retourne une clause SQL complète avec les 3 filtres
     */
    public static function getTripleFilterSQL(string $tableAlias = '', array &$params = [], string $conjunction = 'AND', bool $applyUserScope = false, bool $userFieldAsCommercial = true): string
    {
        $conditions = [];
        self::applyTripleFilter($tableAlias, $conditions, $params, $applyUserScope, $userFieldAsCommercial);
        if (empty($conditions)) {
            return '';
        }
        return ' ' . trim($conjunction) . ' ' . implode(' AND ', $conditions);
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
