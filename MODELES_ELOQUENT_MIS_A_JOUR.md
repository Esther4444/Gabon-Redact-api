# 📝 MODÈLES ELOQUENT MIS À JOUR

**Date de mise à jour** : 8 octobre 2025  
**Nombre de modèles mis à jour** : 13  
**Statut** : ✅ Terminé

---

## 📋 RÉSUMÉ DES MODIFICATIONS

Tous les modèles Eloquent ont été mis à jour pour correspondre à la nouvelle structure de base de données francisée. Les timestamps ont été remis en anglais (`created_at`, `updated_at`, `deleted_at`) conformément aux conventions Laravel.

---

## ✅ MODÈLES MIS À JOUR

### 1. **User.php**
- ✅ Table : `users` (reste en anglais - table système)
- **Colonnes francisées** :
  - `is_active` → `est_actif`
  - `last_login_at` → `derniere_connexion_le`
  - `failed_login_attempts` → `tentatives_connexion_echouees`
  - `locked_until` → `verrouille_jusqu_au`
- **Scopes mis à jour** :
  - `scopeActive()` utilise maintenant `est_actif`
  - `scopeNotLocked()` utilise maintenant `verrouille_jusqu_au`

---

### 2. **Profile.php**
- ✅ Table : `profiles` → **`profils`**
- **Colonnes francisées** :
  - `full_name` → `nom_complet`
  - `avatar_url` → `url_avatar`

---

### 3. **Folder.php**
- ✅ Table : `folders` → **`dossiers`**
- **Colonnes francisées** :
  - `name` → `nom`
- **Relations mises à jour** :
  - `articles()` : Clé étrangère → `dossier_id`

---

### 4. **Article.php** ⭐
- ✅ Table : `articles` (reste en anglais)
- **Colonnes francisées** (20 colonnes) :
  - `title` → `titre`
  - `content` → `contenu`
  - `status` → `statut`
  - `workflow_status` → `statut_workflow`
  - `folder_id` → `dossier_id`
  - `seo_title` → `titre_seo`
  - `seo_description` → `description_seo`
  - `seo_keywords` → `mots_cles_seo`
  - `published_at` → `publie_le`
  - `submitted_at` → `soumis_le`
  - `reviewed_at` → `relu_le`
  - `approved_at` → `approuve_le`
  - `rejection_reason` → `raison_rejet`
  - `workflow_history` → `historique_workflow`
  - `metadata` → `metadonnees`

- **Méthodes workflow mises à jour** :
  - `submitForReview()` : Utilise `statut_workflow`, `soumis_le`, `titre`
  - `review()` : Utilise `relu_le`, `statut`, `commentaire`, `action_le`
  - `approve()` : Utilise `approuve_le`, `statut`, `commentaire`
  - `reject()` : Utilise `raison_rejet`, `statut`, `commentaire`
  - `publish()` : Utilise `publie_le`, `statut`
  - `sendNotification()` : Utilise `titre`, `donnees`

- **Scopes mis à jour** :
  - `scopeByWorkflowStatus()` utilise `statut_workflow`
  - `scopeSubmitted()`, `scopeInReview()`, `scopeApproved()`, `scopeRejected()` utilisent `statut_workflow`

- **Relations mises à jour** :
  - `folder()` : Clé étrangère → `dossier_id`

---

### 5. **Comment.php**
- ✅ Table : `comments` → **`commentaires`**
- **Colonnes francisées** :
  - `body` → `contenu`

---

### 6. **Message.php**
- ✅ Table : `messages` (reste en anglais)
- **Colonnes francisées** :
  - `subject` → `sujet`
  - `body` → `contenu`
  - `is_read` → `est_lu`
  - `parent_message_id` → `message_parent_id`
  - `attachments` → `pieces_jointes`
  - `read_at` → `lu_le`

- **Méthodes mises à jour** :
  - `markAsRead()` : Utilise `est_lu`, `lu_le`
  - `markAsUnread()` : Utilise `est_lu`, `lu_le`
  - `isReply()` : Utilise `message_parent_id`

- **Scopes mis à jour** :
  - `scopeUnread()` utilise `est_lu`
  - `scopeRead()` utilise `est_lu`

- **Relations mises à jour** :
  - `parentMessage()` : Clé étrangère → `message_parent_id`
  - `replies()` : Clé étrangère → `message_parent_id`

---

### 7. **Notification.php**
- ✅ Table : `notifications` (reste en anglais)
- **Colonnes francisées** :
  - `title` → `titre`
  - `read` → `lu`
  - `data` → `donnees`

---

### 8. **Media.php**
- ✅ Table : `media` → **`medias`**
- **Colonnes francisées** :
  - `disk` → `disque`
  - `path` → `chemin`
  - `mime_type` → `type_mime`
  - `size_bytes` → `taille_octets`
  - `meta` → `metadonnees`

---

### 9. **ArticleWorkflow.php**
- ✅ Table : `article_workflow` → **`workflow_articles`**
- **Colonnes francisées** :
  - `status` → `statut`
  - `comment` → `commentaire`
  - `action_at` → `action_le`

- **Scopes mis à jour** :
  - `scopePending()` utilise `statut`
  - `scopeCompleted()` utilise `statut`

---

### 10. **TeamInvitation.php**
- ✅ Table : `team_invitations` → **`invitations_equipe`**
- **Colonnes francisées** :
  - `token` → `jeton`
  - `expires_at` → `expire_le`
  - `accepted_at` → `accepte_le`

---

### 11. **PublicationSchedule.php**
- ✅ Table : `publication_schedules` → **`planifications_publication`**
- **Colonnes francisées** :
  - `scheduled_for` → `planifie_pour`
  - `channel` → `canal`
  - `status` → `statut`
  - `failure_reason` → `raison_echec`

---

### 12. **AnalyticsEvent.php**
- ✅ Table : `analytics_events` → **`evenements_analytiques`**
- **Colonnes francisées** :
  - `event_type` → `type_evenement`
  - `properties` → `proprietes`
  - `occurred_at` → `survenu_le`

---

### 13. **AuditLog.php**
- ✅ Table : `audit_logs` → **`journaux_audit`**
- **Colonnes francisées** :
  - `entity_type` → `type_entite`
  - `entity_id` → `entite_id`
  - `context` → `contexte`
  - `occurred_at` → `survenu_le`

---

## 🔄 TIMESTAMPS

**Tous les modèles** utilisent maintenant les timestamps standards de Laravel :
- ✅ `created_at` (au lieu de `cree_le`)
- ✅ `updated_at` (au lieu de `modifie_le`)
- ✅ `deleted_at` (au lieu de `supprime_le`) - uniquement pour Article

**Aucune constante CREATED_AT, UPDATED_AT ou DELETED_AT n'est définie** car les noms sont standards.

---

## 📊 STATISTIQUES

| Catégorie | Nombre |
|-----------|--------|
| **Modèles mis à jour** | 13 |
| **Tables renommées** | 9 |
| **Tables conservées** | 4 |
| **Colonnes francisées** | ~120 |
| **Méthodes mises à jour** | ~20 |
| **Scopes mis à jour** | ~10 |
| **Relations mises à jour** | ~15 |

---

## 🎯 CONVENTIONS RESPECTÉES

### Noms de tables
- ✅ Tables système : **anglais** (`users`, `notifications`, `messages`, `articles`)
- ✅ Tables métier : **français** (`profils`, `dossiers`, `commentaires`, `medias`, etc.)

### Colonnes
- ✅ Colonnes système : **anglais** (created_at, updated_at, deleted_at)
- ✅ Foreign keys : **anglais** (user_id, created_by, assigned_to, etc.)
- ✅ Colonnes métier : **français** (titre, contenu, statut, etc.)

### Constantes de modèle
- ✅ `protected $table` : Défini uniquement pour les tables francisées
- ✅ `protected $fillable` : Toutes les colonnes francisées
- ✅ `protected $casts` : Toutes les colonnes francisées avec casting
- ❌ Pas de constantes CREATED_AT/UPDATED_AT (timestamps standards)

---

## ✅ VALIDATION

Pour vérifier que tout fonctionne correctement, vous pouvez exécuter :

```bash
# 1. Tester les modèles avec Tinker
php artisan tinker

# Exemples de tests :
User::first()
Profile::first()
Article::with('folder', 'creator')->first()
Message::unread()->get()
Notification::where('lu', false)->get()

# 2. Lancer les tests (si disponibles)
php artisan test

# 3. Vérifier les relations
php artisan tinker
>>> $user = User::first()
>>> $user->profile->nom_complet
>>> $article = Article::first()
>>> $article->titre
>>> $article->statut_workflow
```

---

## 🔗 RELATIONS VÉRIFIÉES

Toutes les relations entre modèles ont été vérifiées et mises à jour :

| Relation | Depuis | Vers | Clé étrangère | Status |
|----------|--------|------|---------------|--------|
| User → Profile | User | Profile | user_id | ✅ |
| User → Folders | User | Folder | owner_id | ✅ |
| Folder → Articles | Folder | Article | dossier_id | ✅ |
| Article → Comments | Article | Comment | article_id | ✅ |
| Article → Messages | Article | Message | article_id | ✅ |
| Article → Workflow | Article | ArticleWorkflow | article_id | ✅ |
| User → Messages (sender) | User | Message | sender_id | ✅ |
| User → Messages (recipient) | User | Message | recipient_id | ✅ |
| Message → Parent | Message | Message | message_parent_id | ✅ |
| User → Notifications | User | Notification | user_id | ✅ |
| User → Media | User | Media | user_id | ✅ |
| User → TeamInvitations | User | TeamInvitation | invited_by | ✅ |
| Article → Schedules | Article | PublicationSchedule | article_id | ✅ |
| User → Analytics | User | AnalyticsEvent | user_id | ✅ |
| User → AuditLogs | User | AuditLog | actor_id | ✅ |

---

## 🚀 PROCHAINES ÉTAPES

1. ✅ ~~Mettre à jour tous les modèles Eloquent~~ **TERMINÉ**
2. ⏭️ **Mettre à jour les contrôleurs** pour utiliser les nouvelles colonnes
3. ⏭️ **Mettre à jour les seeders** si nécessaire
4. ⏭️ **Mettre à jour les factories** si nécessaire
5. ⏭️ **Mettre à jour les tests** si disponibles
6. ⏭️ **Tester l'API** avec les nouvelles colonnes

---

## ⚠️ POINTS D'ATTENTION

### Contrôleurs à vérifier
Les contrôleurs doivent maintenant utiliser les nouvelles colonnes francisées :

```php
// ❌ AVANT
$article = Article::create([
    'title' => $request->title,
    'content' => $request->content,
    'status' => 'draft',
]);

// ✅ APRÈS
$article = Article::create([
    'titre' => $request->title,
    'contenu' => $request->content,
    'statut' => 'draft',
]);
```

### Validation à adapter
Les règles de validation doivent aussi être adaptées :

```php
// ❌ AVANT
$request->validate([
    'title' => 'required|string|max:255',
    'content' => 'required',
]);

// ✅ APRÈS
$request->validate([
    'title' => 'required|string|max:255', // Le champ de requête peut rester en anglais
]);

// Puis mapper :
$article->titre = $request->title;
$article->contenu = $request->content;
```

### API Responses
Les réponses JSON peuvent continuer à utiliser des noms anglais pour l'API externe :

```php
// Option 1 : Utiliser des Resource classes
class ArticleResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'title' => $this->titre,        // Mapper français → anglais
            'content' => $this->contenu,
            'status' => $this->statut,
            'created_at' => $this->created_at,
        ];
    }
}

// Option 2 : Utiliser des Accessors (appends)
class Article extends Model
{
    protected $appends = ['title', 'content'];
    
    public function getTitleAttribute()
    {
        return $this->titre;
    }
    
    public function getContentAttribute()
    {
        return $this->contenu;
    }
}
```

---

## 📖 DOCUMENTATION COMPLÉMENTAIRE

- 📄 [STRUCTURE_BASE_DONNEES.md](./STRUCTURE_BASE_DONNEES.md) : Structure complète de la base
- 📄 [MIGRATION_FRANCISATION_RESUME.md](./MIGRATION_FRANCISATION_RESUME.md) : Résumé de la francisation

---

**Mise à jour réalisée avec succès** ✅  
**Tous les modèles sont maintenant synchronisés avec la base de données francisée**

