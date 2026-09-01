<?php

class ModelPressing extends BaseModel
{
    protected string $table = 'pressings';
    protected string $primaryKey = 'id_pressing';
    protected ?string $statusField = 'statut_pressing';
    protected ?string $createdAtField = 'created_at_pressing';

    public function getByCode(string $code): ?array
    {
        try {
            $stmt = $this->getCon()->prepare("SELECT * FROM {$this->table} WHERE code_pressing = ? LIMIT 1");
            $stmt->execute([$code]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log('[ModelPressing::getByCode] ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Statistiques globales du pressing pour le tableau de bord 360°
     */
    public function getPressingStats(string $pressingCode): array
    {
        try {
            // Total commandes & Total CA
            $sql = "
                SELECT 
                    COUNT(*) as total_commandes,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande = 'livree' THEN montant_total_commande ELSE 0 END), 0) as ca_total,
                    COUNT(DISTINCT client_code) as total_clients,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande NOT IN ('livree', 'refusee', 'annulee') THEN 1 ELSE 0 END), 0) as commandes_en_cours,
                    COALESCE(SUM(CASE WHEN statut_suivi_commande = 'creee' THEN 1 ELSE 0 END), 0) as commandes_nouvelles
                FROM " . TABLES::COMMANDES . "
                WHERE pressing_code = ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

            // Total articles tarifés
            $stmtT = $this->getCon()->prepare("SELECT COUNT(*) FROM " . TABLES::TARIFS_ARTICLES . " WHERE pressing_code = ? AND statut_tarif = 'actif'");
            $stmtT->execute([$pressingCode]);
            $stats['total_tarifs'] = (int)$stmtT->fetchColumn();

            // Total livreurs rattachés
            $stmtL = $this->getCon()->prepare("SELECT COUNT(DISTINCT livreur_code) FROM " . TABLES::MISSIONS . " m JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande WHERE c.pressing_code = ?");
            $stmtL->execute([$pressingCode]);
            $stats['total_livreurs'] = (int)$stmtL->fetchColumn();

            return $stats;
        } catch (Exception $e) {
            error_log("Erreur getPressingStats: " . $e->getMessage());
            return [
                'total_commandes' => 0,
                'ca_total' => 0,
                'total_clients' => 0,
                'commandes_en_cours' => 0,
                'commandes_nouvelles' => 0,
                'total_tarifs' => 0,
                'total_livreurs' => 0
            ];
        }
    }

    /**
     * Liste des commandes du pressing
     */
    public function getPressingOrders(string $pressingCode, int $limit = 100): array
    {
        try {
            $sql = "
                SELECT 
                    c.*, 
                    COALESCE(cl.nom_client, 'Client') as nom_client, 
                    COALESCE(cl.telephone_client, '-') as telephone_client,
                    COALESCE(cl.email_client, '') as email_client
                FROM " . TABLES::COMMANDES . " c
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                ORDER BY c.id_commande DESC
                LIMIT ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindValue(1, $pressingCode, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingOrders: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Liste des articles et tarifs du pressing
     */
    public function getPressingTarifs(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT 
                    t.*, 
                    COALESCE(a.libelle_article, 'Article') as libelle_article,
                    COALESCE(s.libelle_service, 'Service') as libelle_service,
                    COALESCE(cat.libelle_categorie_article, 'Catégorie') as libelle_categorie
                FROM " . TABLES::TARIFS_ARTICLES . " t
                LEFT JOIN " . TABLES::ARTICLES_PRESSINGS . " a ON t.article_code = a.code_article
                LEFT JOIN " . TABLES::SERVICES . " s ON t.service_code = s.code_service
                LEFT JOIN " . TABLES::CATEGORIES_ARTICLES . " cat ON a.categorie_article_code = cat.code_categorie_article
                WHERE t.pressing_code = ?
                ORDER BY cat.libelle_categorie_article ASC, a.libelle_article ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingTarifs: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Horaires d'ouverture du pressing
     */
    public function getPressingHoraires(string $pressingCode): array
    {
        try {
            $sql = "SELECT * FROM " . TABLES::HORAIRES_PRESSINGS . " WHERE pressing_code = ? ORDER BY FIELD(jour, 'lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi', 'samedi', 'dimanche')";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingHoraires: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clients fidélisés ayant commandé dans ce pressing
     */
    public function getPressingClients(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT 
                    cl.code_client,
                    cl.nom_client,
                    cl.telephone_client,
                    cl.email_client,
                    cl.adresse_client,
                    COUNT(c.id_commande) as nb_commandes,
                    COALESCE(SUM(c.montant_total_commande), 0) as total_depense,
                    MAX(c.created_at_commande) as derniere_commande
                FROM " . TABLES::COMMANDES . " c
                JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                GROUP BY cl.code_client, cl.nom_client, cl.telephone_client, cl.email_client, cl.adresse_client
                ORDER BY total_depense DESC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingClients: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Missions de livraison/collecte liées à ce pressing
     */
    public function getPressingMissions(string $pressingCode, int $limit = 50): array
    {
        try {
            $sql = "
                SELECT 
                    m.*,
                    c.code_commande,
                    c.montant_total_commande,
                    c.statut_suivi_commande,
                    COALESCE(l.nom_livreur, 'Non assigné') as nom_livreur,
                    COALESCE(l.telephone_livreur, '-') as telephone_livreur,
                    COALESCE(cl.nom_client, 'Client') as nom_client,
                    COALESCE(cl.telephone_client, '-') as telephone_client
                FROM " . TABLES::MISSIONS . " m
                JOIN " . TABLES::COMMANDES . " c ON m.commande_code = c.code_commande
                LEFT JOIN " . TABLES::LIVREURS . " l ON m.livreur_code = l.code_livreur
                LEFT JOIN " . TABLES::CLIENTS . " cl ON c.client_code = cl.code_client
                WHERE c.pressing_code = ?
                ORDER BY m.id_mission DESC
                LIMIT ?
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->bindValue(1, $pressingCode, PDO::PARAM_STR);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur getPressingMissions: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Abonnement B2B et forfait actif du pressing
     */
    public function getPressingAbonnement(string $pressingCode): ?array
    {
        try {
            $sql = "
                SELECT 
                    ap.*, 
                    COALESCE(f.libelle_forfait, 'Forfait Standard') as libelle_forfait, 
                    COALESCE(ap.montant_abonnement, f.montant_forfait, 0) as montant_abonnement,
                    COALESCE(f.montant_forfait, 0) as montant_forfait,
                    COALESCE(f.description_forfait, '') as description_forfait,
                    DATEDIFF(ap.date_fin_abonnement, CURDATE()) as jours_restants
                FROM " . TABLES::ABONNEMENTS_PRESSINGS . " ap
                LEFT JOIN " . TABLES::FORFAITS . " f ON ap.forfait_code = f.code_forfait
                WHERE ap.pressing_code = ?
                ORDER BY ap.id_abonnement_pressing DESC
                LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log("Erreur getPressingAbonnement: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir le Gérant / Responsable Principal du pressing
     */
    public function getPressingOwner(string $pressingCode): ?array
    {
        try {
            $sql = "
                SELECT u.*, COALESCE(r.libelle_role, 'Gérant / Propriétaire') as libelle_role
                FROM " . TABLES::USERS_PRESSINGS . " up
                JOIN " . TABLES::USERS . " u ON up.user_code = u.code_user
                LEFT JOIN " . TABLES::ROLES . " r ON up.role_code = r.code_role OR u.role_code = r.code_role
                WHERE up.pressing_code = ? AND up.statut_user_pressing = 'actif'
                ORDER BY CASE WHEN up.role_code = 'ROLE-PRO' OR u.role_code = 'ROLE-PRO' THEN 1 ELSE 2 END, u.id_user ASC
                LIMIT 1
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            $res = $stmt->fetch(PDO::FETCH_ASSOC);
            return $res ?: null;
        } catch (Exception $e) {
            error_log("Erreur getPressingOwner: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtenir la liste de tous les Utilisateurs / Personnel du pressing
     */
    public function getPressingUsers(string $pressingCode): array
    {
        try {
            $sql = "
                SELECT u.*, COALESCE(r.libelle_role, u.role_code) as libelle_role, up.role_code as role_pressing_code, up.statut_user_pressing
                FROM " . TABLES::USERS_PRESSINGS . " up
                JOIN " . TABLES::USERS . " u ON up.user_code = u.code_user
                LEFT JOIN " . TABLES::ROLES . " r ON up.role_code = r.code_role OR u.role_code = r.code_role
                WHERE up.pressing_code = ?
                ORDER BY u.nom_user ASC
            ";
            $stmt = $this->getCon()->prepare($sql);
            $stmt->execute([$pressingCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Exception $e) {
            error_log("Erreur getPressingUsers: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Transaction Onboarding Tout-en-Un : Création atomique du Pressing, du Compte Gérant et de l'Abonnement B2B Initial
     */
    public function onboardPressingWithOwnerAndSubscription(array $pressingData, array $userData, array $subscriptionData, ?string $adminUserCode = null): array
    {
        $validator = new Validator();
        $pressingCode = !empty($pressingData['code_pressing']) ? $pressingData['code_pressing'] : $validator->generateCode(TABLES::PRESSINGS, 'code_pressing', 'PRS-', 6);
        $userCode = $validator->generateCode(TABLES::USERS, 'code_user', 'USR-', 6);
        $userPressingCode = $validator->generateCode(TABLES::USERS_PRESSINGS, 'code_user_pressing', 'USP-', 6);
        $abnCode = $validator->generateCode(TABLES::ABONNEMENTS_PRESSINGS, 'code_abonnement_pressing', 'ABN-', 6);

        $db = $this->getCon();
        if (!$db->inTransaction()) {
            $db->beginTransaction();
        }

        try {
            // 1. Créer le Pressing
            $stmtP = $db->prepare("
                INSERT INTO " . TABLES::PRESSINGS . " (
                    code_pressing, libelle_pressing, telephone_pressing, email_pressing,
                    adresse_pressing, ville_code, quartier_code, latitude_pressing, longitude_pressing,
                    logo_pressing, miniature_pressing, propose_livraison, mode_logistique,
                    frais_collecte_pressing, frais_livraison_pressing, livraison_gratuite,
                    seuil_livraison_gratuite, delai_livraison_pressing, accepte_colis_sans_detail,
                    statut_pressing, created_at_pressing
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtP->execute([
                $pressingCode,
                $pressingData['libelle_pressing'],
                $pressingData['telephone_pressing'] ?? '',
                $pressingData['email_pressing'] ?? '',
                $pressingData['adresse_pressing'] ?? '',
                $pressingData['ville_code'] ?? '',
                $pressingData['quartier_code'] ?? '',
                $pressingData['latitude_pressing'] ?? null,
                $pressingData['longitude_pressing'] ?? null,
                $pressingData['logo_pressing'] ?? '',
                $pressingData['miniature_pressing'] ?? '',
                $pressingData['propose_livraison'] ?? 1,
                $pressingData['mode_logistique'] ?? 'pressing',
                $pressingData['frais_collecte_pressing'] ?? 1000.00,
                $pressingData['frais_livraison_pressing'] ?? 1000.00,
                $pressingData['livraison_gratuite'] ?? 0,
                $pressingData['seuil_livraison_gratuite'] ?? 0.00,
                $pressingData['delai_livraison_pressing'] ?? '24h - 48h',
                $pressingData['accepte_colis_sans_detail'] ?? 1
            ]);

            // 2. Créer l'Utilisateur Gérant (ROLE-PRO)
            $hashedPassword = password_hash($userData['password_user'], PASSWORD_BCRYPT);
            
            $stmtU = $db->prepare("
                INSERT INTO " . TABLES::USERS . " (
                    code_user, role_code, nom_user, prenom_user, telephone_user, email_user, password_user, statut_user, created_at_user
                ) VALUES (?, 'ROLE-PRO', ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtU->execute([
                $userCode,
                $userData['nom_user'],
                $userData['prenom_user'] ?? '',
                $userData['telephone_user'] ?? '',
                $userData['email_user'],
                $hashedPassword
            ]);

            // 3. Associer le Gérant au Pressing dans users_pressings
            $stmtUP = $db->prepare("
                INSERT INTO " . TABLES::USERS_PRESSINGS . " (
                    code_user_pressing, user_code, pressing_code, role_code, statut_user_pressing, created_at_user_pressing
                ) VALUES (?, ?, ?, 'ROLE-PRO', 'actif', NOW())
            ");
            $stmtUP->execute([$userPressingCode, $userCode, $pressingCode]);

            // 4. Créer l'Abonnement B2B Initial rattaché au Pressing ET au Gérant
            $stmtA = $db->prepare("
                INSERT INTO " . TABLES::ABONNEMENTS_PRESSINGS . " (
                    code_abonnement_pressing, pressing_code, user_code, created_by_user, forfait_code,
                    montant_abonnement, date_debut_abonnement, date_fin_abonnement, statut_abonnement_pressing, created_at_abonnement_pressing
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'actif', NOW())
            ");
            $stmtA->execute([
                $abnCode,
                $pressingCode,
                $userCode,
                $adminUserCode,
                $subscriptionData['forfait_code'],
                $subscriptionData['montant_abonnement'],
                $subscriptionData['date_debut_abonnement'],
                $subscriptionData['date_fin_abonnement']
            ]);

            if ($db->inTransaction()) {
                $db->commit();
            }

            return [
                'success' => true,
                'pressing_code' => $pressingCode,
                'user_code' => $userCode,
                'abonnement_code' => $abnCode
            ];
        } catch (Exception $e) {
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            error_log('[onboardPressingWithOwnerAndSubscription] ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}
