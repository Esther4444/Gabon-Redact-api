<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\Request;

class FolderController extends Controller
{
	public function index(Request $request)
	{
		$query = Folder::query();

		// Filtrer par statut actif
		$query->where('is_active', true);

		// Trier par sort_order puis par nom
		$query->orderBy('sort_order', 'asc')
			  ->orderBy('nom', 'asc');

		// Inclure les statistiques si demandé
		if ($request->boolean('include_stats')) {
			$query->withCount([
				'articles as total_articles',
				'articles as draft_articles' => function($q) { $q->where('statut', 'brouillon'); },
				'articles as submitted_articles' => function($q) { $q->where('statut', 'en_relecture'); },
				'articles as approved_articles' => function($q) { $q->where('statut', 'approuve'); },
				'articles as published_articles' => function($q) { $q->where('statut', 'publie'); }
			]);
		}

		$folders = $query->get();

		return response()->json([
			'success' => true,
			'data' => $folders,
			'message' => 'Dossiers récupérés avec succès'
		]);
	}

	public function store(Request $request)
	{
		$validated = $request->validate([
			'name' => ['required','string','max:255'],
			'description' => ['nullable','string','max:1000'],
			'couleur' => ['nullable','string','max:7'],
			'icone' => ['nullable','string','max:50'],
		]);

		$folder = Folder::create([
			'owner_id' => $request->user()->id,
			'nom' => $validated['name'],
			'description' => $validated['description'] ?? null,
			'couleur' => $validated['couleur'] ?? '#6B7280',
			'icone' => $validated['icone'] ?? 'folder',
			'parent_id' => null,
			'sort_order' => 0,
			'is_active' => true,
		]);

		return response()->json([
			'success' => true,
			'data' => $folder,
			'message' => 'Dossier créé avec succès'
		], 201);
	}

	public function show(Folder $folder)
	{
		return response()->json(['success' => true, 'data' => $folder]);
	}

	public function update(Request $request, Folder $folder)
	{
		$validated = $request->validate([
			'name' => ['required','string','max:255'],
		]);
		$folder->nom = $validated['name'];
		$folder->save();
		return response()->json(['success' => true, 'data' => $folder]);
	}

	public function destroy(Folder $folder)
	{
		$folder->delete();
		return response()->json(['success' => true], 204);
	}

	/**
	 * Obtenir les statistiques d'un dossier
	 */
	public function stats(Folder $folder)
	{
		$stats = [
			'id' => $folder->id,
			'nom' => $folder->nom,
			'total_articles' => $folder->articles()->count(),
			'draft_articles' => $folder->articles()->where('statut', 'brouillon')->count(),
			'submitted_articles' => $folder->articles()->where('statut', 'en_relecture')->count(),
			'approved_articles' => $folder->articles()->where('statut', 'approuve')->count(),
			'published_articles' => $folder->articles()->where('statut', 'publie')->count(),
			'last_activity' => $folder->articles()->latest('updated_at')->value('updated_at'),
			'created_at' => $folder->created_at,
		];

		return response()->json([
			'success' => true,
			'data' => $stats
		]);
	}

	/**
	 * Obtenir la hiérarchie des dossiers
	 */
	public function hierarchy()
	{
		$folders = Folder::where('is_active', true)
			->whereNull('parent_id')
			->with('children')
			->orderBy('sort_order', 'asc')
			->get();

		return response()->json([
			'success' => true,
			'data' => $folders
		]);
	}

	/**
	 * Obtenir les dossiers les plus actifs
	 */
	public function mostActive(Request $request)
	{
		$limit = $request->get('limit', 10);

		$folders = Folder::where('is_active', true)
			->withCount('articles')
			->orderBy('articles_count', 'desc')
			->limit($limit)
			->get();

		return response()->json([
			'success' => true,
			'data' => $folders
		]);
	}

	/**
	 * Récupérer les dossiers supprimés (corbeille)
	 */
	public function trashed(Request $request)
	{
		$folders = Folder::onlyTrashed()
			->withCount('articles')
			->orderByDesc('deleted_at')
			->get();

		return response()->json([
			'success' => true,
			'data' => $folders
		]);
	}

	/**
	 * Restaurer un dossier supprimé
	 */
	public function restore(Request $request, $id)
	{
		$folder = Folder::onlyTrashed()->findOrFail($id);
		$folder->restore();

		return response()->json([
			'success' => true,
			'message' => 'Dossier restauré',
			'data' => $folder
		]);
	}

	/**
	 * Supprimer définitivement un dossier
	 */
	public function forceDelete(Request $request, $id)
	{
		$folder = Folder::onlyTrashed()->findOrFail($id);
		$folder->forceDelete();

		return response()->json(['success' => true], 204);
	}
}


