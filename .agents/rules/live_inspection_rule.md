---
trigger: always_on
---

# Règle d'Inspection de l'État Présent (Live State Check)

Avant d'exécuter toute tâche ou modification sur le projet :

1. **Inspection systématique en temps réel** :
   - Toujours lire l'état actuel et présent du projet (fichiers `.env`, structure de la base de données actuelle, fichiers de configuration actifs).
   - Ne jamais s'appuyer aveuglément sur le cache, les historiques de conversation précédents ou d'anciens logs si les données réelles du présent peuvent être lues directement sur le disque/base de données.

2. **Validation des cibles** :
   - Vérifier explicitement le fichier d'environnement `.env` actif et la base de données visée avant toute action de modification ou de suppression.

3. **Regles de redaction des interfaces generer** :
   - redige les interface base sur les modeles existants du projet. 
respecte l'identité visuel du projet, pas d'improvisation sur les styles generer.