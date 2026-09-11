---
trigger: always_on
---

# Règle de Modification de Schéma BDD & Ajout de Colonnes

Avant d'exécuter toute modification de la structure de la base de données (`CREATE TABLE`, `ALTER TABLE`, ajout ou modification de colonnes) :

1. **Justification et Explication Préalable Obligatoire** :
   - Expliquer la raison fonctionnelle et technique nécessitant la création de la table ou l'ajout de la colonne.
   - Présenter la structure exacte proposée : nom de la table, nom de la colonne, type de données (VARCHAR, INT, DECIMAL, DATETIME, ENUM, etc.), contraintes (NULL, NOT NULL, DEFAULT, INDEX, etc.).

2. **Demande d'Autorisation Explicite** :
   - Ne JAMAIS exécuter de requête `ALTER TABLE` ou `CREATE TABLE` sans avoir préalablement expliqué le besoin et obtenu l'accord explicite de l'utilisateur.

3. **Mise à Jour Systématique de `database/olive.sql`** :
   - Une fois la modification validée et exécutée, mettre à jour immédiatement le fichier de référence `database/olive.sql` pour conserver la synchronisation entre la BDD et la référence du projet.

4. **Respect de la Nomenclature et des Filtres Contextuels** :
   - Respecter la convention de nommage du projet (`code_...`, `etablissement_code`, `zone_code`, `annee_code`, `user_code`, `created_at_...`, `updated_at_...`).
