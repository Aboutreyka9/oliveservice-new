---
trigger: always_on
---

# Règle d'Inspection de l'État Présent & Référence Schéma SQL

Avant d'exécuter toute tâche, génération d'interface ou modification sur le projet :

1. **Inspection du schéma et des données via `database/olive.sql`** :
   - Toujours consulter directement le fichier `database/olive.sql` pour vérifier la structure exacte des tables, les colonnes, les types de données, les contraintes et les valeurs d'initialisation.
   - Utiliser `database/olive.sql` (qui est la copie conforme de la base de données) comme référence principale pour la correspondance des champs et pour éviter d'exécuter des requêtes d'inspection inutiles directement en BDD.

2. **Inspection systématique de l'environnement** :
   - Lire le fichier d'environnement `.env` actif et les fichiers de configuration pour valider les paramètres cibles avant toute action de modification ou de suppression.

3. **Règles de rédaction des interfaces générées** :
   - Rédiger les interfaces basées sur les modèles et composants existants du projet.
   - Respecter strictement l'identité visuelle du projet (thème Navy & Émeraude, cartes premium, Lucide icons), sans aucune improvisation sur les styles générés.

4. **Règles de Filtrage Contextuel & Sécurité RBAC** :
   - Appliquer systématiquement le filtrage contextuel (`Context::applyTripleFilter` / `Context::applyScopeSQL`) sur les 10 tables cibles (`caisses`, `cautisation_clients`, `depenses`, `distributions`, `pack_articles`, `pack_souscriptions`, `packs`, `sessions`, `souscriptions`, `versements_commerciaux`).
   - Le filtrage contextuel doit valider strictly `etablissement_code`, `zone_code` et `annee_code` selon les valeurs actives de la session utilisateur.
   - Pour les commerciaux (`ROLE_COMMERCIAL`), restreindre uniquement à leur `user_code` / `commercial_code`.
   - La permission `MAIN_ACCESS` est un Pass-Partout/Joker exclusivement réservé aux administrateurs (`ROLE_ADMIN`, `ROLE_SUPERADMIN`) permettant de contourner l'absence d'éléments de contexte (année, zone, établissement) pour effectuer les paramétrages requis.

5. **Interdiction Stricte des Valeurs Hard-Codées (No Hardcoding Rule)** :
   - Ne JAMAIS inscrire de valeurs hard-codées (montants, compteurs, données statiques de repli, rôles ou identifiants en dur) dans les contrôleurs, modèles ou vues du projet.
   - Tous les calculs (totaux, soldes restants, durées, taux de progression, statistiques) et états doivent TOUJOURS être évalués et dérivés dynamiquement en temps réel à partir des données réelles de la base de données (`database/olive.sql`).