# Guide de déploiement - Fonctionnalité de Paiement des Cautisations

## 📋 Vue d'ensemble

Ce guide détaille les étapes pour déployer et tester la fonctionnalité complète de paiement des cautisations.

## ✅ Pré-requis

1. Base de données `olive` configurée avec les tables:
   - `souscriptions`
   - `clients`
   - `packs`
   - `pack_souscriptions`
   - `cautisation_clients`
   - `sessions`
   - `caisses`

2. Application basée sur le système de contrôleurs BaseController
3. Système d'authentification Context::user() et Context::annee()

## 📁 Fichiers créés/modifiés

### Contrôleurs
- **`/controllers/cotisations/CautisationPaymentController.php`** (NOUVEAU)
  - Gère la recherche de souscriptions
  - Affiche la situation d'une souscription
  - Enregistre les paiements de cautisations

### Vues
- **`/views/cautisations_payment/search.php`** (NOUVEAU)
  - Formulaire de recherche de souscription
  - Tableau des résultats

- **`/views/cautisations_payment/situation.php`** (NOUVEAU)
  - Affichage des informations du client
  - Historique des paiements
  - Modal de paiement

### Classes utilitaires
- **`/core/CautisationValidator.php`** (NOUVEAU)
  - Validation des montants
  - Calcul des jours/montants
  - Calcul de la date du prochain RDV
  - Formatage des devises

### Modèles
- **`/models/cotisations/ModelCotisation.php`** (UTILISÉ EXISTANT)
  - Méthodes: `createCotisation()`, `getBySouscription()`

## 🔧 Configuration requise

### Routes
Ajouter les routes suivantes dans le routeur (généralement dans `core/Router.php`):

```php
// Routes de paiement des cautisations
$router->add('cautisation-payment/search-form', 'CautisationPaymentController@searchForm');
$router->add('cautisation-payment/search', 'CautisationPaymentController@search');
$router->add('cautisation-payment/situation', 'CautisationPaymentController@situation');
$router->add('cautisation-payment/situation-details', 'CautisationPaymentController@situationDetails');
$router->add('cautisation-payment/history', 'CautisationPaymentController@history');
$router->add('cautisation-payment/savepayment', 'CautisationPaymentController@savepayment');
```

### Permissions requises
L'utilisateur doit avoir la permission:
- `MANAGE_PAYMENTS` (Gestion de la Caisse et Encaissements)

Rôles autorisés:
- `ROLE_CAISSIER` (Agent de Caisse)
- `ROLE_COMPTABLE` (Chef Comptable)
- `ROLE_SUPERADMIN`

### Configuration de la caisse
La système récupère automatiquement la caisse ouverte via:
```php
SELECT * FROM caisses 
WHERE zone_code = ? AND etablissement_code = ?
AND statut_caisse = 'ouverte'
```

Assurez-vous qu'une caisse est ouverte avant d'effectuer les paiements.

## 🚀 Déploiement

### Étape 1: Copier les fichiers

```bash
# Copier le contrôleur
cp CautisationPaymentController.php controllers/cotisations/

# Copier les vues
mkdir -p views/cautisations_payment
cp views/cautisations_payment/* views/cautisations_payment/

# Copier la classe utilitaire
cp CautisationValidator.php core/
```

### Étape 2: Vérifier les imports dans le contrôleur

```php
<?php
// En haut de CautisationPaymentController.php
require_once __DIR__ . '/../../core/CautisationValidator.php';
require_once __DIR__ . '/../../models/cotisations/ModelCotisation.php';
require_once __DIR__ . '/../../core/Context.php';
```

### Étape 3: Configurer les routes

Ajouter au fichier de routing principal.

### Étape 4: Tester l'accès

Accéder à: `http://votre-domaine/cautisation-payment/search-form`

## 🧪 Scénarios de test

### Test 1: Recherche de souscription

1. Aller à `/cautisation-payment/search-form`
2. Entrer un critère de recherche (téléphone, nom, code, etc.)
3. Cliquer sur "Rechercher"
4. Vérifier que les souscriptions s'affichent correctement

**Données attendues:**
- Nom du client
- Code souscription
- Session
- Montant total (somme des packs)

### Test 2: Affichage de la situation

1. Depuis la liste de recherche, cliquer sur "Détails"
2. Vérifier l'affichage des sections:
   - Informations du client (6 champs)
   - Résumé de la situation (montants, jours, progression)
   - Historique des paiements

### Test 3: Paiement par montant

1. Cliquer sur "Faire paiement"
2. Remplir le modal:
   - Mode de paiement: Espèces
   - Type: "Saisir le montant"
   - Montant: 500 FCFA (ou un multiple valide)
3. Vérifier que:
   - Le nombre de jours est calculé automatiquement
   - La date du prochain RDV est calculée
   - La validation du montant fonctionne

### Test 4: Paiement par nombre de jours

1. Cliquer sur "Faire paiement"
2. Remplir le modal:
   - Mode de paiement: Mobile Money
   - Type: "Saisir le nombre de jours"
   - Nombre de jours: 5
3. Vérifier que:
   - Le montant est calculé automatiquement
   - La date du prochain RDV est correcte
   - Le montant ne dépasse pas le montant restant

### Test 5: Validations

1. **Montant invalide**
   - Essayer de saisir 550 FCFA (si le prix/jour est 100)
   - Vérifier le message d'erreur
   - Vérifier que la suggestion apparaît

2. **Dépassement du montant restant**
   - Si montant restant = 300 FCFA
   - Essayer de payer 500 FCFA
   - Vérifier l'erreur

3. **Dépassement du nombre de jours**
   - Si jours restants = 3
   - Essayer de payer 5 jours
   - Vérifier l'erreur

### Test 6: Historique mis à jour

1. Effectuer un paiement
2. Le modal se ferme et la page se recharge
3. Vérifier que l'historique affiche le nouveau paiement
4. Vérifier que le montant restant a été mis à jour

## 📊 Données de test recommandées

```sql
-- Créer un client de test
INSERT INTO clients (code_client, nom_client, prenom_client, telephone_client, email_client, 
                     sexe_client, lieu_residence_client, profession_client, 
                     user_code, zone_code, etablissement_code, statut_client)
VALUES ('CLIENT-TEST', 'Dupont', 'Jean', '221701234567', 'test@example.com',
        'M', 'Dakar', 'Ingénieur', '5wBEh2OfI00frxk8ITPf', '6QIlVfXP0LiXE9tBzHownYLAAqDi2', 
        '5454544456', 'actif');

-- Créer une souscription
INSERT INTO souscriptions (code_souscription, client_code, zone_code, etablissement_code, 
                          annee_code, session_code, user_code, created_at_souscription, 
                          statut_souscription)
VALUES ('SOUS-TEST', 'CLIENT-TEST', '6QIlVfXP0LiXE9tBzHownYLAAqDi2', '5454544456',
        'VL0hWQ', 'kezoZf6kVz40261eKu', '5wBEh2OfI00frxk8ITPf', NOW(), 'valide');

-- Lier un pack à la souscription
INSERT INTO pack_souscriptions (souscription_code, pack_code, annee_code, statut_pack_souscription,
                               established_code, user_code, zone_code)
VALUES ('SOUS-TEST', 'PCK-C6EORC2G', 'VL0hWQ', 'actif',
        '5454544456', '5wBEh2OfI00frxk8ITPf', '6QIlVfXP0LiXE9tBzHownYLAAqDi2');

-- Ouvrir une caisse
INSERT INTO caisses (code_caisse, date_ouverture, zone_code, etablissement_code, 
                     statut_caisse, user_code, created_at_caisse)
VALUES ('CAISSE-TEST', NOW(), '6QIlVfXP0LiXE9tBzHownYLAAqDi2', '5454544456',
        'ouverte', '5wBEh2OfI00frxk8ITPf', NOW());
```

## 🐛 Dépannage

### Erreur: "Souscription introuvable"
- Vérifier que la souscription existe dans la base de données
- Vérifier que le statut est 'valide'

### Erreur: "Prix de cotisation introuvable"
- Vérifier que des packs sont liés à la souscription
- Vérifier que le prix_cotisation_pack est défini pour les packs

### Erreur: "La caisse ouverte est introuvable"
- Ouvrir une caisse avant d'effectuer les paiements
- Vérifier que la caisse a le statut 'ouverte'

### Le montant n'est pas calculé automatiquement
- Vérifier la console JavaScript pour les erreurs
- Vérifier que les calculs utilisent le bon prix_cotisation_pack

### L'historique ne s'affiche pas
- Vérifier que la requête AJAX réussit (F12 > Network)
- Vérifier que les cautisations sont en base de données

## 📝 Améliorations futures

1. **Édition/Annulation des cautisations**
   - Permettre la modification après enregistrement
   - Justification des annulations

2. **Génération de reçu**
   - Reçu PDF automatique
   - Envoi par email

3. **Rappels et relances**
   - Notification pour les prochains rendez-vous
   - SMS/Email de rappel

4. **Rapports**
   - Rapport journalier des paiements
   - Statistiques par client/agent

5. **Intégration Mobile Money**
   - Vérification automatique des paiements
   - Réconciliation des comptes

## 📞 Support

Pour toute question ou problème, consulter la documentation métier dans `consigne.md`.

---

**Dernière mise à jour:** 01/09/2026
**Version:** 1.0
**Statut:** Prêt pour production
