# 🔑 Configuration des Accès Base de Données - Olive Service

Ce document récapitule les informations nécessaires pour configurer la connexion à la base de données **`olive`**.

---

## 📝 Informations de Connexion à Renseigner

Veuillez indiquer ci-dessous vos identifiants et accès pour la base de données MySQL :

| Paramètre | Valeur à renseigner | Description |
| :--- | :--- | :--- |
| **Hôte (Host)** | `localhost` / `127.0.0.1` | Adresse du serveur MySQL / MariaDB |
| **Nom de la BDD (Database)** | `olive` | Nom de la base de données |
| **Utilisateur (User)** | `admin` *(ou votre utilisateur)* | Nom d'utilisateur MySQL |
| **Mot de passe (Password)** | `admin` | Mot de passe d'accès |
| **Port** | `3306` | Port MySQL (par défaut 3306) |

---

## ⚙️ Emplacement du fichier de configuration dans le projet

Les paramètres de connexion s'appliquent directement dans le fichier suivant :
👉 **`config/Database.php`**

```php
// Exemple de configuration dans config/Database.php :

private $host     = 'localhost';
private $dbname   = 'olive';
private $user     = 'VOTRE_UTILISATEUR';
private $password = 'VOTRE_MOT_DE_PASSE';
```

---

## 🚀 Étapes de Mise en Place

1. Indiquez vos identifiants dans ce fichier ou communiquez-les.
2. Assurez-vous que le fichier SQL `database/olive.sql` est bien importé dans votre serveur MySQL sur la base de données `olive`.
3. Le fichier `config/Database.php` sera mis à jour avec ces informations pour rendre l'application opérationnelle.
