<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
	use HasFactory;

	protected $fillable = [
		'actor_id','action','entity_type','entity_id','context','occurred_at',
	];

	protected $casts = [
		'context' => 'array',
		'occurred_at' => 'datetime',
	];

	public function actor()
	{
		return $this->belongsTo(User::class, 'actor_id');
	}
}


