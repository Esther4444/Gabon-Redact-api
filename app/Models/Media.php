<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
	use HasFactory;

	protected $fillable = [
		'user_id','disk','path','mime_type','size_bytes','meta',
	];

	protected $casts = [
		'meta' => 'array',
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}
}


