<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
	use HasFactory;

	protected $table = 'journaux_audit';

	protected $fillable = [
		'actor_id','action','type_entite','entite_id','contexte','survenu_le',
	];

	protected $casts = [
		'contexte' => 'array',
		'survenu_le' => 'datetime',
	];

	public function actor()
	{
		return $this->belongsTo(User::class, 'actor_id');
	}
}


