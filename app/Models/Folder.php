<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
	use HasFactory, SoftDeletes;

	protected $table = 'dossiers';

	protected $fillable = [
		'owner_id', 'nom', 'description', 'color', 'icon', 'parent_id', 'sort_order', 'is_active'
	];

	protected $casts = [
		'sort_order' => 'integer',
		'is_active' => 'boolean',
	];

	public function owner()
	{
		return $this->belongsTo(User::class, 'owner_id');
	}

	public function articles()
	{
		return $this->hasMany(Article::class, 'dossier_id');
	}

	// Relations hiérarchiques
	public function parent()
	{
		return $this->belongsTo(Folder::class, 'parent_id');
	}

	public function children()
	{
		return $this->hasMany(Folder::class, 'parent_id')->orderBy('sort_order');
	}

	// Scopes
	public function scopeActive($query)
	{
		return $query->where('is_active', true);
	}

	public function scopeRoot($query)
	{
		return $query->whereNull('parent_id');
	}

	// Méthodes utilitaires
	public function getFullPath()
	{
		$path = [$this->nom];
		$parent = $this->parent;

		while ($parent) {
			array_unshift($path, $parent->nom);
			$parent = $parent->parent;
		}

		return implode(' > ', $path);
	}

	public function getDepth()
	{
		$depth = 0;
		$parent = $this->parent;

		while ($parent) {
			$depth++;
			$parent = $parent->parent;
		}

		return $depth;
	}
}


