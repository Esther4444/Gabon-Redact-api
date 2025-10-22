# 🧪 TESTS API AVEC POSTMAN

**Date** : 8 octobre 2025  
**API Base URL** : `http://localhost/api` (ou votre URL Laragon)  
**Version** : 1.0

---

## 📋 TABLE DES MATIÈRES

1. [Configuration Postman](#configuration-postman)
2. [Tests d'Authentification](#1-authentification)
3. [Tests Utilisateurs](#2-utilisateurs)
4. [Tests Dossiers](#3-dossiers)
5. [Tests Articles](#4-articles)
6. [Tests Workflow](#5-workflow)
7. [Tests Commentaires](#6-commentaires)
8. [Tests Messages](#7-messages)
9. [Tests Notifications](#8-notifications)
10. [Tests Médias](#9-médias)
11. [Tests Analytics](#10-analytics)

---

## ⚙️ CONFIGURATION POSTMAN

### Variables d'Environnement

Créez un environnement Postman avec ces variables :

| Variable | Valeur | Description |
|----------|--------|-------------|
| `base_url` | `http://localhost/api` | URL de base de l'API |
| `token` | (vide au départ) | Token Bearer à remplir après login |
| `user_id` | (vide au départ) | ID de l'utilisateur connecté |
| `article_id` | (vide au départ) | ID d'un article de test |
| `folder_id` | (vide au départ) | ID d'un dossier de test |

### Headers Globaux

Ajoutez ces headers à chaque requête (sauf login) :

```
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

---

## 1️⃣ AUTHENTIFICATION

### 🔐 1.1 - Login (Connexion)

**Objectif** : Se connecter et obtenir un token

```http
POST {{base_url}}/login
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "email": "admin@example.com",
  "password": "password123",
  "role": "directeur_publication",
  "device_info": {
    "platform": "Postman",
    "user_agent": "Postman Test"
  }
}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "access_token": "1|xxxxxxxxxxxx",
    "token_type": "Bearer",
    "expires_in": 28800,
    "user": {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "directeur_publication",
      "full_name": "Administrateur Principal",
      "avatar_url": null,
      "last_login_at": "2025-10-08T10:30:00.000000Z"
    }
  }
}
```

**⚠️ Important** : Copiez le `access_token` et mettez-le dans la variable `{{token}}` de votre environnement !

**Tests à vérifier** :
- ✅ Le token est bien retourné
- ✅ Le champ `last_login_at` (pas `derniere_connexion_le`) est présent
- ✅ Le champ `full_name` (pas `nom_complet`) est présent dans la réponse API
- ✅ La base de données contient bien `derniere_connexion_le` et `nom_complet`

---

### 🚪 1.2 - Logout (Déconnexion)

```http
POST {{base_url}}/logout
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "message": "Déconnexion réussie"
}
```

---

### 🔄 1.3 - Refresh Token

```http
POST {{base_url}}/refresh
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "access_token": "2|yyyyyyyyyyyy",
    "token_type": "Bearer",
    "expires_in": 28800
  }
}
```

---

### 👥 1.4 - Utilisateurs Disponibles

```http
GET {{base_url}}/available-users
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Admin User",
      "email": "admin@example.com",
      "role": "directeur_publication",
      "full_name": "Administrateur Principal",
      "avatar_url": null,
      "last_login_at": "2025-10-08T10:30:00.000000Z"
    }
  ]
}
```

---

## 2️⃣ UTILISATEURS

### 👤 2.1 - Mon Profil

```http
GET {{base_url}}/profile
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Admin User",
    "email": "admin@example.com",
    "profile": {
      "id": 1,
      "user_id": 1,
      "nom_complet": "Administrateur Principal",
      "url_avatar": null,
      "role": "directeur_publication",
      "preferences": null
    }
  }
}
```

---

### ✏️ 2.2 - Mettre à Jour Mon Profil

```http
PUT {{base_url}}/profile
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "full_name": "Nouveau Nom Complet",
  "avatar_url": "https://example.com/avatar.jpg",
  "preferences": {
    "theme": "dark",
    "language": "fr"
  }
}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "nom_complet": "Nouveau Nom Complet",
    "url_avatar": "https://example.com/avatar.jpg",
    "role": "directeur_publication",
    "preferences": {
      "theme": "dark",
      "language": "fr"
    }
  }
}
```

**Tests à vérifier** :
- ✅ Les champs `nom_complet` et `url_avatar` sont bien mis à jour en BDD
- ✅ L'API accepte `full_name` et `avatar_url` (anglais) en entrée

---

## 3️⃣ DOSSIERS

### 📁 3.1 - Liste des Dossiers

```http
GET {{base_url}}/folders
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "owner_id": 1,
      "nom": "Articles 2025",
      "created_at": "2025-10-08T10:00:00.000000Z",
      "updated_at": "2025-10-08T10:00:00.000000Z"
    }
  ]
}
```

---

### ➕ 3.2 - Créer un Dossier

```http
POST {{base_url}}/folders
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "name": "Mon Nouveau Dossier"
}
```

**Réponse attendue** (201) :
```json
{
  "success": true,
  "data": {
    "id": 2,
    "owner_id": 1,
    "nom": "Mon Nouveau Dossier",
    "created_at": "2025-10-08T11:00:00.000000Z",
    "updated_at": "2025-10-08T11:00:00.000000Z"
  }
}
```

**Tests à vérifier** :
- ✅ En BDD, la colonne `nom` contient bien "Mon Nouveau Dossier"
- ✅ L'API accepte `name` (anglais) en entrée

**💡 Astuce** : Copiez l'`id` retourné dans `{{folder_id}}`

---

### ✏️ 3.3 - Mettre à Jour un Dossier

```http
PUT {{base_url}}/folders/{{folder_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "name": "Dossier Renommé"
}
```

---

### 🗑️ 3.4 - Supprimer un Dossier

```http
DELETE {{base_url}}/folders/{{folder_id}}
Authorization: Bearer {{token}}
```

**Réponse attendue** (204 No Content)

---

## 4️⃣ ARTICLES

### 📰 4.1 - Liste des Articles

```http
GET {{base_url}}/articles?per_page=10&page=1
Authorization: Bearer {{token}}
```

**Paramètres optionnels** :
- `search` : Recherche dans titre/contenu
- `status` : Filtrer par statut (draft, published)
- `folder_id` : Filtrer par dossier
- `mine` : true pour voir uniquement mes articles

**Exemple avec filtres** :
```http
GET {{base_url}}/articles?search=test&status=draft&mine=true
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "titre": "Mon Premier Article",
      "slug": "mon-premier-article",
      "contenu": "<p>Contenu de l'article...</p>",
      "statut": "draft",
      "statut_workflow": "draft",
      "dossier_id": 1,
      "created_by": 1,
      "assigned_to": null,
      "current_reviewer_id": null,
      "titre_seo": "Mon Premier Article - SEO",
      "description_seo": "Description SEO...",
      "mots_cles_seo": ["test", "article"],
      "publie_le": null,
      "soumis_le": null,
      "relu_le": null,
      "approuve_le": null,
      "raison_rejet": null,
      "historique_workflow": null,
      "metadonnees": null,
      "created_at": "2025-10-08T10:00:00.000000Z",
      "updated_at": "2025-10-08T10:00:00.000000Z",
      "deleted_at": null,
      "creator": {
        "id": 1,
        "name": "Admin User",
        "profile": {
          "nom_complet": "Administrateur Principal",
          "role": "directeur_publication"
        }
      },
      "folder": {
        "id": 1,
        "nom": "Articles 2025"
      }
    }
  ],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 10,
    "total": 1
  }
}
```

**Tests à vérifier** :
- ✅ Les colonnes françaises sont présentes : `titre`, `contenu`, `statut`, `statut_workflow`
- ✅ Les relations fonctionnent correctement
- ✅ Les filtres de recherche fonctionnent sur `titre` et `contenu`

---

### ➕ 4.2 - Créer un Article

```http
POST {{base_url}}/articles
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "title": "Article de Test via Postman",
  "content": "<h1>Contenu</h1><p>Ceci est un test de l'API francisée</p>",
  "folder_id": 1,
  "assigned_to": null,
  "seo_title": "Article Test SEO",
  "seo_description": "Description pour les moteurs de recherche",
  "seo_keywords": ["test", "postman", "api"]
}
```

**Réponse attendue** (201) :
```json
{
  "success": true,
  "data": {
    "id": 2,
    "titre": "Article de Test via Postman",
    "slug": "article-de-test-via-postman",
    "contenu": "<h1>Contenu</h1><p>Ceci est un test de l'API francisée</p>",
    "statut": "draft",
    "statut_workflow": "draft",
    "dossier_id": 1,
    "created_by": 1,
    "titre_seo": "Article Test SEO",
    "description_seo": "Description pour les moteurs de recherche",
    "mots_cles_seo": ["test", "postman", "api"],
    "created_at": "2025-10-08T11:30:00.000000Z",
    "updated_at": "2025-10-08T11:30:00.000000Z"
  }
}
```

**💡 Astuce** : Copiez l'`id` retourné dans `{{article_id}}`

**Tests à vérifier** :
- ✅ Le mapping fonctionne : `title` → `titre`, `content` → `contenu`
- ✅ Le slug est généré automatiquement
- ✅ Les statuts par défaut sont bien "draft"

---

### 👁️ 4.3 - Voir un Article

```http
GET {{base_url}}/articles/{{article_id}}
Authorization: Bearer {{token}}
```

---

### ✏️ 4.4 - Mettre à Jour un Article

```http
PUT {{base_url}}/articles/{{article_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "folder_id": 1,
  "assigned_to": 2,
  "seo_title": "Nouveau Titre SEO",
  "seo_description": "Nouvelle description",
  "seo_keywords": ["nouveau", "test"],
  "status": "published"
}
```

---

### 💾 4.5 - Sauvegarder un Article (méthode complète)

```http
PUT {{base_url}}/articles/{{article_id}}/save
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "title": "Titre Modifié",
  "content": "<h1>Nouveau Contenu</h1><p>Modifié via Postman</p>",
  "folder_id": 1,
  "seo_title": "Titre SEO Modifié",
  "status": "draft"
}
```

**Tests à vérifier** :
- ✅ Le titre et le contenu sont mis à jour
- ✅ Le slug est regénéré si le titre change

---

### 🔍 4.6 - Prévisualisation Publique

```http
GET {{base_url}}/articles/preview/{{slug}}
```

**Exemple** :
```http
GET {{base_url}}/articles/preview/article-de-test-via-postman
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "id": 2,
    "titre": "Article de Test via Postman",
    "contenu": "...",
    "creator": {...},
    "folder": {...}
  },
  "meta": {
    "title": "Article Test SEO",
    "description": "Description pour les moteurs de recherche",
    "keywords": ["test", "postman", "api"]
  }
}
```

**Tests à vérifier** :
- ✅ Les métadonnées SEO sont bien retournées depuis `titre_seo`, `description_seo`, `mots_cles_seo`

---

### 🗑️ 4.7 - Supprimer un Article

```http
DELETE {{base_url}}/articles/{{article_id}}
Authorization: Bearer {{token}}
```

**Réponse attendue** (204 No Content)

**Tests à vérifier** :
- ✅ Soft delete : `deleted_at` est rempli en BDD
- ✅ L'article n'apparaît plus dans la liste

---

## 5️⃣ WORKFLOW

### 📤 5.1 - Soumettre un Article pour Révision

```http
POST {{base_url}}/workflow/articles/{{article_id}}/submit
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "reviewer_id": 2,
  "comment": "Merci de réviser cet article"
}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "message": "Article soumis pour révision",
  "data": {
    "id": 2,
    "statut_workflow": "submitted",
    "current_reviewer_id": 2,
    "soumis_le": "2025-10-08T12:00:00.000000Z",
    "currentReviewer": {
      "id": 2,
      "name": "Réviseur",
      "profile": {...}
    },
    "workflowSteps": [
      {
        "id": 1,
        "article_id": 2,
        "from_user_id": 1,
        "to_user_id": 2,
        "action": "submitted",
        "statut": "pending",
        "commentaire": "Merci de réviser cet article",
        "action_le": null,
        "created_at": "2025-10-08T12:00:00.000000Z"
      }
    ]
  }
}
```

**Tests à vérifier** :
- ✅ `statut_workflow` passe à "submitted"
- ✅ `soumis_le` est rempli
- ✅ Une entrée est créée dans `workflow_articles` avec les colonnes `statut` et `commentaire`

---

### ✅ 5.2 - Réviser un Article

```http
POST {{base_url}}/workflow/articles/{{article_id}}/review
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "comment": "Article révisé, prêt pour approbation"
}
```

**Tests à vérifier** :
- ✅ `statut_workflow` passe à "in_review"
- ✅ `relu_le` est rempli

---

### 👍 5.3 - Approuver un Article

```http
POST {{base_url}}/workflow/articles/{{article_id}}/approve
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "comment": "Article approuvé pour publication"
}
```

**Tests à vérifier** :
- ✅ `statut_workflow` passe à "approved"
- ✅ `approuve_le` est rempli

---

### 👎 5.4 - Rejeter un Article

```http
POST {{base_url}}/workflow/articles/{{article_id}}/reject
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "reason": "Le contenu n'est pas conforme aux standards",
  "comment": "Merci de revoir la structure"
}
```

**Tests à vérifier** :
- ✅ `statut_workflow` passe à "rejected"
- ✅ `raison_rejet` est rempli

---

### 🚀 5.5 - Publier un Article

```http
POST {{base_url}}/workflow/articles/{{article_id}}/publish
Authorization: Bearer {{token}}
```

**Tests à vérifier** :
- ✅ `statut_workflow` passe à "published"
- ✅ `statut` passe à "published"
- ✅ `publie_le` est rempli

---

### 📋 5.6 - Articles en Attente

```http
GET {{base_url}}/workflow/pending
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 2,
      "titre": "Article en Attente",
      "statut_workflow": "submitted",
      "soumis_le": "2025-10-08T12:00:00.000000Z",
      "creator": {...},
      "folder": {...},
      "workflowSteps": [...]
    }
  ]
}
```

---

### 📊 5.7 - Statistiques Workflow

```http
GET {{base_url}}/workflow/stats
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "my_articles": {
      "draft": 5,
      "submitted": 2,
      "in_review": 1,
      "approved": 3,
      "rejected": 0,
      "published": 10
    },
    "pending_review": 2
  }
}
```

**Tests à vérifier** :
- ✅ Les comptages utilisent bien `statut_workflow`

---

### 📜 5.8 - Historique Workflow d'un Article

```http
GET {{base_url}}/workflow/articles/{{article_id}}/history
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "article_id": 2,
      "from_user_id": 1,
      "to_user_id": 2,
      "action": "submitted",
      "statut": "completed",
      "commentaire": "Merci de réviser",
      "action_le": "2025-10-08T12:00:00.000000Z",
      "fromUser": {...},
      "toUser": {...}
    }
  ]
}
```

---

## 6️⃣ COMMENTAIRES

### 💬 6.1 - Liste des Commentaires d'un Article

```http
GET {{base_url}}/articles/{{article_id}}/comments
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "article_id": 2,
      "author_id": 1,
      "contenu": "Excellent article !",
      "created_at": "2025-10-08T13:00:00.000000Z",
      "author": {
        "id": 1,
        "name": "Admin User",
        "profile": {...}
      }
    }
  ]
}
```

---

### ➕ 6.2 - Ajouter un Commentaire

```http
POST {{base_url}}/articles/{{article_id}}/comments
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "body": "Voici mon commentaire sur cet article"
}
```

**Réponse attendue** (201) :
```json
{
  "success": true,
  "data": {
    "id": 2,
    "article_id": 2,
    "author_id": 1,
    "contenu": "Voici mon commentaire sur cet article",
    "created_at": "2025-10-08T13:05:00.000000Z"
  }
}
```

**Tests à vérifier** :
- ✅ Le champ `body` (anglais) en entrée est mappé vers `contenu` (français) en BDD

---

### ✏️ 6.3 - Modifier un Commentaire

```http
PUT {{base_url}}/comments/{{comment_id}}
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "body": "Commentaire modifié"
}
```

---

### 🗑️ 6.4 - Supprimer un Commentaire

```http
DELETE {{base_url}}/comments/{{comment_id}}
Authorization: Bearer {{token}}
```

---

## 7️⃣ MESSAGES

### 📧 7.1 - Liste de Mes Messages

```http
GET {{base_url}}/messages
Authorization: Bearer {{token}}
```

**Paramètres optionnels** :
- `unread_only=true` : Uniquement les messages non lus
- `article_id=2` : Messages liés à un article

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "sender_id": 2,
      "recipient_id": 1,
      "article_id": 2,
      "message_parent_id": null,
      "sujet": "Question sur l'article",
      "contenu": "Bonjour, j'ai une question...",
      "est_lu": false,
      "pieces_jointes": null,
      "lu_le": null,
      "created_at": "2025-10-08T14:00:00.000000Z",
      "sender": {
        "id": 2,
        "name": "Réviseur",
        "profile": {...}
      },
      "article": {
        "id": 2,
        "titre": "Article de Test"
      }
    }
  ],
  "pagination": {...}
}
```

**Tests à vérifier** :
- ✅ Les colonnes `sujet`, `contenu`, `est_lu`, `message_parent_id`, `pieces_jointes`, `lu_le` sont présentes

---

### ➕ 7.2 - Envoyer un Message

```http
POST {{base_url}}/messages
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "recipient_id": 2,
  "subject": "Demande de révision",
  "body": "Bonjour, pouvez-vous réviser cet article ?",
  "article_id": 2,
  "parent_message_id": null
}
```

**Réponse attendue** (201) :
```json
{
  "success": true,
  "message": "Message envoyé",
  "data": {
    "id": 2,
    "sender_id": 1,
    "recipient_id": 2,
    "sujet": "Demande de révision",
    "contenu": "Bonjour, pouvez-vous réviser cet article ?",
    "article_id": 2,
    "message_parent_id": null,
    "est_lu": false,
    "created_at": "2025-10-08T14:10:00.000000Z"
  }
}
```

**Tests à vérifier** :
- ✅ Mapping : `subject` → `sujet`, `body` → `contenu`, `parent_message_id` → `message_parent_id`

---

### 👁️ 7.3 - Voir un Message (marque comme lu automatiquement)

```http
GET {{base_url}}/messages/{{message_id}}
Authorization: Bearer {{token}}
```

**Tests à vérifier** :
- ✅ Si le message est pour moi et non lu, `est_lu` passe à `true` et `lu_le` est rempli

---

### 💬 7.4 - Répondre à un Message

```http
POST {{base_url}}/messages/{{message_id}}/reply
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "body": "Bien sûr, je vais réviser cet article aujourd'hui"
}
```

**Tests à vérifier** :
- ✅ Le nouveau message a `message_parent_id` rempli
- ✅ Le sujet commence par "Re: "

---

### ✅ 7.5 - Marquer comme Lu

```http
POST {{base_url}}/messages/{{message_id}}/mark-read
Authorization: Bearer {{token}}
```

---

### ❌ 7.6 - Marquer comme Non Lu

```http
POST {{base_url}}/messages/{{message_id}}/mark-unread
Authorization: Bearer {{token}}
```

---

### 🔢 7.7 - Compteur de Messages Non Lus

```http
GET {{base_url}}/messages/unread
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "unread_count": 5
  }
}
```

---

### 💬 7.8 - Conversations Groupées

```http
GET {{base_url}}/messages/conversations
Authorization: Bearer {{token}}
```

---

## 8️⃣ NOTIFICATIONS

### 🔔 8.1 - Liste de Mes Notifications

```http
GET {{base_url}}/notifications
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "user_id": 1,
      "type": "workflow",
      "titre": "Article approuvé",
      "message": "Votre article a été approuvé",
      "lu": false,
      "donnees": {
        "article_id": 2,
        "article_title": "Article de Test"
      },
      "created_at": "2025-10-08T15:00:00.000000Z"
    }
  ]
}
```

**Tests à vérifier** :
- ✅ Les colonnes `titre`, `lu`, `donnees` sont présentes

---

### ➕ 8.2 - Créer une Notification

```http
POST {{base_url}}/notifications
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "user_id": 1,
  "title": "Test de Notification",
  "message": "Ceci est une notification de test",
  "type": "info",
  "data": {
    "test": true
  }
}
```

**Tests à vérifier** :
- ✅ Mapping : `title` → `titre`, `data` → `donnees`

---

### ✅ 8.3 - Marquer comme Lue

```http
PUT {{base_url}}/notifications/{{notification_id}}/read
Authorization: Bearer {{token}}
```

**Tests à vérifier** :
- ✅ Le champ `lu` passe à `true`

---

## 9️⃣ MÉDIAS

### 📤 9.1 - Upload de Fichier

```http
POST {{base_url}}/media/upload
Authorization: Bearer {{token}}
Content-Type: multipart/form-data
```

**Body (Form Data)** :
- `file` : Sélectionnez un fichier (image, PDF, etc.)

**Réponse attendue** (201) :
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "disque": "public",
    "chemin": "uploads/xyz123.jpg",
    "type_mime": "image/jpeg",
    "taille_octets": 245678,
    "metadonnees": null,
    "created_at": "2025-10-08T16:00:00.000000Z"
  }
}
```

**Tests à vérifier** :
- ✅ Les colonnes `disque`, `chemin`, `type_mime`, `taille_octets`, `metadonnees` sont présentes
- ✅ Le fichier est bien uploadé dans `storage/app/public/uploads/`

---

### 📁 9.2 - Liste des Médias

```http
GET {{base_url}}/media
Authorization: Bearer {{token}}
```

---

### 🗑️ 9.3 - Supprimer un Média

```http
DELETE {{base_url}}/media/{{media_id}}
Authorization: Bearer {{token}}
```

**Tests à vérifier** :
- ✅ Le fichier physique est supprimé du disque

---

## 🔟 ANALYTICS

### 📊 10.1 - Tableau de Bord Analytics

```http
GET {{base_url}}/analytics/dashboard
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": {
    "articles_by_status": {
      "draft": 10,
      "published": 25,
      "archived": 5
    }
  }
}
```

**Tests à vérifier** :
- ✅ Le groupement utilise bien `statut` (colonne francisée)

---

### ➕ 10.2 - Enregistrer un Événement

```http
POST {{base_url}}/analytics
Authorization: Bearer {{token}}
Content-Type: application/json
```

**Body (JSON)** :
```json
{
  "event_type": "article_viewed",
  "properties": {
    "article_id": 2,
    "source": "postman_test"
  },
  "occurred_at": "2025-10-08T16:30:00Z"
}
```

**Réponse attendue** (201) :
```json
{
  "success": true,
  "data": {
    "id": 1,
    "user_id": 1,
    "type_evenement": "article_viewed",
    "proprietes": {
      "article_id": 2,
      "source": "postman_test"
    },
    "survenu_le": "2025-10-08T16:30:00.000000Z",
    "created_at": "2025-10-08T16:30:00.000000Z"
  }
}
```

**Tests à vérifier** :
- ✅ Mapping : `event_type` → `type_evenement`, `properties` → `proprietes`, `occurred_at` → `survenu_le`

---

## 1️⃣1️⃣ AUDIT LOGS

### 📜 11.1 - Liste des Logs d'Audit

```http
GET {{base_url}}/audit-logs
Authorization: Bearer {{token}}
```

**Réponse attendue** (200) :
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "actor_id": 1,
      "action": "login_success",
      "type_entite": "user",
      "entite_id": 1,
      "contexte": {
        "ip_address": "127.0.0.1",
        "user_agent": "Postman"
      },
      "survenu_le": "2025-10-08T10:30:00.000000Z",
      "created_at": "2025-10-08T10:30:00.000000Z"
    }
  ]
}
```

**Tests à vérifier** :
- ✅ Les colonnes `type_entite`, `entite_id`, `contexte`, `survenu_le` sont présentes

---

## ✅ CHECKLIST DE VALIDATION

### Phase 1 : Authentification
- [ ] Login réussit et retourne un token
- [ ] Le token fonctionne pour les requêtes suivantes
- [ ] Les champs `derniere_connexion_le` et `tentatives_connexion_echouees` sont mis à jour en BDD
- [ ] Logout fonctionne
- [ ] Les audit logs sont créés avec les colonnes francisées

### Phase 2 : Utilisateurs & Profils
- [ ] Le profil est créé automatiquement avec `nom_complet` en BDD
- [ ] La mise à jour du profil fonctionne (mapping `full_name` → `nom_complet`)

### Phase 3 : Dossiers
- [ ] Création de dossier : `name` (API) → `nom` (BDD)
- [ ] Liste des dossiers affiche `nom` (français)
- [ ] Mise à jour et suppression fonctionnent

### Phase 4 : Articles
- [ ] Création : tous les champs sont mappés correctement
- [ ] La recherche fonctionne sur `titre` et `contenu`
- [ ] Les filtres par `statut` et `dossier_id` fonctionnent
- [ ] Le slug est généré automatiquement
- [ ] Les relations (creator, folder) fonctionnent
- [ ] La prévisualisation publique affiche les métadonnées SEO

### Phase 5 : Workflow
- [ ] Soumission : `statut_workflow` → "submitted", `soumis_le` rempli
- [ ] Révision : `relu_le` rempli
- [ ] Approbation : `approuve_le` rempli
- [ ] Rejet : `raison_rejet` rempli
- [ ] Publication : `publie_le` rempli
- [ ] Les statistiques comptent bien selon `statut_workflow`
- [ ] L'historique utilise `statut`, `commentaire`, `action_le`

### Phase 6 : Commentaires
- [ ] Création : `body` → `contenu`
- [ ] Les commentaires sont liés correctement aux articles

### Phase 7 : Messages
- [ ] Création : mapping `subject` → `sujet`, `body` → `contenu`
- [ ] Marquage comme lu : `est_lu` → true, `lu_le` rempli
- [ ] Le compteur de messages non lus fonctionne
- [ ] Les réponses ont `message_parent_id` correct

### Phase 8 : Notifications
- [ ] Création : `title` → `titre`, `data` → `donnees`
- [ ] Marquage comme lu : `lu` → true

### Phase 9 : Médias
- [ ] Upload : colonnes `disque`, `chemin`, `type_mime`, `taille_octets` remplies
- [ ] Le fichier physique est bien uploadé
- [ ] La suppression efface le fichier physique

### Phase 10 : Analytics
- [ ] Les événements sont créés avec `type_evenement`, `proprietes`, `survenu_le`
- [ ] Le dashboard groupe par `statut`

### Phase 11 : Audit Logs
- [ ] Les logs affichent `type_entite`, `entite_id`, `contexte`, `survenu_le`

---

## 🎯 TESTS DE BOUT EN BOUT

### Scénario Complet : Cycle de Vie d'un Article

1. **Login** → Obtenir un token
2. **Créer un dossier** → Récupérer `folder_id`
3. **Créer un article** → Récupérer `article_id`
4. **Ajouter un commentaire** sur l'article
5. **Soumettre l'article** pour révision
6. **Réviser l'article** (si vous avez un compte réviseur)
7. **Approuver l'article**
8. **Publier l'article**
9. **Vérifier les statistiques** workflow
10. **Consulter l'historique** du workflow
11. **Envoyer un message** concernant l'article
12. **Créer une notification** de publication
13. **Consulter les logs d'audit**

---

## 💡 ASTUCES POSTMAN

### Variables d'Environnement Automatiques

Dans l'onglet "Tests" de vos requêtes, ajoutez ces scripts :

**Pour le Login** :
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("token", response.data.access_token);
    pm.environment.set("user_id", response.data.user.id);
}
```

**Pour la Création d'Article** :
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    pm.environment.set("article_id", response.data.id);
}
```

**Pour la Création de Dossier** :
```javascript
if (pm.response.code === 201) {
    const response = pm.response.json();
    pm.environment.set("folder_id", response.data.id);
}
```

---

## 🐛 DEBUGGING

### Vérifier en Base de Données

Après chaque requête, vous pouvez vérifier en BDD :

```sql
-- Voir les articles avec colonnes francisées
SELECT id, titre, contenu, statut, statut_workflow, dossier_id, 
       soumis_le, relu_le, approuve_le, publie_le
FROM articles;

-- Voir les profils
SELECT id, user_id, nom_complet, url_avatar, role
FROM profils;

-- Voir les messages
SELECT id, sujet, contenu, est_lu, lu_le, message_parent_id
FROM messages;

-- Voir le workflow
SELECT id, article_id, action, statut, commentaire, action_le
FROM workflow_articles;
```

---

## 📝 NOTES FINALES

- **API en anglais** : Les requêtes utilisent des noms anglais (`title`, `content`, etc.)
- **BDD en français** : Les colonnes en base sont francisées (`titre`, `contenu`, etc.)
- **Mapping transparent** : Les contrôleurs font la conversion automatiquement
- **Timestamps standards** : `created_at`, `updated_at`, `deleted_at` (anglais)

**Bon test ! 🚀**

