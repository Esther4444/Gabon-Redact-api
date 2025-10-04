<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationSchedule extends Model
{
	use HasFactory;

	protected $fillable = [
		'article_id','scheduled_for','channel','status','failure_reason',
	];

	protected $casts = [
		'scheduled_for' => 'datetime',
	];

	public function article()
	{
		return $this->belongsTo(Article::class);
	}
}


