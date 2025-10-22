<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'recipient_id',
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

    // Relations
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function parentMessage()
    {
        return $this->belongsTo(Message::class, 'message_parent_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'message_parent_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('est_lu', false);
    }

    public function scopeRead($query)
    {
        return $query->where('est_lu', true);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    public function scopeFromUser($query, $userId)
    {
        return $query->where('sender_id', $userId);
    }

    public function scopeRelatedToArticle($query, $articleId)
    {
        return $query->where('article_id', $articleId);
    }

    // Méthodes
    public function markAsRead()
    {
        $this->update([
            'est_lu' => true,
            'lu_le' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'est_lu' => false,
            'lu_le' => null,
        ]);
    }

    public function isReply()
    {
        return !is_null($this->message_parent_id);
    }

    public function hasReplies()
    {
        return $this->replies()->exists();
    }
}
