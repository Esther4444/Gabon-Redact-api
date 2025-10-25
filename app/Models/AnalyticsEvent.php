<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsEvent extends Model
{
	use HasFactory;

	protected $table = 'analytics_events';

	protected $fillable = [
		'user_id','type_evenement','proprietes','survenu_le',
	];

	protected $casts = [
		'proprietes' => 'array',
		'survenu_le' => 'datetime',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


