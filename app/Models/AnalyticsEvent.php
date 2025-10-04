<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id','event_type','properties','occurred_at',
	];

	protected $casts = [
		'properties' => 'array',
		'occurred_at' => 'datetime',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


