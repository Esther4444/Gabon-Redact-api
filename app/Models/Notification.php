<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'title',
        'message',
        'read',
        'action_url',
        'metadata',
        'user_id',
        'related_id',
        'related_type',
        'read_at'
    ];

    protected $casts = [
        'read' => 'boolean',
        'metadata' => 'array',
        'read_at' => 'datetime'
    ];

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation polymorphe avec l'entité liée
     */
    public function related(): MorphTo
    {
        return $this->morphTo('related');
    }

    /**
     * Marquer comme lu
     */
    public function markAsRead(): void
    {
        $this->update([
            'read' => true,
            'read_at' => now()
        ]);
    }

    /**
     * Marquer comme non lu
     */
    public function markAsUnread(): void
    {
        $this->update([
            'read' => false,
            'read_at' => null
        ]);
    }

    /**
     * Scope pour les notifications non lues
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope pour les notifications lues
     */
    public function scopeRead($query)
    {
        return $query->where('read', true);
    }

    /**
     * Scope pour un type spécifique
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope pour un utilisateur spécifique
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Obtenir le temps écoulé depuis la création
     */
    public function getTimeAgoAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Obtenir l'icône selon le type
     */
    public function getIconAttribute(): string
    {
        return match($this->type) {
            'success' => 'check-circle',
            'warning' => 'alert-triangle',
            'info' => 'info',
            'error' => 'x-circle',
            default => 'bell'
        };
    }

    /**
     * Obtenir la couleur selon le type
     */
    public function getColorAttribute(): string
    {
        return match($this->type) {
            'success' => 'green',
            'warning' => 'yellow',
            'info' => 'blue',
            'error' => 'red',
            default => 'gray'
        };
    }
}
