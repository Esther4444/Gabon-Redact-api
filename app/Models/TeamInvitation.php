<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamInvitation extends Model
{
	use HasFactory;

	protected $table = 'invitations_equipe';

	protected $fillable = [
		'email','role','jeton','invited_by','expire_le','accepte_le',
	];

	protected $casts = [
		'expire_le' => 'datetime',
		'accepte_le' => 'datetime',
	];

	public function inviter()
	{
		return $this->belongsTo(User::class, 'invited_by');
	}
}


