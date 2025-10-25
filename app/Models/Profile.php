<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
	use HasFactory;

	protected $table = 'profiles';

	protected $fillable = [
		'user_id', 'full_name', 'nom_complet', 'matricule', 'avatar_url', 'url_avatar', 'role', 'preferences',
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


