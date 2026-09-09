# Matrice d'Inspection RBAC - Boutons d'Action par Page

Ce document recense l'intégralité des boutons d'action de chaque page de l'application **Olive Service**.
Veuillez indiquer pour chaque bouton le rôle, l'agent ou le type d'agent autorisé à le voir ou l'exécuter.

---

## 1. Tableau de Bord (Dashboard)
**URL** : `http://oliveservice.local/`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| Dashboard | `Lancer Nouveau Contrat` | Redirige vers le wizard de souscription | |
| Dashboard | `Encaisser Cotisation` | Redirige vers le formulaire de recherche d'encaissement | | agent commercial | tresorier et financier avec permission encaissement
| Dashboard | `Valider Versements` | Redirige vers la liste des versements à valider | | tresorier et financier
| Dashboard | `Afficher tout` (Catégories Packs) | Redirige vers la liste des packs | | gestionnaire et admin et super admin|
| Dashboard | `Afficher tout` (Sessions) | Redirige vers la liste des sessions | |admin et super admin|

---

## 2. Module Configuration Système

### 2.1. Années d'Activité
**URLs** : `/annee/list`, `/annee/formulaire`, `/annee/edition/{id}`, `/annee/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/annee/list` | `Créer une Année` | Redirige vers le formulaire de création d'année | | admin et super admin 
| `/annee/list` | `Éditer` (Ligne tableau) | Ouvre le formulaire de modification de l'année | | admin et super admin
| `/annee/list` | `Détails` (Ligne tableau) | Affiche la fiche de l'année | | admin et super admin 
| `/annee/formulaire` | `Créer l'année` (Submit) | Enregistre une nouvelle année | | admin et super admin 
| `/annee/edition/{id}` | `Mettre à jour l'année` (Submit) | Enregistre les modifications de l'année | | admin et super admin 
| `/annee/details/{id}` | `Modifier l'année` | Redirige vers le formulaire d'édition | | admin et super admin 
| `/annee/details/{id}` | `Retour` | Retourne à la liste des années | | admin et super admin

### 2.2. Sessions de Cotisation
**URLs** : `/session/list`, `/session/formulaire`, `/session/edition/{id}`, `/session/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/session/list` | `Nouvelle Session` | Redirige vers le formulaire de création de session | | admin et super admin 
| `/session/list` | `Éditer` (Ligne tableau) | Modifie la session | | admin et super admin 
| `/session/list` | `Détails` (Ligne tableau) | Consulte les détails de la session | | admin et super admin
| `/session/formulaire` | `Créer la session` (Submit) | Enregistre une nouvelle session | | admin et super admin
| `/session/edition/{id}` | `Mettre à jour la session` (Submit) | Enregistre les modifications | | admin et super admin
| `/session/details/{id}` | `Modifier Session` | Redirige vers le formulaire d'édition | | admin et super admin

### 2.3. Zones Géographiques
**URLs** : `/zone/list`, `/zone/formulaire`, `/zone/edition/{id}`, `/zone/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/zone/list` | `Nouvelle Zone` | Formulaire de création de zone | | admin et super admin et 
| `/zone/list` | `Éditer` (Ligne tableau) | Édition d'une zone | |  admin et super admin et gestionnaire|
| `/zone/list` | `Détails` (Ligne tableau) | Détails d'une zone | | admin et super admin
| `/zone/formulaire` | `Créer la zone` (Submit) | Enregistrement de la zone | | admin et super admin
| `/zone/details/{id}` | `Modifier Zone` | Édition de la zone | |  admin et super admin

### 2.4. Établissements
**URL** : `/etablissement/config`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) | 
|---|---|---|---|
| `/etablissement/config` | `Enregistrer la configuration` | Sauvegarde les paramètres de l'établissement | | permission joker |  super admin 

---

## 3. Module Produits, Offres & Catalogue

### 3.1. Catégories d'Articles
**URLs** : `/categorie_article/list`, `/categorie_article/formulaire`, `/categorie_article/edition/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/categorie_article/list` | `Nouvelle Catégorie` | Création d'une catégorie d'articles | | gestionnaire et tresorier et admin et super admin|
| `/categorie_article/list` | `Éditer` (Ligne tableau) | Modification de la catégorie | | gestionnaire et tresorier et admin et super admin|
| `/categorie_article/list` | `Activer / Désactiver` (Switch) | Change le statut de la catégorie | | gestionnaire et tresorier et admin et super admin|

### 3.2. Articles Catalogue
**URLs** : `/article/list`, `/article/formulaire`, `/article/edition/{id}`, `/article/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/article/list` | `Nouvel Article` | Formulaire de création d'article | | gestionnaire et tresorier et admin et super admin|
| `/article/list` | `Éditer` (Ligne tableau) | Modification d'un article | | gestionnaire et tresorier et admin et super admin|
| `/article/list` | `Détails` (Ligne tableau) | Fiche détaillée de l'article | | gestionnaire et tresorier et admin et super admin|
| `/article/list` | `Activer / Désactiver` (Switch) | Bascule du statut actif/inactif | | gestionnaire et tresorier et admin et super admin|
| `/article/details/{id}` | `Éditer Article` | Redirige vers l'édition d'article | | gestionnaire et tresorier et admin et super admin|

### 3.3. Catégories de Packs
**URLs** : `/categorie_pack/list`, `/categorie_pack/formulaire`, `/categorie_pack/edition/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/categorie_pack/list` | `Nouvelle Catégorie Pack` | Formulaire création catégorie pack | | gestionnaire et tresorier et admin et super admin|
| `/categorie_pack/list` | `Éditer` (Ligne tableau) | Modification catégorie pack | | gestionnaire et tresorier et admin et super admin|
| `/categorie_pack/list` | `Activer / Désactiver` (Switch) | Bascule statut | | gestionnaire et tresorier et admin et super admin|

### 3.4. Packs & Offres Produit
**URLs** : `/pack/list`, `/pack/formulaire`, `/pack/edition/{id}`, `/pack/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/pack/list` | `Actualiser` | Rafraîchit la liste et les 3 cartes KPI | | gestionnaire et tresorier et admin et super admin|
| `/pack/list` | `Nouveau Pack` | Formulaire de création de pack | | gestionnaire et tresorier et admin et super admin|
| `/pack/list` | `Éditer` (Ligne tableau) | Modification du pack | | gestionnaire et tresorier et admin et super admin|
| `/pack/list` | `Détails` (Ligne tableau) | Consulter les détails du pack | | gestionnaire et tresorier et admin et super admin|
| `/pack/list` | `Toggle Statut` (Switch) | Activer ou Désactiver le pack | | gestionnaire et tresorier et admin et super admin|
| `/pack/details/{id}` | `Modifier Pack` | Redirige vers le formulaire d'édition | | gestionnaire et tresorier et admin et super admin|

---

## 4. Module Clients & Souscriptions (Contrats)

### 4.1. Gestion des Clients
**URLs** : `/client/list`, `/client/formulaire`, `/client/edition/{id}`, `/client/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/client/list` | `Nouveau Client` | Formulaire d'ajout client | | agent comercial | gestionnaire
| `/client/list` | `Éditer` (Ligne tableau) | Modification des coordonnées d'un client | | gestionnaire 
| `/client/list` | `Détails` (Ligne tableau) | Fiche client et historique de ses souscriptions | | agent comercial en lecture seule | gestionnaire en lecture seule | tresorier et finance en lecture seule | admin en lecture seule | super admin en lecture seule|
| `/client/details/{id}` | `Modifier Client` | Formulaire édition client | | gestionnaire | tresorier et finance | admin | super admin|
| `/client/details/{id}` | `Nouveau Contrat` | Démarre la souscription pour ce client | | agent comercial | gestionnaire | 

### 4.2. Liste des Souscriptions / Contrats
**URLs** : `/souscription/list`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/souscription/list` | `Nouveau Contrat` | Ouvre l'assistant de souscription | |agent comercial 
| `/souscription/list` | `Situation & Encaissement` (Tableau) | Redirige vers l'écran d'encaissement | |  agent comercial | tresorier et finance avec permission speciale caisse ou consulter| super admin en lecture seule|
| `/souscription/list` | `Détails` (Tableau) | Consulter la fiche contrat | agent comercial | tresorier et finance en lecture seule| gestionnaire en lecture seule | admin en lecture seule | super admin en lecture seule|
| `/souscription/list` | `Éditer` (Tableau) | Modifier les paramètres du contrat | agent comercial en lecture seule |tresorier et finance en lecture seule|gestionnaire avec permission special modifier les contrats | admin | super admin|
| `/souscription/list` | `Activer / Désactiver` (Switch) | Changement de statut de la souscription  | admin | super admin|

### 4.3. Assistant de Souscription (Wizard)
**URL** : `/souscription/wizard`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/souscription/wizard` | `Rechercher Client` | Filtre les clients existants par nom/téléphone | | permission joker:consulter et editer les cotisations| agent comercial | tresorier et finance | gestionnaire | admin | super admin|
| `/souscription/wizard` | `Créer un nouveau Client` | Modal d'ajout rapide de client | | agent commercial
| `/souscription/wizard` | `Sélectionner / Ajouter Pack` | Ajoute un pack au panier de souscription | | agent commercial
| `/souscription/wizard` | `Retirer du panier` | Supprime un pack sélectionné | | agent commercial 
| `/souscription/wizard` | `Valider et Créer le Contrat` | Finalise la création de la souscription | | agent commercial

### 4.4. Fiche Contrat / Souscription
**URL** : `/souscription/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/souscription/details/{id}` | `Situation & Encaissement` | Ouvre le guichet de collecte/situation | | permission joker:consulter et editer les cotisations| agent tresoriere et finance avec permission speciale caisse ou consulter|
| `/souscription/details/{id}` | `Modifier Contrat` | Édition des clauses du contrat | | permission joker:consulter et editer les cotisations| agent gestionnaire ou admin ou super admin|
| `/souscription/details/{id}` | `Encaisser une Cotisation` | Raccourci d'encaissement direct | | permission joker:consulter et editer les cotisations| agent tresoriere et finance avec permission speciale caisse ou consulter|
| `/souscription/details/{id}` | `Retour` | Retourne à la liste des souscriptions | |

---

## 5. Module Cotisations & Encaissements Terrain

### 5.1. Guichet Encaissement & Situation
**URLs** : `/cautisation-payment/search-form`, `/cautisation-payment/situation/{code}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/cautisation-payment/search-form` | `Rechercher Contrat` | Recherche par code souscription ou client | | permission joker:consulter et editer les cotisations|
| `/cautisation-payment/situation/{code}` | `Valider l'Encaissement` | Saisie d'une nouvelle cotisation | | agent comercial, agent tresoriere et finance avec permission speciale caisse|
| `/cautisation-payment/situation/{code}` | `Imprimer Reçu / Fiche` | Génère la fiche/reçu de paiement | | agent tresoriere et finance avec permission speciale caisse | role adminitrateur en lecture seule | role agent comercial | permission joker:consulter et editer les cotisations|
| `/cautisation-payment/situation/{code}` | `Ouvrir la Caisse` | Ouvre la caisse journalière du commercial | | permission joker:consulter et editer les cotisations|

### 5.2. Suivi Général des Cotisations
**URLs** : `/cotisation/list`, `/cotisation/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/cotisation/list` | `Filtrer` | Filtrage des cotisations par date/zone | | agent tresoriere et finance en lecture seule | role adminitrateur en lecture seule | role agent comercial en lecture seule | permission joker:consulter et editer les cotisations| 
| `/cotisation/list` | `Exporter Excel / PDF` | Exportation des données de cotisations | | role adminitrateur en lecture seule | role agent comercial | role tresoriere et finance | permission joker
| `/cotisation/list` | `Détails` (Tableau) | Affiche le détail du paiement de la cotisation | | permission joker:consulter les cotisations | agent tresoriere et finance en lecture seule | role adminitrateur en lecture seule | role agent comercial en lecture seule

---

## 6. Module Trésorerie, Dépenses & Caisses

### 6.1. Versements Commerciaux
**URLs** : `/versement/list`, `/versement/formulaire`, `/versement/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/versement/list` | `Nouveau Versement` | Saisie d'un versement vers la caisse centrale | | agent comercial
| `/versement/list` | `Valider Versement` (Tableau) | Validation caisse centrale | | agent tresoriere et finance | 
| `/versement/list` | `Rejeter Versement` (Tableau) | Rejet du versement | | agent tresoriere et finance | 
| `/versement/details/{id}` | `Valider / Rejeter` | Validation ou rejet depuis la fiche versement | | agent tresoriere et finance | role adminitrateur | role agent comercial | 

### 6.2. Dépenses d'Exploitation & Types de Dépenses
**URLs** : `/depense/list`, `/depense/formulaire`, `/type_depense/list`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/depense/list` | `Nouvelle Dépense` | Saisie d'une dépense d'exploitation | | agent tresoriere et finance | role adminitrateur | 
| `/depense/list` | `Valider Dépense` | Validation par la comptabilité/finance | | agent tresoriere et finance | role adminitrateur |
| `/depense/list` | `Rejeter Dépense` | Rejet de la demande de dépense | | agent tresoriere et finance | role adminitrateur |
| `/type_depense/list` | `Nouveau Type` | Ajout d'une catégorie de dépense | | role adminitrateur | agent tresoriere et finance | 

### 6.3. Journal des Caisses
**URL** : `/caisse_commercial/list`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/caisse_commercial/list` | `Ouvrir Ma Caisse` | Ouverture de caisse du jour pour encaissement | | agent comercial
| `/caisse_commercial/list` | `Fermer Ma Caisse` | Clôture de caisse en fin de journée | |agent comercial
| `/caisse_commercial/list` | `Consulter Journal` | Consultation des mouvements de caisse | | agent comercial | role adminitrateur | agent tresoriere et finance |  role  | 

---

## 7. Module Logistique & Livraisons (Distributions)

### 7.1. Distributions & Procès-Verbaux
**URLs** : `/distribution/list`, `/distribution/formulaire`, `/distribution/details/{id}`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/distribution/list` | `Nouvelle Distribution` | Enregistrer une nouvelle livraison de pack | | manager distribution | agent ayant la permission joker | role adminitrateur |
| `/distribution/list` | `Détails / PV` (Tableau) | Consultation du PV de livraison | | manager distribution | agent ayant la permission joker | role adminitrateur |
| `/distribution/formulaire` | `Valider la Distribution (PV)` | Confirmation de remise des articles au client | | manager distribution | agent ayant la permission joker | role adminitrateur |
| `/distribution/details/{id}` | `Imprimer Procès-Verbal (PV)` | Impression du PV physique de livraison | | manager distribution | agent ayant la permission joker | role adminitrateur |

---

## 8. Module Administration, Sécurité & RBAC

### 8.1. Utilisateurs Système & Fonctions
**URLs** : `/user/list`, `/user/formulaire`, `/fonction/list`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/user/list` | `Nouvel Utilisateur` | Création d'un compte utilisateur | | role adminitrateur | agent ayant la permission joker |
| `/user/list` | `Éditer` (Tableau) | Modification du profil / rôle utilisateur | | role adminitrateur | agent ayant la permission joker |
| `/user/list` | `Détails` (Tableau) | Fiche utilisateur | |
| `/user/list` | `Toggle Statut` (Switch) | Activer ou Suspendre un utilisateur | | agent ayant la permission joker |  role adminitrateur
| `/fonction/list` | `Nouvelle Fonction` | Gestion des fonctions entreprise | | role adminitrateur | agent ayant la permission joker | 

### 8.2. Rôles & Permissions RBAC
**URLs** : `/role/list`, `/permission/list`, `/zone_commercial/list`

| Page | Bouton / Action | Description | Permission / Agent Autorisé (À compléter) |
|---|---|---|---|
| `/role/list` | `Nouveau Rôle` | Création d'un rôle d'accès | | agent ayant la permission joker |
| `/role/list` | `Éditer Permissions` | Matrice des permissions associées au rôle | | agent ayant la permission joker |
| `/permission/list` | `Nouvelle Permission` | Définition d'une permission granulaire | | agent ayant la permission joker |
| `/zone_commercial/list` | `Affecter Commercial` | Association d'un commercial à une zone géographique | | agent ayant la permission joker | role adminitrateur
