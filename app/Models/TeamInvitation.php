<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
	use HasFactory;

	protected $fillable = [
		'email','role','token','invited_by','expires_at','accepted_at',
	];

	protected $casts = [
		'expires_at' => 'datetime',
		'accepted_at' => 'datetime',
	];

	public function inviter()
	{
		return $this->belongsTo(User::class, 'invited_by');
	}
}


