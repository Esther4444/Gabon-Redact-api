# 🇫🇷 Résumé Complet des Tables Francisées

## 📊 Vue d'Ensemble du Projet

**Projet** : Dossier Redac Pro  
**Statut** : Migration de francisation prête  
**Date** : 8 octobre 2025  

---

## 🗄️ Tables de la Base de Données (APRÈS Francisation)

### 1. **utilisateurs** (users)
Table principale pour les comptes utilisateurs.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `nom` | VARCHAR | Nom de l'utilisateur |
| `email` | VARCHAR (unique) | Adresse email |
| `email_verifie_le` | TIMESTAMP | Date de vérification email |
| `mot_de_passe` | VARCHAR (hashed) | Mot de passe crypté |
| `jeton_souvenir` | VARCHAR | Remember token |
| `est_actif` | BOOLEAN | Compte actif ou non |
| `derniere_connexion_le` | TIMESTAMP | Dernière connexion |
| `tentatives_connexion_echouees` | INTEGER | Compteur tentatives |
| `verrouille_jusqu_au` | TIMESTAMP | Date de déverrouillage |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `hasOne` → profils (via `utilisateur_id`)
- `hasMany` → articles (via `cree_par`)
- `hasMany` → dossiers (via `proprietaire_id`)

---

### 2. **profils** (profiles)
Profils utilisateurs avec rôles et préférences.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `utilisateur_id` | BIGINT (FK) | Référence à utilisateurs |
| `nom_complet` | VARCHAR | Nom complet |
| `url_avatar` | VARCHAR | URL de l'avatar |
| `role` | VARCHAR (indexed) | Rôle (journaliste, etc.) |
| `preferences` | JSON | Préférences utilisateur |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `utilisateur_id`)

---

### 3. **dossiers** (folders)
Organisation des articles en dossiers.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `proprietaire_id` | BIGINT (FK) | Référence à utilisateurs |
| `nom` | VARCHAR | Nom du dossier |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `proprietaire_id`)
- `hasMany` → articles (via `dossier_id`)

---

### 4. **articles**
Table principale pour les articles.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `titre` | VARCHAR | Titre de l'article |
| `slug` | VARCHAR (unique) | Slug URL |
| `contenu` | LONGTEXT | Contenu de l'article |
| `statut` | VARCHAR (indexed) | Statut général |
| `statut_workflow` | VARCHAR (indexed) | Statut du workflow |
| `dossier_id` | BIGINT (FK) | Référence à dossiers |
| `cree_par` | BIGINT (FK) | Créateur (utilisateurs) |
| `assigne_a` | BIGINT (FK) | Assigné à (utilisateurs) |
| `relecteur_actuel_id` | BIGINT (FK) | Relecteur actuel |
| `titre_seo` | VARCHAR | Titre SEO |
| `description_seo` | TEXT | Description SEO |
| `mots_cles_seo` | JSON | Mots-clés SEO |
| `publie_le` | TIMESTAMP | Date de publication |
| `soumis_le` | TIMESTAMP | Date de soumission |
| `relu_le` | TIMESTAMP | Date de relecture |
| `approuve_le` | TIMESTAMP | Date d'approbation |
| `raison_rejet` | TEXT | Raison du rejet |
| `historique_workflow` | JSON | Historique workflow |
| `metadonnees` | JSON | Métadonnées |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |
| `supprime_le` | TIMESTAMP | Date de suppression (soft) |

**Relations** :
- `belongsTo` → dossiers (via `dossier_id`)
- `belongsTo` → utilisateurs (via `cree_par`, `assigne_a`, `relecteur_actuel_id`)
- `hasMany` → commentaires
- `hasMany` → workflow_articles
- `hasMany` → messages

**Workflow** :
- `draft` → `submitted` → `in_review` → `approved` → `published`
- Alternative : `draft` → `submitted` → `rejected` → `draft`

---

### 5. **workflow_articles** (article_workflow)
Gestion du workflow de révision des articles.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `article_id` | BIGINT (FK) | Référence à articles |
| `de_utilisateur_id` | BIGINT (FK) | De l'utilisateur |
| `a_utilisateur_id` | BIGINT (FK) | À l'utilisateur |
| `action` | VARCHAR | Action (submitted, reviewed, etc.) |
| `statut` | VARCHAR | Statut (pending, completed, rejected) |
| `commentaire` | TEXT | Commentaire |
| `action_le` | TIMESTAMP | Date de l'action |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → articles (via `article_id`)
- `belongsTo` → utilisateurs (via `de_utilisateur_id`, `a_utilisateur_id`)

---

### 6. **commentaires** (comments)
Commentaires sur les articles.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `article_id` | BIGINT (FK) | Référence à articles |
| `auteur_id` | BIGINT (FK) | Auteur (utilisateurs) |
| `contenu` | TEXT | Contenu du commentaire |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → articles (via `article_id`)
- `belongsTo` → utilisateurs (via `auteur_id`)

---

### 7. **messages**
Système de messagerie interne.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `expediteur_id` | BIGINT (FK) | Expéditeur (utilisateurs) |
| `destinataire_id` | BIGINT (FK) | Destinataire (utilisateurs) |
| `sujet` | VARCHAR | Sujet du message |
| `contenu` | TEXT | Contenu du message |
| `est_lu` | BOOLEAN | Message lu ou non |
| `article_id` | BIGINT (FK) | Article lié (optionnel) |
| `message_parent_id` | BIGINT (FK) | Message parent (réponse) |
| `pieces_jointes` | JSON | Pièces jointes |
| `lu_le` | TIMESTAMP | Date de lecture |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `expediteur_id`, `destinataire_id`)
- `belongsTo` → articles (via `article_id`)
- `belongsTo` → messages (via `message_parent_id`)
- `hasMany` → messages (réponses)

---

### 8. **notifications**
Système de notifications.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `utilisateur_id` | BIGINT (FK) | Référence à utilisateurs |
| `type` | VARCHAR | Type de notification |
| `titre` | VARCHAR | Titre |
| `message` | TEXT | Message |
| `lu` | BOOLEAN (indexed) | Lu ou non |
| `donnees` | JSON | Données supplémentaires |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `utilisateur_id`)

---

### 9. **medias**
Gestion des fichiers médias.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `utilisateur_id` | BIGINT (FK) | Propriétaire (utilisateurs) |
| `disque` | VARCHAR | Disque de stockage |
| `chemin` | VARCHAR | Chemin du fichier |
| `type_mime` | VARCHAR | Type MIME |
| `taille_octets` | BIGINT | Taille en octets |
| `metadonnees` | JSON | Métadonnées |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `utilisateur_id`)

---

### 10. **invitations_equipe** (team_invitations)
Invitations pour rejoindre l'équipe.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `email` | VARCHAR (indexed) | Email de l'invité |
| `role` | VARCHAR | Rôle assigné |
| `jeton` | VARCHAR (unique) | Token d'invitation |
| `invite_par` | BIGINT (FK) | Invité par (utilisateurs) |
| `expire_le` | TIMESTAMP | Date d'expiration |
| `accepte_le` | TIMESTAMP | Date d'acceptation |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `invite_par`)

---

### 11. **planifications_publication** (publication_schedules)
Planification de publication des articles.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `article_id` | BIGINT (FK) | Référence à articles |
| `planifie_pour` | TIMESTAMP (indexed) | Date de publication |
| `canal` | VARCHAR | Canal de publication |
| `statut` | VARCHAR (indexed) | Statut (pending, completed, failed) |
| `raison_echec` | TEXT | Raison de l'échec |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → articles (via `article_id`)

---

### 12. **evenements_analytiques** (analytics_events)
Événements pour les statistiques.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `utilisateur_id` | BIGINT (FK) | Utilisateur (optionnel) |
| `type_evenement` | VARCHAR (indexed) | Type d'événement |
| `proprietes` | JSON | Propriétés |
| `survenu_le` | TIMESTAMP (indexed) | Date de l'événement |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `utilisateur_id`)

---

### 13. **journaux_audit** (audit_logs)
Journaux d'audit pour la traçabilité.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `acteur_id` | BIGINT (FK) | Acteur (utilisateurs) |
| `action` | VARCHAR | Action effectuée |
| `type_entite` | VARCHAR | Type d'entité |
| `entite_id` | BIGINT | ID de l'entité |
| `contexte` | JSON | Contexte |
| `survenu_le` | TIMESTAMP (indexed) | Date de l'événement |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

**Relations** :
- `belongsTo` → utilisateurs (via `acteur_id`)

---

### 14. **jetons_reinitialisation_mdp** (password_reset_tokens)
Tokens de réinitialisation de mot de passe.

| Colonne | Type | Description |
|---------|------|-------------|
| `email` | VARCHAR (PK) | Email |
| `jeton` | VARCHAR | Token |
| `cree_le` | TIMESTAMP | Date de création |

---

### 15. **jetons_acces_personnel** (personal_access_tokens)
Tokens d'accès API (Sanctum).

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `tokenable_type` | VARCHAR | Type de modèle |
| `tokenable_id` | BIGINT | ID du modèle |
| `nom` | VARCHAR | Nom du token |
| `jeton` | VARCHAR (unique) | Token |
| `abilities` | TEXT | Capacités |
| `last_used_at` | TIMESTAMP | Dernière utilisation |
| `expires_at` | TIMESTAMP | Date d'expiration |
| `cree_le` | TIMESTAMP | Date de création |
| `modifie_le` | TIMESTAMP | Date de modification |

---

### 16. **taches_echouees** (failed_jobs)
Jobs échoués dans la queue.

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | BIGINT | Identifiant unique |
| `uuid` | VARCHAR (unique) | UUID |
| `connection` | TEXT | Connexion |
| `queue` | TEXT | Queue |
| `payload` | LONGTEXT | Payload |
| `exception` | LONGTEXT | Exception |
| `failed_at` | TIMESTAMP | Date d'échec |

---

## 📈 Statistiques

- **Total de tables** : 16
- **Tables francisées** : 13
- **Tables système** : 3
- **Colonnes renommées** : ~150+
- **Relations** : ~30+

---

## 🎯 Points Clés à Retenir

### Nomenclature des Timestamps
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`
- `deleted_at` → `supprime_le`
- `*_at` → `*_le` (pour les dates)

### Nomenclature des Foreign Keys
- `user_id` → `utilisateur_id`
- `*_id` reste `*_id` (sauf exceptions)

### Nomenclature Générale
- `name` → `nom`
- `title` → `titre`
- `content` / `body` → `contenu`
- `status` → `statut`
- `is_*` → `est_*`
- `*_by` → `*_par`
- `*_to` → `*_a`

---

## 🔗 Schéma des Relations Principales

```
utilisateurs
  ├── profils (1:1)
  ├── dossiers (1:N)
  ├── articles créés (1:N via cree_par)
  ├── articles assignés (1:N via assigne_a)
  ├── commentaires (1:N via auteur_id)
  ├── messages envoyés (1:N via expediteur_id)
  ├── messages reçus (1:N via destinataire_id)
  └── notifications (1:N)

articles
  ├── dossier (N:1)
  ├── créateur (N:1)
  ├── assigné à (N:1)
  ├── relecteur actuel (N:1)
  ├── commentaires (1:N)
  ├── workflow_articles (1:N)
  ├── messages (1:N)
  └── planifications_publication (1:N)
```

---

## ✅ Migration Prête

La migration est **complète et testable**. Elle inclut :
- ✅ Renommage de toutes les tables
- ✅ Renommage de toutes les colonnes principales
- ✅ Méthode `up()` pour appliquer
- ✅ Méthode `down()` pour annuler
- ✅ Préservation de toutes les relations

---

**Prêt à franciser votre base de données !** 🇫🇷🚀

Consultez `QUICK_START_FRANCISATION.md` pour commencer.


