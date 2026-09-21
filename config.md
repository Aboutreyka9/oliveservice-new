# Configuration & Master Prompt Système pour Nouveau Projet

> **Note d'utilisation** : Ce document constitue le **Master Prompt** et le guide d'architecture complet permettant d'initialiser, de configurer et de piloter un agent d'intelligence artificielle (Antigravity, Cursor, Claude, ChatGPT, etc.) sur un nouveau projet reprenant à l'identique les standards, règles de sécurité, design system et architecture technique de ce projet.
> Vous pouvez copier l'intégralité de la section **Master Prompt** dans le fichier `.agents/rules/master_rules.md`, `.cursorrules` ou dans les instructions système de votre outil d'assistance IA.

---

# PARTIE 1 : LE MASTER PROMPT (À COPIER DANS L'IA)

```markdown
# Directives Système & Standards d'Ingénierie pour le Projet

Vous êtes un Architecte Logiciel Senior et Développeur Full-Stack expert sur une stack PHP MVC moderne sur mesure. Vous devez respecter scrupuleusement les règles, conventions d'architecture, chartes graphiques et contraintes de sécurité définies ci-après.

---

## 1. RÈGLE D'INSPECTION DE L'ÉTAT PRÉSENT & SOURCE DE VÉRITÉ SQL

Avant d'exécuter toute tâche, génération d'interface ou modification de code :

1. **Inspection du schéma et des données via `database/<nom_projet>.sql`** :
   - Toujours consulter directement le fichier `database/<nom_projet>.sql` pour vérifier la structure exacte des tables, les colonnes, les types de données, les contraintes et les valeurs d'initialisation.
   - Utiliser ce fichier (copie conforme de la base de données) comme référence principale pour la correspondance des champs et pour éviter d'exécuter des requêtes d'inspection inutiles en BDD.

2. **Inspection systématique de l'environnement** :
   - Lire le fichier d'environnement `.env` actif et les fichiers de configuration (`config/Database.php`, `config/const.php`) pour valider les paramètres cibles avant toute action.

3. **Interdiction Stricte des Valeurs Hard-Codées (No Hardcoding Rule)** :
   - Ne JAMAIS inscrire de valeurs hard-codées (montants, compteurs, données statiques de repli, rôles ou identifiants en dur) dans les contrôleurs, modèles ou vues.
   - Tous les calculs (totaux, soldes restants, durées, taux de progression, statistiques, KPIs) et états doivent TOUJOURS être évalués et dérivés dynamiquement en temps réel à partir des données réelles de la base de données.

---

## 2. RÈGLE DE MODIFICATION DE SCHÉMA BDD & AJOUT DE COLONNES

Avant d'exécuter toute modification de la structure de la base de données (`CREATE TABLE`, `ALTER TABLE`, ajout ou modification de colonnes) :

1. **Justification et Explication Préalable Obligatoire** :
   - Expliquer la raison fonctionnelle et technique nécessitant la création de la table ou l'ajout de la colonne.
   - Présenter la structure exacte proposée : nom de la table, nom de la colonne, type de données (VARCHAR, INT, DECIMAL, DATETIME, ENUM, etc.), contraintes (NULL, NOT NULL, DEFAULT, INDEX, etc.).

2. **Demande d'Autorisation Explicite** :
   - Ne JAMAIS exécuter de requête `ALTER TABLE` ou `CREATE TABLE` sans avoir préalablement expliqué le besoin et obtenu l'accord explicite de l'utilisateur.

3. **Mise à Jour Systématique du SQL de Référence** :
   - Une fois la modification validée et exécutée, mettre à jour immédiatement le fichier de référence `database/<nom_projet>.sql` pour conserver la synchronisation entre la BDD et la référence du projet.

4. **Conventions de Nomenclature SQL** :
   - Clés primaires auto-incrémentées : `id_<entite>` (INT AUTO_INCREMENT).
   - Codes métiers uniques : `code_<entite>` (VARCHAR(50), ex: `CLI-...`, `DEP-...`, `VRS-...`).
   - Horodatages : `created_at_<entite>` (DATETIME NOT NULL), `updated_at_<entite>` (DATETIME).
   - Clés contextuelles obligatoires : `etablissement_code`, `zone_code`, `annee_code`, `user_code`.

---

## 3. SÉCURITÉ, RBAC & FILTRAGE CONTEXTUEL MULTI-TENANT

1. **Triple Filtrage Contextuel Obligatoire** :
   - Pour les tables opérationnelles soumises au contexte (ex: caisses, cotisations/transactions, dépenses, souscriptions, versements), appliquer systématiquement le filtrage contextuel tripartite via la classe `Context` :
     - `etablissement_code = Context::etablissement()`
     - `zone_code = Context::zone()`
     - `annee_code = Context::annee()`
   - Utiliser systématiquement `Context::applyTripleFilter($tableAlias, $conds, $params)` ou `Context::applyScopeSQL($tableAlias, $conds, $params)`.

2. **Restriction des Rôles Métier & Terrain** :
   - Pour les agents de terrain / commerciaux (`ROLE_COMMERCIAL`), filtrer strictement sur leur identifiant utilisateur (`user_code` / `commercial_code`). Ils ne doivent en aucun cas accéder aux données de leurs pairs.
   - Pour les gestionnaires de zone (`ROLE_GESTIONNAIRE`), filtrer par leur zone de compétence.
   - Pour les comptables / financiers (`ROLE_FINANCE`), filtrer par établissement et exercice en cours.

3. **Permission Joker / Pass-Partout (`MAIN_ACCESS`)** :
   - La permission `MAIN_ACCESS` est exclusivement réservée aux administrateurs globaux (`ROLE_SUPERADMIN`, `ROLE_ADMIN`).
   - Elle permet d'accéder aux paramétrages fondamentaux (création d'années, d'établissements, de zones, attribution de rôles) même lorsqu'aucun contexte actif (année, zone, établissement) n'est encore sélectionné en session.

4. **Sécurité Web & Données Sensibles** :
   - **Requêtes Préparées PDO** : Toute interaction avec la base de données doit utiliser des requêtes préparées avec paramètres typés (`?` ou `:nom`). Aucune concaténation de variable utilisateur dans le SQL.
   - **Protection CSRF** : Tout formulaire POST ou requête mutogène AJAX doit envoyer et vérifier `csrf_token` via `BaseController::requirePost()`.
   - **Chiffrement d'Identifiants d'URL** : Ne jamais exposer les IDs auto-incrémentés bruts dans les URLs publiques. Utiliser systématiquement le chiffrement symétrique réversible :
     `$editId = $this->validator->crypter($id);` et `$id = $this->validator->decrypter($idCrypte);`.
   - **Contrôle d'Accès Granulaire** : Chaque méthode de contrôleur doit démarrer par `$this->requirePermission('CODE_PERMISSION');`.

---

## 4. DESIGN SYSTEM & CHARTE GRAPHIQUE PREMIUM

Toutes les interfaces utilisateurs doivent respecter scrupuleusement l'identité visuelle moderne et élégante du projet :

1. **Palette de Couleurs Curatée** :
   - **Bleu Navy Profond** : `#1E3A5F` (Primaire, en-têtes, boutons d'action principaux, navbar) / `#0F172A` (Titres sombres, texte fort).
   - **Émeraude** : `#059669` / `#10B981` (Succès, validations, encaissements, soldes positifs).
   - **Rouge Crimson** : `#DC2626` / `#EF4444` (Dépenses, sorties de caisse, suppressions, blocages/erreurs).
   - **Ambre / Or** : `#D97706` / `#F59E0B` (En attente, alertes, restes à régulariser).
   - **Bleu Indigo** : `#2563EB` / `#3B82F6` (Informations, compteurs globaux).
   - **Fonds & Cartes** : Blanc `#FFFFFF`, surfaces douces `#F8FAFC`, bordures fines `#E2E8F0` / `#CBD5E1`.

2. **Typographie & Lisibilité** :
   - Police moderne sans-serif (Inter, Roboto ou système UI).
   - Titres percutants avec hiérarchie claire (`h1` 22px 800, `h2` 16px 700).
   - Badges de codes métier stylisés : police monospace (`font-family: monospace;`), fond `#F1F5F9`, bordure `#CBD5E1`, texte gras.

3. **Cartes Statistiques (KPI Grid)** :
   - Cartes KPI dynamiques au-dessus des tables de données.
   - En-tête de carte avec titre discret (uppercase 11px gras `#64748B`) et icône dans une pastille translucide avec dégradé subtil.
   - Chiffre clé imposant (24px 800) et sous-titre contextualisé (badge d'évolution ou libellé explicatif).

4. **DataTables Stylisées & Interactives** :
   - Intégration DataTables complète avec localisation française (`json/datatables-i18n-fr-FR.json`).
   - En-têtes de colonnes propres avec fond `#F8FAFC`, texte uppercase 11px `#475569`.
   - Lignes avec survol doux (`#F8FAFC`) et padding aéré.
   - Colonne "Actions" alignée à droite avec boutons compacts (`btn-sm`) et icônes explicites.

5. **Iconographie Systématique (Lucide Icons)** :
   - Utiliser exclusivement la bibliothèque Lucide Icons (`<i data-lucide="nom-icone"></i>`).
   - Toujours invoquer `lucide.createIcons();` dans le callback `drawCallback` des DataTables et après injection dynamique de DOM.

6. **Micro-Interactions & Retours Non-Bloquants** :
   - Feedback visuel via notifications Toast (`toastr.success`, `toastr.error`, `toastr.warning`, `toastr.info`).
   - Boutons en cours de traitement avec état désactivé / spinner de chargement.
   - Verrouillage strict des boutons d'action lors de non-conformités (ex: boutons de clôture désactivés avec alerte rouge tant qu'un écart de caisse subsiste).

---

## 5. RÈGLES DE CODAGE & CYCLE DE VIE MÉTIER

1. **Verrouillage Post-Validation** :
   - Dès qu'un enregistrement passe au statut `actif`, `valide` ou `cloture` (ex: dépenses approuvées, caisses clôturées, versements validés), les actions d'édition et les switches de statut (`toggle-statut`) doivent être désactivés et verrouillés tant côté client (`disabled`, `cursor: not-allowed`, `title` explicatif) que côté serveur (rejet dans la méthode `changer()` / `edit()`).

2. **Flux à Double Validation / Émargement** :
   - Pour les flux financiers (sorties de caisse, versements commerciaux) :
     1. Déclaration par l'initiateur (statut `En attente`, `decission = 'attente'`).
     2. Contrôle physique et comparaison dynamique par l'ordonnateur / comptable.
     3. Validation synchronisée en cascade (versement validé -> caisse validée -> cotisations passées à `valide`).

3. **Validation de Syntaxe Continue** :
   - Avant de finaliser une tâche, toujours exécuter un linter / vérificateur de syntaxe (`php -l chemin/du/fichier.php`) sur l'ensemble des fichiers modifiés.
```

---

# PARTIE 2 : BLUEPRINT D'ARCHITECTURE TECHNIQUE

## 1. Arborescence Recommandée du Projet

```text
nouveau-projet/
├── .agents/
│   └── rules/
│       ├── live_inspection_rule.md        # Règle d'inspection SQL & variables
│       └── schema_migration_rule.md       # Règle de migration BDD
├── .env                                   # Configuration locale active
├── .env.example                           # Exemple de configuration d'environnement
├── .htaccess                              # Réécriture d'URL Apache vers index.php
├── config/
│   ├── Database.php                       # Singleton de connexion PDO MySQL
│   └── const.php                          # Constantes globales, détection d'URL, tables
├── controllers/
│   ├── auth/
│   │   └── AuthController.php             # Connexion, déconnexion, session
│   ├── dashboard/
│   │   └── DashboardController.php        # Tableau de bord principal & KPIs
│   └── .../                               # Autres modules métier
├── core/
│   ├── Autoloader.php                     # Chargement automatique PSR-4 / Classes
│   ├── BaseController.php                 # Contrôleur racine (JSON, CSRF, vues, RBAC)
│   ├── BaseModel.php                      # ORM / Modèle générique (CRUD, PDO)
│   ├── Context.php                        # Gestionnaire de contexte (Année, Étab, Zone, Rôle)
│   ├── Router.php                         # Routage dynamique (Contrôleur / Action / Params)
│   └── Validator.php                      # Validation, jetons CSRF, chiffrement URL
├── database/
│   └── schema.sql                         # Dump SQL complet et source de vérité
├── models/
│   └── .../                               # Modèles métier héritant de BaseModel
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── style.css                  # Feuilles de style sur mesure & variables
│   │   ├── js/
│   │   │   ├── app.js                     # Initialisation globale & helpers Toastr
│   │   │   └── modules/                   # Scripts JavaScript dédiés par vue
│   │   └── images/
│   ├── inc/
│   │   ├── header.php                     # Balises head, imports CSS, métadonnées
│   │   ├── nav.php                        # Barre de navigation supérieure & sélecteurs
│   │   ├── sidbar.php                     # Menu latéral dynamique selon les permissions
│   │   └── footer-link.php                # Scripts JS, initialisation Lucide, CSRF token
│   └── json/
│       └── datatables-i18n-fr-FR.json      # Fichier de traduction officielle DataTables FR
├── views/
│   ├── dashboard/
│   ├── errors/
│   │   ├── 403.php                        # Vue Accès Interdit
│   │   ├── 404.php                        # Vue Page Non Trouvée
│   │   └── 500.php                        # Vue Erreur Serveur
│   └── .../                               # Vues spécifiques
└── index.php                              # Point d'entrée unique de l'application
```

---

## 2. Fichiers Socles Prêts à l'Emploi

### A. Point d'entrée `.htaccess`
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Ne pas réécrire les fichiers réels (images, css, js)
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    
    # Rediriger toutes les requêtes vers index.php
    RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
</IfModule>
```

### B. Configuration d'Environnement `.env.example`
```env
# APPLICATION
APP_ENV=local
APP_NAME="MON NOUVEAU PROJET"
APP_URL=http://localhost/mon-projet
APP_SECRET=changer_ce_token_secret_aleatoire_64_caracteres

# BASE DE DONNÉES
DB_HOST=localhost
DB_PORT=3306
DB_NAME=mon_projet_db
DB_USER=root
DB_PASSWORD=

# EMAILS (SMTP)
MAIL_MAILER=smtp
MAIL_HOST=mail.domaine.com
MAIL_PORT=465
MAIL_USERNAME=noreply@domaine.com
MAIL_PASSWORD=secret
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=noreply@domaine.com
MAIL_FROM_NAME="Mon Nouveau Projet"
```

### C. Connexion PDO Singleton (`config/Database.php`)
```php
<?php

class Database
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            $host = $_ENV['DB_HOST'] ?? 'localhost';
            $port = $_ENV['DB_PORT'] ?? '3306';
            $dbName = $_ENV['DB_NAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';

            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
            ];

            try {
                self::$pdo = new PDO($dsn, $user, $password, $options);
            } catch (PDOException $e) {
                die("Erreur de connexion à la base de données : " . $e->getMessage());
            }
        }

        return self::$pdo;
    }

    public function getCon(): PDO
    {
        return self::getConnection();
    }
}
```

### D. Constantes & Détection d'URL (`config/const.php`)
```php
<?php

define('ROOT', dirname(__DIR__));

if (!defined('RACINE')) {
    $httpHost = $_SERVER['HTTP_HOST'] ?? '';
    if (!empty($httpHost)) {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? '') == 443) ? 'https://' : 'http://';
        $scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = rtrim(str_replace('\\', '/', $scriptDir), '/') . '/';
        define('RACINE', $protocol . $httpHost . $basePath);
    } else {
        define('RACINE', rtrim($_ENV['APP_URL'] ?? 'http://localhost/', '/') . '/');
    }
}

const USERS_AUTH = 'users_auth';
define('APP_TITLE', $_ENV['APP_NAME'] ?? 'Application de Gestion');
```

### E. Gestionnaire de Contexte Sécurisé (`core/Context.php`)
```php
<?php

class Context
{
    public static function annee(): string
    {
        return $_SESSION['annee_active_code'] ?? '';
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
        return $_SESSION[USERS_AUTH]['code_user'] ?? null;
    }

    public static function roles(): array
    {
        $roles = $_SESSION[USERS_AUTH]['roles'] ?? [];
        if (is_string($roles)) $roles = [$roles];
        return array_values(array_unique(array_filter($roles)));
    }

    public static function isSuperAdmin(): bool
    {
        return !empty(array_intersect(self::roles(), ['ROLE_SUPERADMIN', 'ROLE_ADMIN']));
    }

    public static function hasPermission(string $permission): bool
    {
        if (self::isSuperAdmin()) return true;
        $perms = $_SESSION['user_permissions'] ?? [];
        return in_array('*', $perms, true) || in_array($permission, $perms, true);
    }

    public static function can(string $permission): bool
    {
        return self::hasPermission($permission);
    }

    /**
     * Applique systématiquement les 3 filtres obligatoires sur les tables opérationnelles
     */
    public static function applyTripleFilter(string $alias, array &$conditions, array &$params, bool $applyUserScope = false): void
    {
        $p = !empty($alias) ? rtrim($alias, '.') . '.' : '';

        if (self::etablissement()) {
            $conditions[] = "{$p}etablissement_code = ?";
            $params[] = self::etablissement();
        }

        if (self::zone()) {
            $conditions[] = "{$p}zone_code = ?";
            $params[] = self::zone();
        }

        if (self::annee()) {
            $conditions[] = "{$p}annee_code = ?";
            $params[] = self::annee();
        }

        if ($applyUserScope && !self::isSuperAdmin()) {
            $conditions[] = "{$p}user_code = ?";
            $params[] = self::user();
        }
    }
}
```

### F. Contrôleur de Base Abstrait (`core/BaseController.php`)
```php
<?php

abstract class BaseController
{
    protected Validator $validator;
    protected $model;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->model = $this->resolveModel();
    }

    abstract protected function resolveModel();

    protected function requirePost(bool $checkCsrf = true): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['status' => 0, 'message' => 'Méthode non autorisée (POST requis)'], 405);
        }

        if ($checkCsrf && !Validator::validateCsrfToken($_POST['csrf_token'] ?? null)) {
            $this->json(['status' => 0, 'message' => 'Session expirée ou jeton de sécurité invalide. Veuillez rafraîchir.'], 419);
        }
    }

    protected function requirePermission(string $permission): void
    {
        if (!isset($_SESSION[USERS_AUTH])) {
            if ($this->isAjax()) {
                $this->json(['status' => 0, 'message' => 'Authentification requise'], 401);
            } else {
                header('Location: ' . RACINE . 'user/connexion');
                exit();
            }
        }

        if (!Context::hasPermission($permission)) {
            if ($this->isAjax()) {
                $this->json(['status' => 0, 'message' => 'Accès refusé : privilèges insuffisants'], 403);
            } else {
                http_response_code(403);
                require ROOT . '/views/errors/403.php';
                exit();
            }
        }
    }

    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit();
    }

    protected function success(string $message, array $extra = []): void
    {
        $this->json(array_merge(['status' => 1, 'success' => true, 'message' => $message], $extra));
    }

    protected function error(string $message, int $code = 400, array $extra = []): void
    {
        $this->json(array_merge(['status' => 0, 'success' => false, 'message' => $message], $extra), $code);
    }

    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
    }

    protected function loadView(string $viewPath, array $data = []): void
    {
        extract($data);
        $fullPath = ROOT . '/' . ltrim($viewPath, '/');
        if (file_exists($fullPath)) {
            require $fullPath;
        } else {
            die("Vue introuvable : " . htmlspecialchars($viewPath));
        }
    }
}
```

### G. Modèle de Base Abstrait (`core/BaseModel.php`)
```php
<?php

abstract class BaseModel
{
    protected PDO $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected ?string $statusField = null;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function getCon(): PDO
    {
        return $this->db;
    }

    public function getById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function create(array $data): int|bool
    {
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = implode(', ', array_fill(0, count($keys), '?'));

        $stmt = $this->db->prepare("INSERT INTO {$this->table} ({$fields}) VALUES ({$placeholders})");
        if ($stmt->execute(array_values($data))) {
            return (int)$this->db->lastInsertId();
        }
        return false;
    }

    public function update(array $data, int $id): bool
    {
        $sets = [];
        $values = [];
        foreach ($data as $col => $val) {
            $sets[] = "{$col} = ?";
            $values[] = $val;
        }
        $values[] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $sets) . " WHERE {$this->primaryKey} = ?");
        return $stmt->execute($values);
    }

    public function toggleStatus(int $id): bool
    {
        if (!$this->statusField) return false;
        $item = $this->getById($id);
        if (!$item) return false;

        $newStatus = ($item[$this->statusField] === 'actif') ? 'inactif' : 'actif';
        $stmt = $this->db->prepare("UPDATE {$this->table} SET {$this->statusField} = ? WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$newStatus, $id]);
    }
}
```

---

## 3. Guide de Démarrage Rapide (Checklist pour le Nouveau Projet)

1. **Créer les répertoires** :
   Créer les dossiers `config`, `core`, `controllers`, `models`, `views`, `public/assets/css`, `public/assets/js/modules`, `public/inc`, `database`.
2. **Initialiser l'environnement** :
   Copier `.env.example` en `.env` et renseigner les accès MySQL (`DB_NAME`, `DB_USER`, `DB_PASSWORD`).
3. **Créer le schéma SQL de référence** :
   Placer le dump d'initialisation dans `database/<nom_du_projet>.sql`.
4. **Configurer l'Agent IA** :
   Créer le fichier `.agents/rules/live_inspection_rule.md` contenant la **Partie 1** de ce document pour forcer l'agent à respecter l'ensemble des règles dès son premier prompt.
