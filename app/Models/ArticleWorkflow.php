<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleWorkflow extends Model
{
    use HasFactory;

    protected $table = 'workflow_articles';

    protected $fillable = [
        'article_id',
        'from_user_id',
        'to_user_id',
        'action',
        'statut',
        'commentaire',
        'action_le',
    ];

    protected $casts = [
        'action_le' => 'datetime',
    ];

    // Relations
    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function toUser()
    {
        return $this->belongsTo(User::class, 'to_user_id');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('statut', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('statut', 'completed');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('to_user_id', $userId);
    }

    // Actions possibles
    const ACTIONS = [
        'submitted' => 'Soumis pour révision',
        'reviewed' => 'Révisé',
        'approved' => 'Approuvé',
        'rejected' => 'Rejeté',
        'published' => 'Publié',
    ];

    const STATUSES = [
        'pending' => 'En attente',
        'completed' => 'Terminé',
        'rejected' => 'Rejeté',
    ];
}
