<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicationSchedule extends Model
{
	use HasFactory;

	protected $table = 'planifications_publication';

	protected $fillable = [
		'article_id','planifie_pour','canal','statut','raison_echec',
	];

	protected $casts = [
		'planifie_pour' => 'datetime',
	];

	public function article()
	{
		return $this->belongsTo(Article::class);
	}
}


