<?php

abstract class BaseModel
{
    protected $pdo;
    protected string $table;
    protected string $primaryKey = 'id';
    protected ?string $statusField = null;
    protected ?string $createdAtField = null;

    public function __construct()
    {
        $this->pdo = new Database();
        if ($this->createdAtField === null) {
            $resolved = $this->resolveCreatedAtField($this->table);
            try {
                $cols = $this->pdo->getCon()->query("DESCRIBE `{$this->table}`")->fetchAll(PDO::FETCH_COLUMN);
                if (in_array($resolved, $cols)) {
                    $this->createdAtField = $resolved;
                } else {
                    $this->createdAtField = '';
                }
            } catch (Exception $e) {
                $this->createdAtField = '';
            }
        }
    }

    private function resolveCreatedAtField(string $table): string
    {
        $base = $table;
        if (substr($base, -3) === 'ies') {
            $base = substr($base, 0, -3) . 'y';
        } elseif (substr($base, -2) === 'es' && strlen($base) > 2) {
            $base = substr($base, 0, -1);
        } elseif (substr($base, -1) === 's' && strlen($base) > 1) {
            $base = substr($base, 0, -1);
        }
        return "created_at_{$base}";
    }

    public function getCon()
    {
        return $this->pdo->getCon();
    }

    public function getAll(): array
    {
        try {
            $where = "";
            $params = [];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $where = " WHERE etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params = [Context::etablissement(), Context::zone(), Context::annee()];
            }
            $orderBy = !empty($this->createdAtField) ? " ORDER BY {$this->createdAtField} DESC" : " ORDER BY {$this->primaryKey} DESC";
            $sql = "SELECT * FROM {$this->table}{$where}{$orderBy}";
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get all {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    public function getById(int $id): array
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $params = [$id];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $sql .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Get by id {$this->table}: " . $e->getMessage());
            return [];
        }
    }

    public function create(array $data): bool
    {
        try {
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                if (!isset($data['etablissement_code']) || $data['etablissement_code'] === '') {
                    $data['etablissement_code'] = Context::etablissement();
                }
                if (!isset($data['zone_code']) || $data['zone_code'] === '') {
                    $data['zone_code'] = Context::zone();
                }
                if (!isset($data['annee_code']) || $data['annee_code'] === '') {
                    $data['annee_code'] = Context::annee();
                }

                if (empty($data['etablissement_code']) || empty($data['zone_code']) || empty($data['annee_code'])) {
                    throw new Exception("Erreur d'insertion dans {$this->table} : L'établissement, la zone et l'année sont obligatoires et ne peuvent pas être null ou vides.");
                }
            }

            $fields = array_keys($data);
            $placeholders = array_map(fn($f) => ":$f", $fields);
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
            $stmt = $this->pdo->getCon()->prepare($sql);
            return $stmt->execute($data);
        } catch (Exception $e) {
            error_log("Create {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function update(array $data, ?int $id = null): bool
    {
        try {
            if ($id === null) {
                $id = $data['id'] ?? ($data[$this->primaryKey] ?? null);
            }
            if ($id === null) {
                error_log("Update {$this->table}: No ID provided in payload");
                return false;
            }
            unset($data['id']);
            unset($data[$this->primaryKey]);

            if (empty($data)) {
                return true;
            }

            // La clause SET ne doit contenir que les vraies colonnes passées dans $data
            $set = implode(', ', array_map(fn($f) => "`$f` = :$f", array_keys($data)));
            $params = $data;

            $where = "WHERE `{$this->primaryKey}` = :primary_key_id";
            $params['primary_key_id'] = $id;

            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $where .= " AND `etablissement_code` = :scoped_etab AND `zone_code` = :scoped_zone AND `annee_code` = :scoped_annee";
                $params['scoped_etab'] = Context::etablissement();
                $params['scoped_zone'] = Context::zone();
                $params['scoped_annee'] = Context::annee();
            }

            $sql = "UPDATE `{$this->table}` SET $set $where";
            $stmt = $this->pdo->getCon()->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log("Update {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function addIndexed(array $data, array $columns): bool
    {
        try {
            $placeholders = array_fill(0, count($columns), '?');
            $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
            return $this->pdo->getCon()->prepare($sql)->execute($data);
        } catch (Exception $e) {
            error_log("Insert indexed {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function updateIndexed(array $data, array $columns, int $id): bool
    {
        try {
            $set = implode(', ', array_map(fn($f) => "$f = ?", $columns));
            $sql = "UPDATE {$this->table} SET $set WHERE {$this->primaryKey} = ?";
            $values = array_merge($data, [$id]);
            return $this->pdo->getCon()->prepare($sql)->execute($values);
        } catch (Exception $e) {
            error_log("Update indexed {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function delete(int $id): bool
    {
        try {
            $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
            $params = [$id];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $sql .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            return $this->pdo->getCon()->prepare($sql)->execute($params);
        } catch (Exception $e) {
            error_log("Delete {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus(int $id): bool
    {
        try {
            $field = $this->statusField ?? "statut_{$this->table}";
            $where = "WHERE `{$this->primaryKey}` = ?";
            $params = [$id];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $where .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $sql = "UPDATE `{$this->table}` SET `{$field}` = CASE WHEN `{$field}` = 'actif' THEN 'inactif' ELSE 'actif' END $where";
            return $this->pdo->getCon()->prepare($sql)->execute($params);
        } catch (Exception $e) {
            error_log("Toggle status {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function updateStatus(int $id, string $status, ?string $statusCol = null): bool
    {
        try {
            $field = $statusCol ?? ($this->statusField ?? "statut_{$this->table}");
            $where = "WHERE `{$this->primaryKey}` = ?";
            $params = [$status, $id];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $where .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $sql = "UPDATE `{$this->table}` SET `{$field}` = ? $where";
            return $this->pdo->getCon()->prepare($sql)->execute($params);
        } catch (Exception $e) {
            error_log("Update status {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function getByElement(string $field, $val)
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE $field = ?";
            $params = [$val];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $sql .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: false;
        } catch (Exception $e) {
            error_log("Get by element {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function exists(string $field, $val): bool
    {
        return $this->getByElement($field, $val) !== false;
    }

    public function existsOther(string $field, $val, string $pkField, $pkVal): bool
    {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE $field = ? AND $pkField != ?";
            $params = [$val, $pkVal];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $sql .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            return $stmt->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Exists other {$this->table}: " . $e->getMessage());
            return false;
        }
    }

    public function getByStatus(string $status): array
    {
        try {
            $field = $this->statusField ?? "statut_{$this->table}";
            $where = "WHERE {$field} = ?";
            $params = [$status];
            if (in_array($this->table, Context::SCOPED_TABLES)) {
                $where .= " AND etablissement_code = ? AND zone_code = ? AND annee_code = ?";
                $params[] = Context::etablissement();
                $params[] = Context::zone();
                $params[] = Context::annee();
            }
            $orderBy = $this->createdAtField ? " ORDER BY {$this->createdAtField} DESC" : '';
            $sql = "SELECT * FROM {$this->table} {$where}{$orderBy}";
            $stmt = $this->pdo->getCon()->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $rows ?: [];
        } catch (Exception $e) {
            error_log("Get by status {$this->table}: " . $e->getMessage());
            return [];
        }
    }
}
