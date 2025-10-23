<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'article_id',
        'shared_by_user_id',
        'shared_with_user_id',
        'permission',
        'message',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function sharedByUser()
    {
        return $this->belongsTo(User::class, 'shared_by_user_id');
    }

    public function sharedWithUser()
    {
        return $this->belongsTo(User::class, 'shared_with_user_id');
    }
}
