<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
        'failed_login_attempts',
        'locked_until',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    /**
     * Get the user's profile.
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission($permission)
    {
        $role = $this->profile->role ?? 'journaliste';
        $permissions = $this->getRolePermissions($role);

        return in_array($permission, $permissions);
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getAllPermissions()
    {
        $role = $this->profile->role ?? 'journaliste';
        return $this->getRolePermissions($role);
    }

    /**
     * Obtenir les permissions par rôle
     */
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

    /**
     * Vérifier si l'utilisateur peut effectuer une action sur une ressource
     */
    public function can($ability, $arguments = [])
    {
        if (is_string($ability)) {
            return $this->hasPermission($ability);
        }

        // Pour les policies Laravel
        return parent::can($ability, $arguments);
    }

    /**
     * Scope pour les utilisateurs actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour les utilisateurs non verrouillés
     */
    public function scopeNotLocked($query)
    {
        return $query->where(function($q) {
            $q->whereNull('locked_until')
              ->orWhere('locked_until', '<', now());
        });
    }
}
