<?php

require_once __DIR__ . '/../BaseModel.php';

class ModelNotification extends BaseModel
{
    protected string $table = 'notifications';
    protected string $primaryKey = 'id_notification';

    /**
     * Crée une nouvelle notification
     */
    public function createNotification(array $data): ?string
    {
        try {
            $con = $this->getCon();
            $code = !empty($data['code_notification']) 
                ? $data['code_notification'] 
                : 'NOT-' . strtoupper(bin2hex(random_bytes(4))) . '-' . substr((string)time(), -4);

            $stmt = $con->prepare("
                INSERT INTO `notifications` (
                    `code_notification`,
                    `user_code`,
                    `role_target`,
                    `type_notification`,
                    `titre_notification`,
                    `message_notification`,
                    `reference_code`,
                    `url_notification`,
                    `lu_notification`,
                    `statut_notification`,
                    `etablissement_code`,
                    `zone_code`,
                    `annee_code`,
                    `created_at_notification`
                ) VALUES (
                    :code_notification,
                    :user_code,
                    :role_target,
                    :type_notification,
                    :titre_notification,
                    :message_notification,
                    :reference_code,
                    :url_notification,
                    :lu_notification,
                    :statut_notification,
                    :etablissement_code,
                    :zone_code,
                    :annee_code,
                    :created_at_notification
                )
            ");

            $stmt->execute([
                ':code_notification'       => $code,
                ':user_code'               => $data['user_code'] ?? null,
                ':role_target'             => $data['role_target'] ?? null,
                ':type_notification'       => $data['type_notification'] ?? 'cotisation',
                ':titre_notification'      => $data['titre_notification'] ?? 'Notification',
                ':message_notification'    => $data['message_notification'] ?? '',
                ':reference_code'          => $data['reference_code'] ?? null,
                ':url_notification'        => $data['url_notification'] ?? null,
                ':lu_notification'         => isset($data['lu_notification']) ? (int)$data['lu_notification'] : 0,
                ':statut_notification'     => $data['statut_notification'] ?? 'actif',
                ':etablissement_code'      => $data['etablissement_code'] ?? '',
                ':zone_code'               => $data['zone_code'] ?? null,
                ':annee_code'              => $data['annee_code'] ?? null,
                ':created_at_notification' => $data['created_at_notification'] ?? date('Y-m-d H:i:s')
            ]);

            return $code;
        } catch (Exception $e) {
            error_log('[ModelNotification::createNotification] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Construit les clauses WHERE et les paramètres pour le ciblage selon l'utilisateur et ses rôles
     */
    private function buildTargetScope(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null): array
    {
        $conds = [];
        $params = [];

        if (!empty($etabCode)) {
            $conds[] = "etablissement_code = :etab";
            $params[':etab'] = $etabCode;
        }

        $isAdmin = false;
        foreach ($roles as $r) {
            if (in_array($r, ['ROLE_SUPERADMIN', 'ROLE_ADMIN', 'ROLE_DIR_GENERAL'], true)) {
                $isAdmin = true;
                break;
            }
        }

        // Si l'utilisateur est admin, il a accès à toutes les notifications de son établissement
        if (!$isAdmin) {
            $targetConds = [];
            if (!empty($userCode)) {
                $targetConds[] = "user_code = :user_code";
                $params[':user_code'] = $userCode;
            }

            if (!empty($roles)) {
                $rolePlaceholders = [];
                foreach ($roles as $idx => $role) {
                    $ph = ":role_" . $idx;
                    $rolePlaceholders[] = $ph;
                    $params[$ph] = $role;
                }
                $targetConds[] = "role_target IN (" . implode(',', $rolePlaceholders) . ")";
            }

            // Notifications globales (sans user_code ni role_target)
            $targetConds[] = "(user_code IS NULL AND role_target IS NULL)";

            $conds[] = "(" . implode(' OR ', $targetConds) . ")";

            // Si commercial, filtrer sur sa zone si la notification précise une zone
            $isCommercial = in_array('ROLE_COMMERCIAL', $roles, true);
            if ($isCommercial && !empty($zoneCode)) {
                $conds[] = "(zone_code IS NULL OR zone_code = '' OR zone_code = :zone_code)";
                $params[':zone_code'] = $zoneCode;
            }
        }

        $whereClause = !empty($conds) ? ("WHERE " . implode(' AND ', $conds)) : "";
        return [$whereClause, $params];
    }

    /**
     * Nombre de notifications non lues pour l'utilisateur connecté
     */
    public function getUnreadCountForUser(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null): int
    {
        try {
            [$whereClause, $params] = $this->buildTargetScope($userCode, $roles, $etabCode, $zoneCode);
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "lu_notification = 0 AND statut_notification = 'actif'";

            $sql = "SELECT COUNT(*) FROM `{$this->table}` {$whereClause}";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        } catch (Exception $e) {
            error_log('[ModelNotification::getUnreadCountForUser] ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Récupère les notifications récentes pour le dropdown du header/nav
     */
    public function getRecentForUser(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null, int $limit = 8): array
    {
        try {
            [$whereClause, $params] = $this->buildTargetScope($userCode, $roles, $etabCode, $zoneCode);
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "statut_notification = 'actif'";

            $sql = "SELECT * FROM `{$this->table}` {$whereClause} ORDER BY created_at_notification DESC, id_notification DESC LIMIT :lim";
            $stmt = $this->getCon()->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelNotification::getRecentForUser] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupère toutes les notifications pour la vue d'ensemble `/notification/list`
     */
    public function getAllForUser(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null, int $limit = 200): array
    {
        try {
            [$whereClause, $params] = $this->buildTargetScope($userCode, $roles, $etabCode, $zoneCode);
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "statut_notification = 'actif'";

            $sql = "SELECT n.*, 
                           u.nom_user, u.prenom_user
                    FROM `{$this->table}` n
                    LEFT JOIN `users` u ON u.code_user = n.user_code
                    {$whereClause} 
                    ORDER BY n.created_at_notification DESC, n.id_notification DESC 
                    LIMIT :lim";
            $stmt = $this->getCon()->prepare($sql);
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log('[ModelNotification::getAllForUser] ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Statistiques des notifications pour l'utilisateur
     */
    public function getStats(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null): array
    {
        try {
            [$whereClause, $params] = $this->buildTargetScope($userCode, $roles, $etabCode, $zoneCode);
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "statut_notification = 'actif'";

            $sql = "
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN lu_notification = 0 THEN 1 ELSE 0 END) as non_lues,
                    SUM(CASE WHEN lu_notification = 1 THEN 1 ELSE 0 END) as lues,
                    SUM(CASE WHEN type_notification = 'cotisation' THEN 1 ELSE 0 END) as cotisations,
                    SUM(CASE WHEN type_notification = 'versement' THEN 1 ELSE 0 END) as versements,
                    SUM(CASE WHEN type_notification = 'souscription' THEN 1 ELSE 0 END) as souscriptions
                FROM `{$this->table}`
                {$whereClause}
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute($params);
            $res = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            return [
                'total'        => (int)($res['total'] ?? 0),
                'non_lues'     => (int)($res['non_lues'] ?? 0),
                'lues'         => (int)($res['lues'] ?? 0),
                'cotisations'  => (int)($res['cotisations'] ?? 0),
                'versements'   => (int)($res['versements'] ?? 0),
                'souscriptions'=> (int)($res['souscriptions'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log('[ModelNotification::getStats] ' . $e->getMessage());
            return [
                'total' => 0, 'non_lues' => 0, 'lues' => 0,
                'cotisations' => 0, 'versements' => 0, 'souscriptions' => 0
            ];
        }
    }

    /**
     * Marque une notification spécifique comme lue
     */
    public function markAsRead(int $id, ?string $userCode = null): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `{$this->table}` 
                SET `lu_notification` = 1 
                WHERE `{$this->primaryKey}` = ?
            ");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::markAsRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Bascule le statut lu/non lu d'une notification
     */
    public function toggleRead(int $id): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `{$this->table}` 
                SET `lu_notification` = CASE WHEN `lu_notification` = 1 THEN 0 ELSE 1 END 
                WHERE `{$this->primaryKey}` = ?
            ");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::toggleRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Marque toutes les notifications visibles par l'utilisateur comme lues
     */
    public function markAllAsRead(?string $userCode, array $roles, ?string $etabCode, ?string $zoneCode = null): bool
    {
        try {
            [$whereClause, $params] = $this->buildTargetScope($userCode, $roles, $etabCode, $zoneCode);
            $whereClause .= ($whereClause ? " AND " : "WHERE ") . "lu_notification = 0";

            $sql = "UPDATE `{$this->table}` SET `lu_notification` = 1 {$whereClause}";
            $stmt = $this->getCon()->prepare($sql);
            return $stmt->execute($params);
        } catch (Exception $e) {
            error_log('[ModelNotification::markAllAsRead] ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime / désactive une notification
     */
    public function deleteNotification(int $id): bool
    {
        try {
            $stmt = $this->getCon()->prepare("
                UPDATE `{$this->table}` 
                SET `statut_notification` = 'supprime' 
                WHERE `{$this->primaryKey}` = ?
            ");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log('[ModelNotification::deleteNotification] ' . $e->getMessage());
            return false;
        }
    }
}
