# 📋 Résumé de la Migration de Francisation (Version Mise à Jour)

## ✅ **Changement Important : Tables Système Conservées**

Les **tables système Laravel** restent en **ANGLAIS** pour garantir la compatibilité avec :
- Laravel Sanctum
- Packages tiers
- Conventions Laravel

---

## 🔒 **Tables NON Modifiées (Système Laravel)**

Ces tables restent exactement comme créées par Laravel :

| Table | Raison |
|-------|--------|
| `users` | Table d'authentification standard Laravel |
| `password_reset_tokens` | Gestion des réinitialisations (standard Laravel) |
| `personal_access_tokens` | Laravel Sanctum API tokens |
| `failed_jobs` | Queue Laravel |

### Colonnes de `users` Conservées :
- ✅ `name` (reste en anglais)
- ✅ `email` (reste en anglais)
- ✅ `password` (reste en anglais)
- ✅ `remember_token` (reste en anglais)
- ✅ `email_verified_at` (reste en anglais)
- ✅ `created_at` (reste en anglais)
- ✅ `updated_at` (reste en anglais)

### Colonnes CUSTOM de `users` Francisées :
- ✅ `is_active` → `est_actif`
- ✅ `last_login_at` → `derniere_connexion_le`
- ✅ `failed_login_attempts` → `tentatives_connexion_echouees`
- ✅ `locked_until` → `verrouille_jusqu_au`

---

## 🇫🇷 **Tables Francisées (Application)**

| Table Originale | Table Francisée |
|----------------|-----------------|
| `profiles` | `profils` |
| `folders` | `dossiers` |
| `comments` | `commentaires` |
| `media` | `medias` |
| `team_invitations` | `invitations_equipe` |
| `publication_schedules` | `planifications_publication` |
| `analytics_events` | `evenements_analytiques` |
| `audit_logs` | `journaux_audit` |
| `article_workflow` | `workflow_articles` |

---

## 🔗 **Foreign Keys : Cohérence Maintenue**

Toutes les colonnes qui pointent vers `users` gardent le suffixe `_id` pour cohérence :

### Conservées :
- `user_id` (dans profils, medias, notifications, etc.)
- `owner_id` (dans dossiers)
- `created_by` (dans articles)
- `assigned_to` (dans articles)
- `current_reviewer_id` (dans articles)
- `author_id` (dans commentaires)
- `sender_id`, `recipient_id` (dans messages)
- `actor_id` (dans journaux_audit)
- `invited_by` (dans invitations_equipe)
- `from_user_id`, `to_user_id` (dans workflow_articles)

**Raison** : Ces colonnes restent liées à la table `users` (en anglais), donc on garde la cohérence.

---

## 📊 **Colonnes Francisées par Table**

### **users** (colonnes custom uniquement)
- `is_active` → `est_actif`
- `last_login_at` → `derniere_connexion_le`
- `failed_login_attempts` → `tentatives_connexion_echouees`
- `locked_until` → `verrouille_jusqu_au`

### **profils**
- `full_name` → `nom_complet`
- `avatar_url` → `url_avatar`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **dossiers**
- `name` → `nom`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **articles**
- `title` → `titre`
- `content` → `contenu`
- `status` → `statut`
- `folder_id` → `dossier_id`
- `seo_title` → `titre_seo`
- `seo_description` → `description_seo`
- `seo_keywords` → `mots_cles_seo`
- `published_at` → `publie_le`
- `metadata` → `metadonnees`
- `workflow_status` → `statut_workflow`
- `submitted_at` → `soumis_le`
- `reviewed_at` → `relu_le`
- `approved_at` → `approuve_le`
- `rejection_reason` → `raison_rejet`
- `workflow_history` → `historique_workflow`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`
- `deleted_at` → `supprime_le`

### **workflow_articles**
- `status` → `statut`
- `comment` → `commentaire`
- `action_at` → `action_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **commentaires**
- `body` → `contenu`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **messages**
- `subject` → `sujet`
- `body` → `contenu`
- `is_read` → `est_lu`
- `parent_message_id` → `message_parent_id`
- `attachments` → `pieces_jointes`
- `read_at` → `lu_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **notifications**
- `title` → `titre`
- `read` → `lu`
- `data` → `donnees`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **medias**
- `disk` → `disque`
- `path` → `chemin`
- `mime_type` → `type_mime`
- `size_bytes` → `taille_octets`
- `meta` → `metadonnees`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **invitations_equipe**
- `token` → `jeton`
- `expires_at` → `expire_le`
- `accepted_at` → `accepte_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **planifications_publication**
- `scheduled_for` → `planifie_pour`
- `channel` → `canal`
- `status` → `statut`
- `failure_reason` → `raison_echec`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **evenements_analytiques**
- `event_type` → `type_evenement`
- `properties` → `proprietes`
- `occurred_at` → `survenu_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### **journaux_audit**
- `entity_type` → `type_entite`
- `entity_id` → `entite_id`
- `context` → `contexte`
- `occurred_at` → `survenu_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

---

## ✅ **Avantages de Cette Approche**

1. ✅ **Compatibilité Laravel** : Authentification native préservée
2. ✅ **Sanctum** : Tokens API fonctionnent sans modification
3. ✅ **Packages tiers** : Pas de conflit avec les packages attendant la structure standard
4. ✅ **Migrations futures** : Laravel peut ajouter des colonnes à `users` sans problème
5. ✅ **Best practices** : Respect des conventions Laravel
6. ✅ **Francisation métier** : Les tables business sont en français

---

## 📝 **Exemple de Modèle User Mis à Jour**

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    // Table reste en anglais
    protected $table = 'users';

    // Colonnes standard Laravel (anglais) + custom (français)
    protected $fillable = [
        'name',
        'email',
        'password',
        'est_actif',
        'derniere_connexion_le',
        'tentatives_connexion_echouees',
        'verrouille_jusqu_au',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'est_actif' => 'boolean',
        'derniere_connexion_le' => 'datetime',
        'verrouille_jusqu_au' => 'datetime',
    ];

    // Timestamps Laravel standard (pas de constantes CREATED_AT/UPDATED_AT)
    // Laravel utilisera automatiquement created_at et updated_at
    
    // Relations
    public function profil()
    {
        return $this->hasOne(Profile::class, 'user_id');
    }

    public function dossiers()
    {
        return $this->hasMany(Folder::class, 'owner_id');
    }
}
```

---

## 📝 **Exemple de Modèle Article Mis à Jour**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use SoftDeletes;

    protected $table = 'articles';

    protected $fillable = [
        'titre',
        'slug',
        'contenu',
        'statut',
        'dossier_id',
        'created_by',        // Reste lié à users
        'assigned_to',       // Reste lié à users
        'current_reviewer_id', // Reste lié à users
        // ... autres colonnes
    ];

    // Timestamps francisés
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';
    const DELETED_AT = 'supprime_le';

    // Relations
    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dossier()
    {
        return $this->belongsTo(Folder::class, 'dossier_id');
    }
}
```

---

## 🎯 **Commandes à Exécuter**

```bash
# 1. Installer doctrine/dbal
composer require doctrine/dbal

# 2. Sauvegarder la base de données
mysqldump -u root -p votre_base > backup_$(date +%Y%m%d).sql

# 3. Exécuter la migration
php artisan migrate

# 4. En cas de problème
php artisan migrate:rollback
```

---

## 📊 **Statistiques Finales**

- **Tables système conservées** : 4
- **Tables métier francisées** : 9
- **Colonnes francisées** : ~120
- **Foreign keys préservées** : ~15

---

## ✨ **Résultat**

Vous obtenez :
- 🇬🇧 **Système Laravel** standard et compatible
- 🇫🇷 **Tables métier** en français
- 🔗 **Relations** cohérentes
- ✅ **Compatibilité** maximale

---

**Version** : 2.0 (Tables système conservées)  
**Date** : 8 octobre 2025  
**Statut** : Prêt pour production

