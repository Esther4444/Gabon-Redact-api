<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
	use HasFactory;

	protected $fillable = [
		'owner_id', 'name',
	];

	public function owner()
	{
		return $this->belongsTo(User::class, 'owner_id');
	}

	public function articles()
	{
		return $this->hasMany(Article::class);
	}
}


