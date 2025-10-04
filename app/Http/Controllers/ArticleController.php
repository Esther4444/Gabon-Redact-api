<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
	public function index(Request $request)
	{
		$perPage = $request->query('per_page', 15); // Par défaut 15 articles par page
		$page = $request->query('page', 1);

		$query = Article::query()->with(['creator.profile','assignee.profile','folder']);

		// Recherche dans le titre et le contenu
		if ($search = $request->query('search')) {
			$query->where(function($q) use ($search) {
				$q->where('title','like',"%{$search}%")
				  ->orWhere('content','like',"%{$search}%")
				  ->orWhere('seo_title','like',"%{$search}%")
				  ->orWhere('seo_description','like',"%{$search}%");
			});
		}

		// Filtre par statut
		if ($status = $request->query('status')) {
			$query->where('status', $status);
		}

		// Filtre par dossier
		if ($folderId = $request->query('folder_id')) {
			$query->where('folder_id', $folderId);
		}

		// Filtre pour mes articles seulement
		if ($request->boolean('mine')) {
			$query->where(function($q) use ($request) {
				$q->where('created_by', $request->user()->id)
					->orWhere('assigned_to', $request->user()->id);
			});
		}

		// Tri par défaut : date de modification décroissante
		$query->orderByDesc('updated_at');

		// Pagination
		$articles = $query->paginate($perPage, ['*'], 'page', $page);

		return response()->json([
			'success' => true,
			'data' => $articles->items(),
			'pagination' => [
				'current_page' => $articles->currentPage(),
				'last_page' => $articles->lastPage(),
				'per_page' => $articles->perPage(),
				'total' => $articles->total(),
				'from' => $articles->firstItem(),
				'to' => $articles->lastItem(),
				'has_more_pages' => $articles->hasMorePages(),
				'prev_page_url' => $articles->previousPageUrl(),
				'next_page_url' => $articles->nextPageUrl(),
			]
		]);
	}

	public function store(Request $request)
	{
		// Vérifier que l'utilisateur est authentifié
		if (!$request->user()) {
			return response()->json(['success' => false, 'message' => 'Utilisateur non authentifié'], 401);
		}

		$validated = $request->validate([
			'title' => ['required','string','max:255'],
			'content' => ['nullable','string'],
			'folder_id' => ['nullable','exists:folders,id'],
			'assigned_to' => ['nullable','exists:users,id'],
			'seo_title' => ['nullable','string','max:255'],
			'seo_description' => ['nullable','string'],
			'seo_keywords' => ['nullable','array'],
		]);

		$slug = Str::slug($validated['title']);
		$slugBase = $slug; $i = 2;
		while (Article::where('slug', $slug)->exists()) { $slug = $slugBase.'-'.$i++; }

		$article = Article::create(array_merge($validated, [
			'status' => 'draft',
			'workflow_status' => 'draft',
			'slug' => $slug,
			'created_by' => $request->user()->id,
		]));

		return response()->json(['success' => true, 'data' => $article], 201);
	}

	public function show(Article $article)
	{
		$article->load(['creator.profile','assignee.profile','folder']);
		return response()->json(['success' => true, 'data' => $article]);
	}

	public function update(Request $request, Article $article)
	{
		// Cette méthode ne fait que mettre à jour les métadonnées, pas le contenu
		$validated = $request->validate([
			'folder_id' => ['sometimes','nullable','exists:folders,id'],
			'assigned_to' => ['sometimes','nullable','exists:users,id'],
			'seo_title' => ['sometimes','nullable','string','max:255'],
			'seo_description' => ['sometimes','nullable','string'],
			'seo_keywords' => ['sometimes','nullable','array'],
			'status' => ['sometimes','string','in:draft,published,review'],
		]);
		$article->fill($validated);
		$article->save();
		return response()->json(['success' => true, 'data' => $article]);
	}

	public function save(Request $request, Article $article)
	{
		// Méthode spécifique pour la sauvegarde manuelle du contenu
		$validated = $request->validate([
			'title' => ['required','string','max:255'],
			'content' => ['nullable','string'],
			'folder_id' => ['nullable','exists:folders,id'],
			'assigned_to' => ['nullable','exists:users,id'],
			'seo_title' => ['nullable','string','max:255'],
			'seo_description' => ['nullable','string'],
			'seo_keywords' => ['nullable','array'],
			'status' => ['nullable','string','in:draft,published,review'],
		]);

		// Mettre à jour le slug si le titre a changé
		if ($article->title !== $validated['title']) {
			$slug = Str::slug($validated['title']);
			$slugBase = $slug;
			$i = 2;
			while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
				$slug = $slugBase.'-'.$i++;
			}
			$validated['slug'] = $slug;
		}

		$article->fill($validated);
		$article->save();

		return response()->json([
			'success' => true,
			'message' => 'Article sauvegardé avec succès',
			'data' => $article->load(['creator.profile','assignee.profile','folder'])
		]);
	}

	public function preview(Article $article)
	{
		$article->load(['creator.profile','assignee.profile','folder']);
		return response()->json([
			'success' => true,
			'data' => $article,
			'preview_url' => url('/api/articles/preview/' . $article->slug)
		]);
	}

	public function publicPreview($slug)
	{
		$article = Article::where('slug', $slug)->firstOrFail();
		$article->load(['creator.profile','assignee.profile','folder']);

		return response()->json([
			'success' => true,
			'data' => $article,
			'meta' => [
				'title' => $article->seo_title ?: $article->title,
				'description' => $article->seo_description,
				'keywords' => $article->seo_keywords,
			]
		]);
	}

	public function destroy(Article $article)
	{
		$article->delete();
		return response()->json(['success' => true], 204);
	}
}


