<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
	use HasFactory;

	protected $table = 'profils';

	protected $fillable = [
		'user_id', 'nom_complet', 'matricule', 'url_avatar', 'role', 'preferences',
		'bio', 'social_links', 'signature', 'phone', 'department', 'specialization', 'timezone'
	];

	protected $casts = [
		'preferences' => 'array',
		'social_links' => 'array',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


