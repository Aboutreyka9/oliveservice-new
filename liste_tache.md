# 📌 LISTE DES TÂCHES - OLIVE SERVICE

Ce fichier permet de suivre la progression de l'adaptation et de la création des modules CRUD du projet **Olive Service**.
Lorsqu'une tâche est terminée et validée, elle est marquée : **`tache clean`**.

---

## 🛠️ Module 0 : Référentiels & Administration de Base
- [x] Tâche 0.1 : Mise à jour de `config/const.php` (Titre, Logo, Définition exacte des 24 tables `olive` et statuts métier) - **tache clean**
- [x] Tâche 0.2 : Contrôleur et Modèle `etablissements` (EtablissementController / ModelEtablissement) - **tache clean**
- [x] Tâche 0.3 : Contrôleur et Modèle `annees` & `sessions` (AnneeController / SessionController) - **tache clean**
- [x] Tâche 0.4 : Contrôleur et Modèle `zones` (ZoneController / ModelZone) - **tache clean**
- [x] Tâche 0.5 : Contrôleur et Modèle `fonctions`, `roles`, `permissions` & `users` (Gestion RBAC) - **tache clean**

## 📦 Module 1 : Catalogue Articles & Packs
- [x] Tâche 1.1 : CRUD Articles (`articles` - ArticleController / ModelArticle / Vues List, Edit, Details) - **tache clean**
- [x] Tâche 1.2 : CRUD Catégories de Packs (`categorie_packs` - CategoriePackController / ModelCategoriePack) - **tache clean**
- [x] Tâche 1.3 : CRUD Packs & Composition Articles (`packs`, `pack_articles` - PackController / ModelPack) - **tache clean**

## 👤 Module 2 : Clients & Commercialisation
- [x] Tâche 2.1 : CRUD Clients (`clients` - ClientController / ModelClient) - **tache clean**
- [x] Tâche 2.2 : Affectation Commercial / Zone (`zone_commercials` - ZoneCommercialController) - **tache clean**

## 💳 Module 3 : Souscriptions & Cotisations Clients (Cœur Métier)
- [x] Tâche 3.1 : CRUD Souscriptions & Packs Souscrits (`souscriptions`, `pack_souscriptions`) - **tache clean**
- [x] Tâche 3.2 : CRUD Cotisations Clients (`cautisation_clients` - CotisationController / ModelCotisation) - **tache clean**

## 🚚 Module 4 : Logistique & Distributions
- [x] Tâche 4.1 : Suivi et validation des Distributions de Packs (`distributions` - DistributionController / ModelDistribution) - **tache clean**

## 💰 Module 5 : Finances, Dépenses & Versements
- [x] Tâche 5.1 : Gestion des Type Dépenses & Dépenses d'Exploitation (`type_depenses`, `depenses`) - **tache clean**
- [x] Tâche 5.2 : Suivi des Paiements & Versements Commerciaux (`paiements`, `versements_commerciaux`) - **tache clean**
- [x] Tâche 5.3 : Tableau de bord et Synthèse Financière (HomeController / Dashboard Data) - **tache clean**
