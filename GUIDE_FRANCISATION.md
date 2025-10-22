# 🇫🇷 Guide de Francisation de la Base de Données

## 📋 Vue d'ensemble

Ce guide explique comment appliquer la francisation complète de votre base de données Laravel pour le projet **Dossier Redac Pro**.

---

## ⚠️ IMPORTANT : Prérequis

Avant d'exécuter la migration, vous **DEVEZ** installer le package `doctrine/dbal` qui est requis pour renommer les colonnes dans Laravel :

```bash
cd RedacGabonProApi
composer require doctrine/dbal
```

---

## 🚀 Étapes d'Installation

### 1. Installer les dépendances
```bash
cd RedacGabonProApi
composer require doctrine/dbal
```

### 2. Vérifier l'état des migrations
```bash
php artisan migrate:status
```

### 3. Exécuter la migration de francisation
```bash
php artisan migrate
```

### 4. En cas de problème, annuler la migration
```bash
php artisan migrate:rollback
```

---

## 📊 Tables Renommées

| Table Originale | Table Francisée |
|----------------|-----------------|
| `users` | `utilisateurs` |
| `profiles` | `profils` |
| `folders` | `dossiers` |
| `articles` | `articles` *(déjà français)* |
| `comments` | `commentaires` |
| `media` | `medias` |
| `notifications` | `notifications` *(déjà français)* |
| `messages` | `messages` *(déjà français)* |
| `team_invitations` | `invitations_equipe` |
| `publication_schedules` | `planifications_publication` |
| `analytics_events` | `evenements_analytiques` |
| `audit_logs` | `journaux_audit` |
| `article_workflow` | `workflow_articles` |
| `password_reset_tokens` | `jetons_reinitialisation_mdp` |
| `personal_access_tokens` | `jetons_acces_personnel` |
| `failed_jobs` | `taches_echouees` |

---

## 📝 Exemples de Colonnes Francisées

### Table `utilisateurs`
- `name` → `nom`
- `email_verified_at` → `email_verifie_le`
- `password` → `mot_de_passe`
- `is_active` → `est_actif`
- `last_login_at` → `derniere_connexion_le`
- `created_at` → `cree_le`
- `updated_at` → `modifie_le`

### Table `articles`
- `title` → `titre`
- `content` → `contenu`
- `status` → `statut`
- `folder_id` → `dossier_id`
- `created_by` → `cree_par`
- `assigned_to` → `assigne_a`
- `published_at` → `publie_le`
- `workflow_status` → `statut_workflow`
- `submitted_at` → `soumis_le`
- `approved_at` → `approuve_le`

### Table `messages`
- `sender_id` → `expediteur_id`
- `recipient_id` → `destinataire_id`
- `subject` → `sujet`
- `body` → `contenu`
- `is_read` → `est_lu`
- `read_at` → `lu_le`

---

## 🔧 Mise à Jour des Modèles Laravel

Après l'exécution de la migration, vous devrez mettre à jour vos modèles Eloquent pour refléter les nouveaux noms de tables et de colonnes.

### Exemple : Modèle User

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'utilisateurs';
    
    protected $fillable = [
        'nom',
        'email',
        'mot_de_passe',
        'est_actif',
    ];
    
    protected $hidden = [
        'mot_de_passe',
        'jeton_souvenir',
    ];
    
    protected $casts = [
        'email_verifie_le' => 'datetime',
        'derniere_connexion_le' => 'datetime',
        'verrouille_jusqu_au' => 'datetime',
        'est_actif' => 'boolean',
    ];
    
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';
}
```

### Exemple : Modèle Article

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
        'cree_par',
        'assigne_a',
        'titre_seo',
        'description_seo',
        'mots_cles_seo',
        'publie_le',
        'metadonnees',
        'statut_workflow',
        'relecteur_actuel_id',
    ];
    
    protected $casts = [
        'mots_cles_seo' => 'array',
        'metadonnees' => 'array',
        'historique_workflow' => 'array',
        'publie_le' => 'datetime',
        'soumis_le' => 'datetime',
        'relu_le' => 'datetime',
        'approuve_le' => 'datetime',
    ];
    
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';
    const DELETED_AT = 'supprime_le';
}
```

---

## 🔄 Mise à Jour des Relations

N'oubliez pas de mettre à jour les noms de colonnes dans les relations Eloquent :

```php
// Avant
public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}

// Après
public function utilisateur()
{
    return $this->belongsTo(User::class, 'utilisateur_id');
}
```

---

## 🗄️ Mise à Jour des Requêtes

Toutes vos requêtes devront être mises à jour pour utiliser les nouveaux noms de colonnes :

```php
// Avant
$articles = Article::where('status', 'published')
    ->orderBy('created_at', 'desc')
    ->get();

// Après
$articles = Article::where('statut', 'published')
    ->orderBy('cree_le', 'desc')
    ->get();
```

---

## 🧪 Tests

Après avoir appliqué la migration, testez :

1. ✅ Connexion utilisateur
2. ✅ Création d'articles
3. ✅ Workflow des articles
4. ✅ Système de messagerie
5. ✅ Notifications
6. ✅ Upload de médias

---

## 🔙 Restauration

Si vous souhaitez revenir aux noms anglais :

```bash
php artisan migrate:rollback
```

La méthode `down()` de la migration restaurera automatiquement tous les noms originaux.

---

## 📌 Notes Importantes

1. **Sauvegarde** : Faites une sauvegarde complète de votre base de données avant d'exécuter cette migration
2. **Environnement** : Testez d'abord sur un environnement de développement
3. **API** : Mettez à jour toutes les routes API et les contrôleurs
4. **Frontend** : Mettez à jour le frontend React pour utiliser les nouveaux noms de colonnes
5. **Documentation** : Mettez à jour toute la documentation technique

---

## 🎯 Prochaines Étapes

1. Installer `doctrine/dbal`
2. Exécuter la migration
3. Mettre à jour tous les modèles Eloquent
4. Mettre à jour les contrôleurs et les routes
5. Mettre à jour le frontend
6. Tester toutes les fonctionnalités
7. Déployer sur la production

---

## 🆘 Dépannage

### Erreur : "doctrine/dbal not found"
```bash
composer require doctrine/dbal
```

### Erreur : "Column not found"
Vérifiez que la migration précédente a bien créé la colonne avant de la renommer.

### Erreur : "SQLSTATE[42S01]: Base table or view already exists"
La table existe déjà. Vérifiez l'état des migrations avec `php artisan migrate:status`.

---

## 📞 Support

Pour toute question ou problème, référez-vous à :
- Documentation Laravel : https://laravel.com/docs
- Migration de base de données : https://laravel.com/docs/migrations
- Doctrine DBAL : https://www.doctrine-project.org/projects/dbal.html

---

**Créé le** : 8 octobre 2025  
**Auteur** : Assistant IA  
**Version** : 1.0


