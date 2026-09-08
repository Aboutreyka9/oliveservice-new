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