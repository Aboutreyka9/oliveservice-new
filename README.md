# 🫒 Olive Service - Guide du Projet & Collaboration

Bienvenue dans le dépôt du projet **Olive Service**. Ce document constitue la référence centrale pour les développeurs et l'assistant IA. Il résume les règles de collaboration, l'architecture du projet, les spécifications métier et l'état des modules.

---

## 📜 1. Règles Inviolables de Collaboration

> [!IMPORTANT]
> **Règle 1 : Alignement Base de Données (`database/olive.sql`)**  
> Avant de générer une quelconque interface ou un code PHP, vérifiez systématiquement les champs et relations dans `database/olive.sql` afin de garantir une correspondance parfaite des données.

> [!WARNING]
> **Règle 2 : Modifications de Base de Données**  
> Toute modification de structure SQL doit être expliquée clairement avec ses raisons métier avant d'être exécutée.

> [!NOTE]
> **Règle 3 : Architecture MVC Épurée**  
> Conservez la structure MVC stricte (`controllers/`, `models/`, `views/`, `core/`, `public/`). Aucun contrôleur ou modèle obsolète ne doit polluer le code actif.

---

## 💡 2. Présentation d'Olive Service

**Olive Service** est une plateforme de gestion commerciale et d'épargne/cotisation permettant aux clients de souscrire à des packs de produits (alimentaires, électroménager, événementiels...).

### 🔄 Flux métier principal :
1. **Souscription** : Le client s'inscrit et choisit un ou plusieurs packs de produits pour une session donnée.
2. **Cotisation quotidienne/flexible** : Les commerciaux collectent les cotisations sur le terrain (au jour le jour ou par versement groupé de plusieurs jours).
3. **Gestion de Trésorerie & Caisse** : Les commerciaux effectuent leurs versements auprès de la caisse (gestionnaires/comptables).
4. **Distribution** : Une fois la souscription entièrement soldée et la session clôturée, le client récupère les articles de son pack lors de la distribution.

---

## 🔑 3. Formules de Calcul Financier Officielles

- **Prix de cotisation journalière du pack** = `packs.prix_cotisation_pack`
- **Total Souscription** = $\sum(\text{prix\_cotisation\_pack}) \times \text{nombre\_jour\_session}$
- **Montant Total Payé** = $\sum(\text{montant\_cautisation\_client})$
- **Solde Restant à Payer** = $\text{Total Souscription} - \text{Montant Total Payé}$
- **Nombre de Jours Payés** = $\sum(\text{nombre\_jour})$
- **Nombre de Jours Restants** = $\text{nombre\_jour\_session} - \text{Nombre de Jours Payés}$

---

## 📂 4. Structure des Modules Actifs

```
/var/www/html/geicg/
├── config/                  # Configuration (Database connection, constantes)
├── core/                    # Moteur MVC (Router, BaseController, BaseModel, Context, Validator)
├── controllers/             # Contrôleurs actifs par module métier
│   ├── home/                # Tableau de bord
│   ├── users/               # Gestion des utilisateurs
│   ├── annees/              # Années d'activité
│   ├── sessions/            # Sessions de cotisation
│   ├── zones/               # Zones géographiques
│   ├── categories_articles/ # Catégories d'articles
│   ├── articles/            # Articles produits
│   ├── categorie_packs/     # Catégories de packs
│   ├── packs/               # Packs & Offres
│   ├── clients/             # Fichier clients
│   ├── zone_commercials/    # Zones commerciales
│   ├── souscriptions/       # Souscriptions clients
│   ├── cotisations/         # Paiements cautisations (CautisationPaymentController)
│   ├── distributions/       # Retraits & livraisons de packs
│   ├── ouvertures_caisse/   # Ouvertures de caisse
│   ├── clotures_caisse/     # Clôtures de caisse
│   ├── type_depenses/       # Catégories de dépenses
│   ├── depenses/            # Saisie des dépenses
│   ├── versements/          # Versements des commerciaux
│   ├── roles/ & permissions/# Habilitations & accès
│   └── notifications/       # Système de notifications
├── models/                  # Modèles de données PDO
├── views/                   # Interfaces utilisateur PHP / Bootstrap / DataTables / Lucide
├── database/
│   └── olive.sql            # Schéma de référence SQL
└── public/                  # Point d'entrée principal (index.php, inc/ header/nav/sidebar)
```

---

## 🗄️ 5. Identifiants de Base de Données

- **Hôte** : `localhost` / `127.0.0.1`
- **Nom de la base** : `olive`
- **Utilisateur** : `root`
- **Mot de passe** : `root`
- **Charset** : `utf8mb4`

---

## 📌 6. État d'Avancement des Tâches (Roadmap)

- [x] Migration de la base de données vers `olive.sql`.
- [x] Nettoyage et suppression définitive des modules scolaires/pressing obsolètes (`trash/`).
- [x] Implémentation du module de paiement des cotisations (`cautisation-payment/situation`).
- [x] Mise à jour des calculs financiers dynamiques sur la liste des souscriptions (`views/souscriptions/list.php`).
- [x] Ajout du bouton d'accès direct "Situation" dans les souscriptions.
- [x] Colonne Montant Total (`prix_cotisation_pack * nombre_jour_session`) et regroupement par Année/Zone sur les packs (`views/packs/list.php`).
- [ ] Audit des accès et permissions selon les rôles.
- [ ] Tableaux de bord de suivi financier et commercial en temps réel.
