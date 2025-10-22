<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
	use HasFactory;

	protected $table = 'medias';

	protected $fillable = [
		'user_id','disque','chemin','type_mime','taille_octets','metadonnees',
	];

	protected $casts = [
		'metadonnees' => 'array',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


