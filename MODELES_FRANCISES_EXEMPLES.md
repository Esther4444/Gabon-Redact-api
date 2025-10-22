# 🔄 Guide de Mise à Jour des Modèles Laravel (Français)

Ce document fournit des exemples complets de mise à jour des modèles Laravel après la migration de francisation.

---

## 📝 Modèle User (Utilisateur)

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // Nom de la table francisée
    protected $table = 'utilisateurs';

    // Colonnes francisées
    protected $fillable = [
        'nom',
        'email',
        'mot_de_passe',
        'est_actif',
        'derniere_connexion_le',
        'tentatives_connexion_echouees',
        'verrouille_jusqu_au',
    ];

    protected $hidden = [
        'mot_de_passe',
        'jeton_souvenir',
    ];

    protected $casts = [
        'email_verifie_le' => 'datetime',
        'mot_de_passe' => 'hashed',
        'est_actif' => 'boolean',
        'derniere_connexion_le' => 'datetime',
        'verrouille_jusqu_au' => 'datetime',
    ];

    // Constantes pour les timestamps francisés
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    /**
     * Get the user's profile.
     */
    public function profil()
    {
        return $this->hasOne(Profile::class, 'utilisateur_id');
    }

    /**
     * Get articles created by user
     */
    public function articlesCreés()
    {
        return $this->hasMany(Article::class, 'cree_par');
    }

    /**
     * Get articles assigned to user
     */
    public function articlesAssignés()
    {
        return $this->hasMany(Article::class, 'assigne_a');
    }

    /**
     * Get folders owned by user
     */
    public function dossiers()
    {
        return $this->hasMany(Folder::class, 'proprietaire_id');
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActif($query)
    {
        return $query->where('est_actif', true);
    }

    /**
     * Scope pour les utilisateurs non verrouillés
     */
    public function scopePasVerrouille($query)
    {
        return $query->where(function($q) {
            $q->whereNull('verrouille_jusqu_au')
              ->orWhere('verrouille_jusqu_au', '<', now());
        });
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permission)
    {
        $role = $this->profil->role ?? 'journaliste';
        $permissions = $this->getRolePermissions($role);

        return in_array($permission, $permissions);
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getAllPermissions()
    {
        $role = $this->profil->role ?? 'journaliste';
        return $this->getRolePermissions($role);
    }

    private function getRolePermissions($role)
    {
        $permissions = [
            'journaliste' => [
                'articles:read', 'articles:write', 'articles:own:delete',
                'comments:read', 'comments:write',
                'media:upload', 'media:read',
                'messages:read', 'messages:write',
                'notifications:read',
            ],
            'secretaire_redaction' => [
                'articles:read', 'articles:write', 'articles:review',
                'articles:assign', 'articles:reject',
                'users:read', 'analytics:read',
                'comments:read', 'comments:write', 'comments:moderate',
                'media:upload', 'media:read', 'media:manage',
                'messages:read', 'messages:write',
                'notifications:read', 'notifications:write',
            ],
            'directeur_publication' => [
                'articles:read', 'articles:write', 'articles:approve', 'articles:publish',
                'users:manage', 'analytics:read', 'audit:read',
                'settings:manage', 'team:manage',
                'comments:read', 'comments:write', 'comments:moderate', 'comments:delete',
                'media:upload', 'media:read', 'media:manage', 'media:delete',
                'messages:read', 'messages:write',
                'notifications:read', 'notifications:write',
            ],
            'social_media_manager' => [
                'articles:read', 'articles:share',
                'analytics:read', 'analytics:write',
                'social:manage',
                'media:read', 'media:upload',
                'messages:read', 'messages:write',
                'notifications:read',
            ],
        ];

        return $permissions[$role] ?? [];
    }
}
```

---

## 📰 Modèle Article

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'articles'; // Reste en français

    protected $fillable = [
        'titre',
        'slug',
        'contenu',
        'statut',
        'statut_workflow',
        'dossier_id',
        'cree_par',
        'assigne_a',
        'relecteur_actuel_id',
        'titre_seo',
        'description_seo',
        'mots_cles_seo',
        'publie_le',
        'soumis_le',
        'relu_le',
        'approuve_le',
        'raison_rejet',
        'historique_workflow',
        'metadonnees',
    ];

    protected $casts = [
        'publie_le' => 'datetime',
        'soumis_le' => 'datetime',
        'relu_le' => 'datetime',
        'approuve_le' => 'datetime',
        'mots_cles_seo' => 'array',
        'historique_workflow' => 'array',
        'metadonnees' => 'array',
    ];

    // Constantes pour les timestamps
    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';
    const DELETED_AT = 'supprime_le';

    // Relations
    public function dossier()
    {
        return $this->belongsTo(Folder::class, 'dossier_id');
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'cree_par');
    }

    public function assigné()
    {
        return $this->belongsTo(User::class, 'assigne_a');
    }

    public function relecteurActuel()
    {
        return $this->belongsTo(User::class, 'relecteur_actuel_id');
    }

    public function commentaires()
    {
        return $this->hasMany(Comment::class, 'article_id');
    }

    public function planifications()
    {
        return $this->hasMany(PublicationSchedule::class, 'article_id');
    }

    public function étapesWorkflow()
    {
        return $this->hasMany(ArticleWorkflow::class, 'article_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class, 'article_id');
    }

    // Scopes pour le workflow
    public function scopeParStatutWorkflow($query, $statut)
    {
        return $query->where('statut_workflow', $statut);
    }

    public function scopePourRelecteur($query, $utilisateurId)
    {
        return $query->where('relecteur_actuel_id', $utilisateurId);
    }

    public function scopeSoumis($query)
    {
        return $query->where('statut_workflow', 'submitted');
    }

    public function scopeEnRevision($query)
    {
        return $query->where('statut_workflow', 'in_review');
    }

    public function scopeApprouvé($query)
    {
        return $query->where('statut_workflow', 'approved');
    }

    public function scopeRejeté($query)
    {
        return $query->where('statut_workflow', 'rejected');
    }

    // Constantes pour les statuts de workflow
    const WORKFLOW_STATUSES = [
        'draft' => 'En cours de rédaction',
        'submitted' => 'Soumis pour révision',
        'in_review' => 'En révision',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'published' => 'Publié',
    ];

    // Méthodes pour le workflow
    public function soumettrePourRevision($relecteurId, $commentaire = null)
    {
        $this->update([
            'statut_workflow' => 'submitted',
            'relecteur_actuel_id' => $relecteurId,
            'soumis_le' => now(),
        ]);

        // Créer une étape de workflow
        ArticleWorkflow::create([
            'article_id' => $this->id,
            'de_utilisateur_id' => $this->cree_par,
            'a_utilisateur_id' => $relecteurId,
            'action' => 'submitted',
            'statut' => 'pending',
            'commentaire' => $commentaire,
        ]);

        // Envoyer une notification
        $this->envoyerNotification($relecteurId, 'Nouvel article soumis pour révision', $this->titre);
    }

    public function réviser($commentaire = null)
    {
        $this->update([
            'statut_workflow' => 'in_review',
            'relu_le' => now(),
        ]);

        // Mettre à jour l'étape de workflow
        $workflow = $this->étapesWorkflow()->where('statut', 'pending')->first();
        if ($workflow) {
            $workflow->update([
                'statut' => 'completed',
                'action' => 'reviewed',
                'commentaire' => $commentaire,
                'action_le' => now(),
            ]);
        }
    }

    public function approuver($commentaire = null)
    {
        $this->update([
            'statut_workflow' => 'approved',
            'approuve_le' => now(),
        ]);

        // Mettre à jour l'étape de workflow
        $workflow = $this->étapesWorkflow()->where('statut', 'pending')->first();
        if ($workflow) {
            $workflow->update([
                'statut' => 'completed',
                'action' => 'approved',
                'commentaire' => $commentaire,
                'action_le' => now(),
            ]);
        }

        // Notifier l'auteur
        $this->envoyerNotification($this->cree_par, 'Votre article a été approuvé', $this->titre);
    }

    public function rejeter($raison, $commentaire = null)
    {
        $this->update([
            'statut_workflow' => 'rejected',
            'raison_rejet' => $raison,
        ]);

        // Mettre à jour l'étape de workflow
        $workflow = $this->étapesWorkflow()->where('statut', 'pending')->first();
        if ($workflow) {
            $workflow->update([
                'statut' => 'rejected',
                'action' => 'rejected',
                'commentaire' => $commentaire,
                'action_le' => now(),
            ]);
        }

        // Notifier l'auteur
        $this->envoyerNotification($this->cree_par, 'Votre article a été rejeté', $this->titre . ' - Raison: ' . $raison);
    }

    public function publier()
    {
        $this->update([
            'statut_workflow' => 'published',
            'statut' => 'published',
            'publie_le' => now(),
        ]);

        // Notifier l'auteur
        $this->envoyerNotification($this->cree_par, 'Votre article a été publié', $this->titre);
    }

    private function envoyerNotification($utilisateurId, $message, $titre)
    {
        Notification::create([
            'utilisateur_id' => $utilisateurId,
            'type' => 'workflow',
            'titre' => $titre,
            'message' => $message,
            'donnees' => [
                'article_id' => $this->id,
                'article_titre' => $titre,
            ],
        ]);
    }
}
```

---

## 📁 Modèle Folder (Dossier)

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    protected $table = 'dossiers';

    protected $fillable = [
        'proprietaire_id',
        'nom',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    public function proprietaire()
    {
        return $this->belongsTo(User::class, 'proprietaire_id');
    }

    public function articles()
    {
        return $this->hasMany(Article::class, 'dossier_id');
    }
}
```

---

## 💬 Modèle Comment (Commentaire)

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'commentaires';

    protected $fillable = [
        'article_id',
        'auteur_id',
        'contenu',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function auteur()
    {
        return $this->belongsTo(User::class, 'auteur_id');
    }
}
```

---

## 💌 Modèle Message

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $table = 'messages';

    protected $fillable = [
        'expediteur_id',
        'destinataire_id',
        'sujet',
        'contenu',
        'est_lu',
        'article_id',
        'message_parent_id',
        'pieces_jointes',
        'lu_le',
    ];

    protected $casts = [
        'est_lu' => 'boolean',
        'pieces_jointes' => 'array',
        'lu_le' => 'datetime',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    // Relations
    public function expediteur()
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function destinataire()
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function messageParent()
    {
        return $this->belongsTo(Message::class, 'message_parent_id');
    }

    public function réponses()
    {
        return $this->hasMany(Message::class, 'message_parent_id');
    }

    // Scopes
    public function scopeNonLu($query)
    {
        return $query->where('est_lu', false);
    }

    public function scopeLu($query)
    {
        return $query->where('est_lu', true);
    }

    public function scopePourUtilisateur($query, $utilisateurId)
    {
        return $query->where('destinataire_id', $utilisateurId);
    }

    public function scopeDeUtilisateur($query, $utilisateurId)
    {
        return $query->where('expediteur_id', $utilisateurId);
    }

    // Méthodes
    public function marquerCommeLu()
    {
        $this->update([
            'est_lu' => true,
            'lu_le' => now(),
        ]);
    }

    public function marquerCommeNonLu()
    {
        $this->update([
            'est_lu' => false,
            'lu_le' => null,
        ]);
    }

    public function estUneRéponse()
    {
        return !is_null($this->message_parent_id);
    }

    public function aDesRéponses()
    {
        return $this->réponses()->exists();
    }
}
```

---

## 🔔 Modèle Notification

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'notifications';

    protected $fillable = [
        'utilisateur_id',
        'type',
        'titre',
        'message',
        'lu',
        'donnees',
    ];

    protected $casts = [
        'lu' => 'boolean',
        'donnees' => 'array',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }

    public function scopeNonLu($query)
    {
        return $query->where('lu', false);
    }

    public function scopeLu($query)
    {
        return $query->where('lu', true);
    }

    public function marquerCommeLu()
    {
        $this->update(['lu' => true]);
    }
}
```

---

## 📊 Modèle Profile (Profil)

### ✅ Version Francisée

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $table = 'profils';

    protected $fillable = [
        'utilisateur_id',
        'nom_complet',
        'url_avatar',
        'role',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
    ];

    const CREATED_AT = 'cree_le';
    const UPDATED_AT = 'modifie_le';

    public function utilisateur()
    {
        return $this->belongsTo(User::class, 'utilisateur_id');
    }
}
```

---

## 🔄 Résumé des Changements Importants

### Constantes à Définir

Tous les modèles doivent définir ces constantes :

```php
const CREATED_AT = 'cree_le';
const UPDATED_AT = 'modifie_le';
const DELETED_AT = 'supprime_le'; // Pour SoftDeletes
```

### Relations à Mettre à Jour

1. **Foreign Keys** : Mettez à jour tous les noms de colonnes dans les relations
2. **Noms de Méthodes** : Optionnellement, francisez les noms de méthodes
3. **Scopes** : Mettez à jour les noms de colonnes dans les scopes

### Exemple de Requête Mise à Jour

```php
// AVANT
$articles = Article::where('status', 'published')
    ->where('created_by', auth()->id())
    ->with('folder', 'creator')
    ->orderBy('created_at', 'desc')
    ->get();

// APRÈS
$articles = Article::where('statut', 'published')
    ->where('cree_par', auth()->id())
    ->with('dossier', 'createur')
    ->orderBy('cree_le', 'desc')
    ->get();
```

---

## ⚠️ Points d'Attention

1. **Authentication** : Le modèle User doit toujours implémenter les interfaces Laravel
2. **Sanctum** : Les tokens d'accès utiliseront la table `jetons_acces_personnel`
3. **Passwords** : La colonne s'appelle maintenant `mot_de_passe`
4. **Hashing** : Assurez-vous que le cast `'hashed'` fonctionne avec `mot_de_passe`

---

Bon courage avec la migration ! 🚀


