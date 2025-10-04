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
        'subject',
        'body',
        'is_read',
        'article_id',
        'parent_message_id',
        'attachments',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'attachments' => 'array',
        'read_at' => 'datetime',
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
        return $this->belongsTo(Message::class, 'parent_message_id');
    }

    public function replies()
    {
        return $this->hasMany(Message::class, 'parent_message_id');
    }

    // Scopes
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeRead($query)
    {
        return $query->where('is_read', true);
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
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    public function isReply()
    {
        return !is_null($this->parent_message_id);
    }

    public function hasReplies()
    {
        return $this->replies()->exists();
    }
}
