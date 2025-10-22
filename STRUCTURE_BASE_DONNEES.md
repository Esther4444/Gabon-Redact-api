# 📊 STRUCTURE DE LA BASE DE DONNÉES - RedacGabon Pro API

**Base de données** : `redact-db`  
**Type** : MySQL 8  
**Taille totale** : 0.67 MiB  
**Nombre de tables** : 17  
**Date de génération** : 8 octobre 2025

---

## 📑 TABLE DES MATIÈRES

1. [Tables Système Laravel](#-1-tables-système-laravel)
2. [Tables Métier (Francisées)](#-2-tables-métier-francisées)
3. [Schéma des Relations](#-3-schéma-des-relations)
4. [Index et Performances](#-4-index-et-performances)
5. [Résumé de l'Architecture](#-5-résumé-de-larchitecture)

---

## 🔐 1. TABLES SYSTÈME LARAVEL

Ces tables restent en **anglais** pour garantir la compatibilité avec Laravel, Sanctum et les packages tiers.

---

### 👤 **users**

**Description** : Table d'authentification principale  
**Colonnes** : 12 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `name` | string | Non | - | Nom de l'utilisateur |
| `email` | string | Non | - | Email (unique) |
| `email_verified_at` | datetime | Oui | null | Date de vérification de l'email |
| `password` | string | Non | - | Mot de passe hashé |
| `remember_token` | string | Oui | null | Token de session persistante |
| **`est_actif`** 🇫🇷 | boolean | Non | 1 | Compte actif ou non |
| **`derniere_connexion_le`** 🇫🇷 | datetime | Oui | null | Date et heure de la dernière connexion |
| **`tentatives_connexion_echouees`** 🇫🇷 | integer | Non | 0 | Nombre de tentatives échouées |
| **`verrouille_jusqu_au`** 🇫🇷 | datetime | Oui | null | Date de fin de verrouillage du compte |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de dernière modification |

**Index** :
- PRIMARY KEY : `id`
- UNIQUE : `email`
- COMPOUND : `(est_actif, verrouille_jusqu_au)`
- INDEX : `derniere_connexion_le`

**Relations** :
- `users` → `profils` (1:1)
- `users` → `dossiers` (1:N)
- `users` → `articles` (1:N - créateur, assigné, réviseur)
- `users` → `commentaires` (1:N)
- `users` → `messages` (1:N - expéditeur/destinataire)
- `users` → `notifications` (1:N)
- `users` → `medias` (1:N)
- `users` → `workflow_articles` (1:N)
- `users` → `invitations_equipe` (1:N)
- `users` → `evenements_analytiques` (1:N)
- `users` → `journaux_audit` (1:N)

---

### 🔑 **password_reset_tokens**

**Description** : Gestion des réinitialisations de mot de passe (Laravel standard)  
**Colonnes** : 3 | **Taille** : 0.02 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `email` | string | Non | - | Email (clé primaire) |
| `token` | string | Non | - | Token de réinitialisation |
| `created_at` | datetime | Oui | null | Date de création du token |

**Index** :
- PRIMARY KEY : `email`

---

### 🎫 **personal_access_tokens**

**Description** : Laravel Sanctum - Gestion des tokens API  
**Colonnes** : 10 | **Taille** : 0.02 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `tokenable_type` | string | Non | - | Type de modèle (polymorphique) |
| `tokenable_id` | bigint (unsigned) | Non | - | ID du modèle |
| `name` | string | Non | - | Nom du token |
| `token` | string | Non | - | Hash du token (unique) |
| `abilities` | text | Oui | null | Permissions JSON |
| `last_used_at` | datetime | Oui | null | Date de dernière utilisation |
| `expires_at` | datetime | Oui | null | Date d'expiration |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- UNIQUE : `token`
- COMPOUND : `(tokenable_type, tokenable_id)`

---

### ❌ **failed_jobs**

**Description** : Laravel Queue - Jobs échoués  
**Colonnes** : 7 | **Taille** : 0.02 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `uuid` | string | Non | - | UUID unique |
| `connection` | text | Non | - | Nom de la connexion |
| `queue` | text | Non | - | Nom de la queue |
| `payload` | text | Non | - | Données du job |
| `exception` | text | Non | - | Exception levée |
| `failed_at` | datetime | Non | CURRENT_TIMESTAMP | Date d'échec |

**Index** :
- PRIMARY KEY : `id`
- UNIQUE : `uuid`

---

## 🇫🇷 2. TABLES MÉTIER (FRANCISÉES)

Ces tables représentent la logique métier de l'application et sont francisées pour une meilleure lisibilité.

---

### 👥 **profils**

**Description** : Profils utilisateurs étendus  
**Colonnes** : 8 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `user_id` | bigint (unsigned) | Non | - | → users.id (FK) |
| `nom_complet` | string | Non | - | Nom complet de l'utilisateur |
| `url_avatar` | string | Oui | null | URL de l'avatar |
| `role` | string | Non | - | Rôle (redacteur, reviseur, directeur_publication, admin) |
| `preferences` | json | Oui | null | Préférences utilisateur |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `user_id` → `users.id` (ON DELETE CASCADE)
- INDEX : `role`

**Valeurs possibles pour `role`** :
- `redacteur` : Rédacteur d'articles
- `reviseur` : Réviseur d'articles
- `directeur_publication` : Directeur de publication
- `admin` : Administrateur système

---

### 📁 **dossiers**

**Description** : Organisation des articles en dossiers  
**Colonnes** : 5 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `owner_id` | bigint (unsigned) | Non | - | → users.id (FK - propriétaire) |
| `nom` | string | Non | - | Nom du dossier |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `owner_id` → `users.id` (ON DELETE CASCADE)

---

### 📄 **articles** ⭐

**Description** : Articles et contenus rédactionnels (table principale)  
**Colonnes** : 23 | **Taille** : 0.13 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `dossier_id` | bigint (unsigned) | Oui | null | → dossiers.id (FK) |
| `created_by` | bigint (unsigned) | Non | - | → users.id (FK - auteur) |
| `assigned_to` | bigint (unsigned) | Oui | null | → users.id (FK - assigné à) |
| `current_reviewer_id` | bigint (unsigned) | Oui | null | → users.id (FK - réviseur actuel) |
| `titre` | string | Non | - | Titre de l'article |
| `slug` | string | Non | - | Slug unique pour URL |
| `contenu` | text | Oui | null | Contenu HTML/Markdown |
| `statut` | string | Non | - | Statut de publication |
| `statut_workflow` | string | Non | 'draft' | Statut dans le workflow |
| `titre_seo` | string | Oui | null | Titre SEO optimisé |
| `description_seo` | text | Oui | null | Description SEO |
| `mots_cles_seo` | json | Oui | null | Mots-clés SEO (array) |
| `publie_le` | datetime | Oui | null | Date de publication |
| `soumis_le` | datetime | Oui | null | Date de soumission pour révision |
| `relu_le` | datetime | Oui | null | Date de relecture |
| `approuve_le` | datetime | Oui | null | Date d'approbation |
| `raison_rejet` | text | Oui | null | Raison du rejet (si rejeté) |
| `historique_workflow` | json | Oui | null | Historique complet du workflow |
| `metadonnees` | json | Oui | null | Métadonnées additionnelles |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |
| `deleted_at` | datetime | Oui | null | Date de suppression (soft delete) |

**Index** :
- PRIMARY KEY : `id`
- UNIQUE : `slug`
- FOREIGN KEY : `dossier_id` → `dossiers.id` (ON DELETE SET NULL)
- FOREIGN KEY : `created_by` → `users.id` (ON DELETE CASCADE)
- FOREIGN KEY : `assigned_to` → `users.id` (ON DELETE SET NULL)
- FOREIGN KEY : `current_reviewer_id` → `users.id` (ON DELETE SET NULL)
- INDEX : `statut`
- INDEX : `statut_workflow`
- INDEX : `publie_le`
- INDEX : `current_reviewer_id`

**Valeurs possibles pour `statut`** :
- `draft` : Brouillon
- `published` : Publié
- `archived` : Archivé

**Valeurs possibles pour `statut_workflow`** :
- `draft` : En cours de rédaction
- `submitted` : Soumis pour révision
- `in_review` : En révision
- `approved` : Approuvé
- `rejected` : Rejeté
- `published` : Publié

---

### 💬 **commentaires**

**Description** : Commentaires sur les articles  
**Colonnes** : 6 | **Taille** : 0.05 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `article_id` | bigint (unsigned) | Non | - | → articles.id (FK) |
| `author_id` | bigint (unsigned) | Non | - | → users.id (FK - auteur) |
| `contenu` | text | Non | - | Contenu du commentaire |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `article_id` → `articles.id` (ON DELETE CASCADE)
- FOREIGN KEY : `author_id` → `users.id` (ON DELETE CASCADE)

---

### 📧 **messages**

**Description** : Système de messagerie interne entre utilisateurs  
**Colonnes** : 12 | **Taille** : 0.08 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `sender_id` | bigint (unsigned) | Non | - | → users.id (FK - expéditeur) |
| `recipient_id` | bigint (unsigned) | Non | - | → users.id (FK - destinataire) |
| `article_id` | bigint (unsigned) | Oui | null | → articles.id (FK - contexte) |
| `message_parent_id` | bigint (unsigned) | Oui | null | → messages.id (FK - réponse) |
| `sujet` | string | Non | - | Sujet du message |
| `contenu` | text | Non | - | Contenu du message |
| `est_lu` | boolean | Non | 0 | Message lu ou non |
| `pieces_jointes` | json | Oui | null | Pièces jointes (array) |
| `lu_le` | datetime | Oui | null | Date de lecture |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `sender_id` → `users.id` (ON DELETE CASCADE)
- FOREIGN KEY : `recipient_id` → `users.id` (ON DELETE CASCADE)
- FOREIGN KEY : `article_id` → `articles.id` (ON DELETE SET NULL)
- FOREIGN KEY : `message_parent_id` → `messages.id` (ON DELETE SET NULL)
- COMPOUND : `(recipient_id, est_lu)`
- COMPOUND : `(sender_id, created_at)`
- INDEX : `article_id`

---

### 🔔 **notifications**

**Description** : Système de notifications  
**Colonnes** : 9 | **Taille** : 0.05 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `user_id` | bigint (unsigned) | Non | - | → users.id (FK) |
| `type` | string | Non | - | Type de notification |
| `titre` | string | Oui | null | Titre de la notification |
| `message` | text | Non | - | Contenu de la notification |
| `lu` | boolean | Non | 0 | Notification lue ou non |
| `donnees` | json | Oui | null | Données contextuelles |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `user_id` → `users.id` (ON DELETE CASCADE)
- INDEX : `lu`

**Valeurs possibles pour `type`** :
- `workflow` : Notification de workflow
- `message` : Nouveau message
- `comment` : Nouveau commentaire
- `system` : Notification système

---

### 🖼️ **medias**

**Description** : Gestion des fichiers médias (images, documents)  
**Colonnes** : 9 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `user_id` | bigint (unsigned) | Non | - | → users.id (FK - propriétaire) |
| `disque` | string | Non | 'public' | Disque de stockage Laravel |
| `chemin` | string | Non | - | Chemin du fichier |
| `type_mime` | string | Oui | null | Type MIME |
| `taille_octets` | bigint (unsigned) | Oui | null | Taille en octets |
| `metadonnees` | json | Oui | null | Métadonnées (dimensions, etc.) |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `user_id` → `users.id` (ON DELETE CASCADE)

---

### 🔄 **workflow_articles**

**Description** : Historique et suivi du workflow des articles  
**Colonnes** : 10 | **Taille** : 0.06 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `article_id` | bigint (unsigned) | Non | - | → articles.id (FK) |
| `from_user_id` | bigint (unsigned) | Oui | null | → users.id (FK - expéditeur) |
| `to_user_id` | bigint (unsigned) | Non | - | → users.id (FK - destinataire) |
| `action` | string | Non | - | Action effectuée |
| `statut` | string | Non | - | Statut de l'étape |
| `commentaire` | text | Oui | null | Commentaire de l'action |
| `action_le` | datetime | Oui | null | Date de l'action |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `article_id` → `articles.id` (ON DELETE CASCADE)
- FOREIGN KEY : `from_user_id` → `users.id` (ON DELETE SET NULL)
- FOREIGN KEY : `to_user_id` → `users.id` (ON DELETE CASCADE)
- COMPOUND : `(article_id, statut)`
- COMPOUND : `(to_user_id, statut)`

**Valeurs possibles pour `action`** :
- `submitted` : Soumis
- `reviewed` : Révisé
- `approved` : Approuvé
- `rejected` : Rejeté
- `published` : Publié

**Valeurs possibles pour `statut`** :
- `pending` : En attente
- `completed` : Complété
- `rejected` : Rejeté

---

### 👫 **invitations_equipe**

**Description** : Invitations pour rejoindre l'équipe  
**Colonnes** : 9 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `invited_by` | bigint (unsigned) | Non | - | → users.id (FK - inviteur) |
| `email` | string | Non | - | Email de l'invité |
| `role` | string | Non | - | Rôle proposé |
| `jeton` | string | Non | - | Token unique d'invitation |
| `expire_le` | datetime | Oui | null | Date d'expiration |
| `accepte_le` | datetime | Oui | null | Date d'acceptation |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- UNIQUE : `jeton`
- FOREIGN KEY : `invited_by` → `users.id` (ON DELETE CASCADE)
- INDEX : `email`

---

### 📅 **planifications_publication**

**Description** : Planification de publication d'articles  
**Colonnes** : 8 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `article_id` | bigint (unsigned) | Non | - | → articles.id (FK) |
| `planifie_pour` | datetime | Non | - | Date et heure de publication prévue |
| `canal` | string | Oui | null | Canal de publication (web, social, etc.) |
| `statut` | string | Non | 'pending' | Statut de la planification |
| `raison_echec` | text | Oui | null | Raison d'échec (si échec) |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `article_id` → `articles.id` (ON DELETE CASCADE)
- INDEX : `planifie_pour`
- INDEX : `statut`

**Valeurs possibles pour `statut`** :
- `pending` : En attente
- `processing` : En cours
- `published` : Publié
- `failed` : Échoué

---

### 📊 **evenements_analytiques**

**Description** : Suivi des événements et analytics  
**Colonnes** : 7 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `user_id` | bigint (unsigned) | Oui | null | → users.id (FK - utilisateur) |
| `type_evenement` | string | Non | - | Type d'événement |
| `proprietes` | json | Oui | null | Propriétés de l'événement |
| `survenu_le` | datetime | Non | - | Date de l'événement |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `user_id` → `users.id` (ON DELETE SET NULL)
- INDEX : `type_evenement`
- INDEX : `survenu_le`

**Types d'événements** :
- `article_viewed` : Article consulté
- `article_created` : Article créé
- `article_published` : Article publié
- `login` : Connexion utilisateur
- `logout` : Déconnexion utilisateur

---

### 📜 **journaux_audit**

**Description** : Journal d'audit pour traçabilité  
**Colonnes** : 9 | **Taille** : 0.03 MiB

| Colonne | Type | Nullable | Défaut | Description |
|---------|------|----------|--------|-------------|
| `id` | bigint (unsigned, auto) | Non | - | Identifiant unique |
| `actor_id` | bigint (unsigned) | Oui | null | → users.id (FK - acteur) |
| `action` | string | Non | - | Action effectuée |
| `type_entite` | string | Non | - | Type d'entité (Article, User, etc.) |
| `entite_id` | bigint (unsigned) | Non | - | ID de l'entité concernée |
| `contexte` | json | Oui | null | Contexte de l'action |
| `survenu_le` | datetime | Non | - | Date de l'action |
| `created_at` | datetime | Oui | null | Date de création |
| `updated_at` | datetime | Oui | null | Date de modification |

**Index** :
- PRIMARY KEY : `id`
- FOREIGN KEY : `actor_id` → `users.id` (ON DELETE SET NULL)
- COMPOUND : `(type_entite, entite_id)`
- INDEX : `survenu_le`

**Valeurs possibles pour `action`** :
- `created` : Création
- `updated` : Modification
- `deleted` : Suppression
- `viewed` : Consultation

---

## 🔗 3. SCHÉMA DES RELATIONS

### Diagramme relationnel simplifié

```
users (1) ──→ (1) profils
users (1) ──→ (N) dossiers
users (1) ──→ (N) articles [created_by]
users (1) ──→ (N) articles [assigned_to]
users (1) ──→ (N) articles [current_reviewer_id]
users (1) ──→ (N) commentaires
users (1) ──→ (N) messages [sender_id]
users (1) ──→ (N) messages [recipient_id]
users (1) ──→ (N) notifications
users (1) ──→ (N) medias
users (1) ──→ (N) workflow_articles [from_user_id]
users (1) ──→ (N) workflow_articles [to_user_id]
users (1) ──→ (N) invitations_equipe
users (1) ──→ (N) evenements_analytiques
users (1) ──→ (N) journaux_audit

dossiers (1) ──→ (N) articles

articles (1) ──→ (N) commentaires
articles (1) ──→ (N) messages
articles (1) ──→ (N) workflow_articles
articles (1) ──→ (N) planifications_publication

messages (1) ──→ (N) messages [réponses - self-referencing]
```

### Relations clés

#### 🔹 **users → profils** (One-to-One)
Chaque utilisateur a un profil unique avec des informations étendues.

#### 🔹 **users → articles** (One-to-Many × 3)
- `created_by` : Auteur de l'article
- `assigned_to` : Personne assignée
- `current_reviewer_id` : Réviseur actuel

#### 🔹 **articles → workflow_articles** (One-to-Many)
Suivi complet du parcours de l'article dans le système de révision.

#### 🔹 **users ↔ messages** (Many-to-Many self)
Messagerie bidirectionnelle avec support de threads (réponses).

---

## ⚡ 4. INDEX ET PERFORMANCES

### Index de performances critiques

#### **Table articles** (la plus sollicitée)
- `slug` (UNIQUE) : Recherche rapide par URL
- `statut` : Filtrage par statut de publication
- `statut_workflow` : Filtrage par état du workflow
- `publie_le` : Tri chronologique des publications
- `current_reviewer_id` : Assignation des révisions

#### **Table messages**
- `(recipient_id, est_lu)` : Messages non lus par destinataire
- `(sender_id, created_at)` : Historique d'envoi

#### **Table workflow_articles**
- `(article_id, statut)` : Historique du workflow par article
- `(to_user_id, statut)` : Tâches en attente par utilisateur

#### **Table notifications**
- `lu` : Notifications non lues (très fréquent)

#### **Table users**
- `email` : Authentification (unique)
- `(est_actif, verrouille_jusqu_au)` : Vérification de compte

---

## 📊 5. RÉSUMÉ DE L'ARCHITECTURE

### ✅ **Architecture Hybride Franco-Anglaise**

| Élément | Langue | Raison |
|---------|--------|--------|
| **Tables système** | 🇬🇧 Anglais | Compatibilité Laravel, Sanctum, packages tiers |
| **Tables métier** | 🇫🇷 Français | Lisibilité métier, équipe francophone |
| **Timestamps** | 🇬🇧 Anglais | Convention Laravel standard |
| **Foreign keys** | 🇬🇧 Anglais | Cohérence avec table `users` |
| **Colonnes métier** | 🇫🇷 Français | ~120 colonnes francisées |
| **Colonnes custom `users`** | 🇫🇷 Français | 4 colonnes spécifiques métier |

---

### 📈 **Statistiques**

- **Total tables** : 17
- **Tables système** : 4 (users, password_reset_tokens, personal_access_tokens, failed_jobs)
- **Tables métier francisées** : 13
- **Colonnes totales** : ~150
- **Colonnes francisées** : ~120
- **Foreign keys** : ~20
- **Index de performance** : ~35
- **Taille actuelle** : 0.67 MiB
- **Capacité de stockage** : Évolutive (MySQL 8)

---

### 🎯 **Points forts de cette structure**

1. ✅ **Compatibilité Laravel** : Authentification native préservée
2. ✅ **Sanctum API** : Tokens fonctionnent sans modification
3. ✅ **Packages tiers** : Pas de conflit avec packages standards
4. ✅ **Migrations futures** : Laravel peut évoluer sans problème
5. ✅ **Best practices** : Respect des conventions Laravel
6. ✅ **Francisation métier** : Tables business en français
7. ✅ **Performance** : Index optimisés pour requêtes fréquentes
8. ✅ **Traçabilité** : Audit logs et workflow complets
9. ✅ **Soft deletes** : Articles récupérables (deleted_at)
10. ✅ **Extensibilité** : JSON pour données flexibles

---

### 🔐 **Sécurité**

- ✅ **Hachage bcrypt** : Mots de passe sécurisés
- ✅ **Tokens Sanctum** : API sécurisée
- ✅ **Verrouillage de compte** : Protection contre brute-force
- ✅ **Audit logs** : Traçabilité complète des actions
- ✅ **Foreign keys** : Intégrité référentielle
- ✅ **Soft deletes** : Pas de perte de données

---

### 🚀 **Évolutions possibles**

1. **Tags pour articles** : Table `tags` + `article_tag` (many-to-many)
2. **Versions d'articles** : Table `article_versions` pour historique complet
3. **Permissions granulaires** : Table `permissions` + `role_permission`
4. **Favoris** : Table `favoris` (utilisateur ↔ article)
5. **Statistiques temps réel** : Table `article_stats` (vues, likes, partages)
6. **Multilingue** : Tables `article_translations`
7. **Catégories** : Table `categories` avec hiérarchie
8. **API externe** : Table `webhooks` pour notifications externes

---

## 📝 **Conventions de nommage**

### Tables
- **Système** : anglais, pluriel (`users`, `failed_jobs`)
- **Métier** : français, pluriel (`articles`, `dossiers`)
- **Pivot** : nom composé (`article_tag`, si créée)

### Colonnes
- **ID** : `id` (bigint unsigned auto_increment)
- **Foreign keys** : `{table}_id` ou `{role}_id` (ex: `user_id`, `created_by`)
- **Timestamps** : `created_at`, `updated_at`, `deleted_at`
- **Booléens** : `est_{adjectif}` (ex: `est_actif`, `est_lu`)
- **Dates** : `{action}_le` (ex: `publie_le`, `soumis_le`)
- **JSON** : pluriel (ex: `metadonnees`, `proprietes`)

### Index
- **Primary** : `PRIMARY`
- **Unique** : `{table}_{colonne}_unique`
- **Foreign** : `{table}_{colonne}_foreign`
- **Index simple** : `{table}_{colonne}_index`
- **Compound** : `{table}_{col1}_{col2}_index`

---

## 📚 **Commandes utiles**

```bash
# Afficher toutes les tables
php artisan db:show

# Afficher structure d'une table
php artisan db:table {nom_table}

# Vérifier l'état des migrations
php artisan migrate:status

# Backup de la base
mysqldump -u root -p redact-db > backup_$(date +%Y%m%d).sql

# Restaurer un backup
mysql -u root -p redact-db < backup_20251008.sql

# Optimiser les tables
php artisan db:table-size

# Générer un diagramme ERD (nécessite extension)
php artisan db:diagram
```

---

## 📄 **Historique des migrations**

| Date | Migration | Description |
|------|-----------|-------------|
| 2025-10-07 | `renommer_tables_et_colonnes_en_francais` | Francisation des tables et colonnes métier |
| 2025-10-08 | `remettre_timestamps_en_anglais` | Retour des timestamps en anglais standard |

---

**Document généré le** : 8 octobre 2025  
**Version de la base** : 1.0  
**Version de Laravel** : 10.x  
**Version de MySQL** : 8.0  
**Environnement** : Laragon (Windows)

---

**Auteur** : RedacGabon Pro Team  
**Statut** : ✅ Production Ready

