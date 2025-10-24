<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Services\NotificationService;
use App\Models\User;

class ArticleController extends Controller
{
	protected NotificationService $notificationService;

	public function __construct(NotificationService $notificationService = null)
	{
		$this->notificationService = $notificationService ?? app(NotificationService::class);
	}

	public function index(Request $request)
	{
		$perPage = $request->query('per_page', 15); // Par défaut 15 articles par page
		$page = $request->query('page', 1);

		$query = Article::query()->with(['creator.profile','assignee.profile','folder'])
			->select(['*']); // S'assurer que tous les champs sont sélectionnés

		// Recherche dans le titre et le contenu
		if ($search = $request->query('search')) {
			$query->where(function($q) use ($search) {
				$q->where('titre','like',"%{$search}%")
				  ->orWhere('contenu','like',"%{$search}%")
				  ->orWhere('titre_seo','like',"%{$search}%")
				  ->orWhere('description_seo','like',"%{$search}%");
			});
		}

		// Filtre par statut
		if ($status = $request->query('status')) {
			$query->where('statut', $status);
		}

		// Filtre par dossier
		if ($folderId = $request->query('folder_id')) {
			$query->where('dossier_id', $folderId);
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
			'folder_id' => ['nullable','exists:dossiers,id'],
			'assigned_to' => ['nullable','exists:users,id'],

			// SEO
			'seo_title' => ['nullable','string','max:60'],
			'seo_description' => ['nullable','string','max:160'],
			'seo_keywords' => ['nullable','array'],

			// Nouveaux champs
			'category' => ['nullable','string','max:100'],
			'tags' => ['nullable','array'],
			'tags.*' => ['string','max:50'],
			'featured_image' => ['nullable','url','max:500'],
			'excerpt' => ['nullable','string','max:500'],
			'language' => ['nullable','string','max:5'],
			'is_featured' => ['nullable','boolean'],
			'is_breaking_news' => ['nullable','boolean'],
			'allow_comments' => ['nullable','boolean'],
			'template' => ['nullable','string','max:50'],
			'author_bio' => ['nullable','string','max:1000'],
			'custom_css' => ['nullable','string'],
			'custom_js' => ['nullable','string'],
			'social_media_data' => ['nullable','array'],
		]);

		$slug = Str::slug($validated['title']);
		$slugBase = $slug; $i = 2;
		while (Article::where('slug', $slug)->exists()) { $slug = $slugBase.'-'.$i++; }

		$article = Article::create([
			'titre' => $validated['title'],
			'contenu' => $validated['content'] ?? null,
			'dossier_id' => $validated['folder_id'] ?? null,
			'assigned_to' => $validated['assigned_to'] ?? null,
			'titre_seo' => $validated['seo_title'] ?? null,
			'description_seo' => $validated['seo_description'] ?? null,
			'mots_cles_seo' => $validated['seo_keywords'] ?? null,

			// Nouveaux champs
			'category' => $validated['category'] ?? null,
			'tags' => $validated['tags'] ?? null,
			'featured_image' => $validated['featured_image'] ?? null,
			'excerpt' => $validated['excerpt'] ?? null,
			'language' => $validated['language'] ?? 'fr',
			'is_featured' => $validated['is_featured'] ?? false,
			'is_breaking_news' => $validated['is_breaking_news'] ?? false,
			'allow_comments' => $validated['allow_comments'] ?? true,
			'template' => $validated['template'] ?? 'default',
			'author_bio' => $validated['author_bio'] ?? null,
			'custom_css' => $validated['custom_css'] ?? null,
			'custom_js' => $validated['custom_js'] ?? null,
			'social_media_data' => $validated['social_media_data'] ?? null,

			'statut' => 'draft',
			'statut_workflow' => 'draft',
			'slug' => $slug,
			'created_by' => $request->user()->id,
		]);

		// Calculer automatiquement les métriques si le contenu est fourni
		if ($article->contenu) {
			$article->calculateWordCount();
			$article->calculateCharacterCount();
			$article->calculateReadingTime();
			if (!$article->excerpt) {
				$article->generateExcerpt(150);
			}
		}

		return response()->json(['success' => true, 'data' => $article->load(['creator.profile','assignee.profile','folder'])], 201);
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
			'folder_id' => ['sometimes','nullable','exists:dossiers,id'],
			'assigned_to' => ['sometimes','nullable','exists:users,id'],
			'seo_title' => ['sometimes','nullable','string','max:255'],
			'seo_description' => ['sometimes','nullable','string'],
			'seo_keywords' => ['sometimes','nullable','array'],
			'status' => ['sometimes','string','in:draft,published,review'],
		]);

		// Mapper les champs de la requête vers les colonnes francisées
		if (isset($validated['folder_id'])) $article->dossier_id = $validated['folder_id'];
		if (isset($validated['assigned_to'])) $article->assigned_to = $validated['assigned_to'];
		if (isset($validated['seo_title'])) $article->titre_seo = $validated['seo_title'];
		if (isset($validated['seo_description'])) $article->description_seo = $validated['seo_description'];
		if (isset($validated['seo_keywords'])) $article->mots_cles_seo = $validated['seo_keywords'];
		if (isset($validated['status'])) $article->statut = $validated['status'];

		$article->save();
		return response()->json(['success' => true, 'data' => $article]);
	}

	public function save(Request $request, Article $article)
	{
		// Méthode spécifique pour la sauvegarde manuelle du contenu
		$validated = $request->validate([
			'title' => ['required','string','max:255'],
			'content' => ['nullable','string'],
			'folder_id' => ['nullable','exists:dossiers,id'],
			'assigned_to' => ['nullable','exists:users,id'],
			'seo_title' => ['nullable','string','max:60'],
			'seo_description' => ['nullable','string','max:160'],
			'seo_keywords' => ['nullable','array'],
			'status' => ['nullable','string','in:draft,published,review'],

			// Nouveaux champs
			'category' => ['nullable','string','max:100'],
			'tags' => ['nullable','array'],
			'tags.*' => ['string','max:50'],
			'featured_image' => ['nullable','url','max:500'],
			'excerpt' => ['nullable','string','max:500'],
			'language' => ['nullable','string','max:5'],
			'is_featured' => ['nullable','boolean'],
			'is_breaking_news' => ['nullable','boolean'],
			'allow_comments' => ['nullable','boolean'],
			'template' => ['nullable','string','max:50'],
			'author_bio' => ['nullable','string','max:1000'],
			'social_media_data' => ['nullable','array'],
		]);

		// Mettre à jour le slug si le titre a changé
		if ($article->titre !== $validated['title']) {
			$slug = Str::slug($validated['title']);
			$slugBase = $slug;
			$i = 2;
			while (Article::where('slug', $slug)->where('id', '!=', $article->id)->exists()) {
				$slug = $slugBase.'-'.$i++;
			}
			$article->slug = $slug;
		}

		// Mapper les champs de base
		$article->titre = $validated['title'];
		$article->contenu = $validated['content'] ?? null;
		if (isset($validated['folder_id'])) $article->dossier_id = $validated['folder_id'];
		if (isset($validated['assigned_to'])) $article->assigned_to = $validated['assigned_to'];
		if (isset($validated['seo_title'])) $article->titre_seo = $validated['seo_title'];
		if (isset($validated['seo_description'])) $article->description_seo = $validated['seo_description'];
		if (isset($validated['seo_keywords'])) $article->mots_cles_seo = $validated['seo_keywords'];
		if (isset($validated['status'])) $article->statut = $validated['status'];

		// Mapper les nouveaux champs
		if (isset($validated['category'])) $article->category = $validated['category'];
		if (isset($validated['tags'])) $article->tags = $validated['tags'];
		if (isset($validated['featured_image'])) $article->featured_image = $validated['featured_image'];
		if (isset($validated['excerpt'])) $article->excerpt = $validated['excerpt'];
		if (isset($validated['language'])) $article->language = $validated['language'];
		if (isset($validated['is_featured'])) $article->is_featured = $validated['is_featured'];
		if (isset($validated['is_breaking_news'])) $article->is_breaking_news = $validated['is_breaking_news'];
		if (isset($validated['allow_comments'])) $article->allow_comments = $validated['allow_comments'];
		if (isset($validated['template'])) $article->template = $validated['template'];
		if (isset($validated['author_bio'])) $article->author_bio = $validated['author_bio'];
		if (isset($validated['social_media_data'])) $article->social_media_data = $validated['social_media_data'];

		$article->save();

		// Recalculer les métriques si le contenu a changé
		if ($article->wasChanged('contenu') && $article->contenu) {
			$article->calculateWordCount();
			$article->calculateCharacterCount();
			$article->calculateReadingTime();
			if (!$article->excerpt) {
				$article->generateExcerpt(150);
			}
		}

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
				'title' => $article->titre_seo ?: $article->titre,
				'description' => $article->description_seo,
				'keywords' => $article->mots_cles_seo,
			]
		]);
	}

	public function destroy(Article $article)
	{
		$article->delete();
		return response()->json(['success' => true], 204);
	}

	/**
	 * Soumettre un article pour révision
	 */
	public function submitForReview(Request $request, Article $article)
	{
		// Vérifier que c'est le créateur
		if ($article->created_by !== $request->user()->id && !$request->user()->hasPermission('articles:approve')) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		$validated = $request->validate([
			'reviewer_id' => ['required', 'exists:users,id'],
			'message' => ['nullable', 'string', 'max:1000']
		]);

		// Vérifier que le réviseur est secrétaire de rédaction
		$reviewer = User::find($validated['reviewer_id']);
		if ($reviewer->profile?->role !== 'secretaire_redaction') {
			return response()->json([
				'success' => false,
				'message' => 'Le réviseur doit être un secrétaire de rédaction'
			], 422);
		}

		// Mettre à jour l'article
		$article->update([
			'statut' => 'en_relecture',
			'statut_workflow' => 'submitted',
			'current_reviewer_id' => $validated['reviewer_id'],
			'submitted_at' => now(),
		]);

		// Notifier le réviseur
		$this->notificationService->notifyUser(
			$reviewer,
			'workflow',
			'Article soumis pour révision',
			"\"{$article->titre}\" a été soumis par " . $request->user()->name,
			"/articles/{$article->id}",
			[
				'article_id' => $article->id,
				'article_title' => $article->titre,
				'submitted_by' => $request->user()->name,
				'message' => $validated['message'] ?? null
			],
			$article->id,
			'article'
		);

		return response()->json([
			'success' => true,
			'message' => 'Article soumis pour révision',
			'data' => $article->load(['creator.profile', 'currentReviewer.profile', 'folder'])
		]);
	}

	/**
	 * Approuver un article
	 */
	public function approve(Request $request, Article $article)
	{
		// Vérifier que c'est le réviseur ou le directeur
		if ($article->current_reviewer_id !== $request->user()->id && !$request->user()->hasPermission('articles:approve')) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		if ($article->statut !== 'en_relecture') {
			return response()->json(['success' => false, 'message' => 'Article non en révision'], 422);
		}

		// Appeler la méthode approve du modèle qui gère la logique du workflow
		$comment = $request->input('comment');
		$article->approve($comment, $request->user());

		return response()->json([
			'success' => true,
			'message' => 'Article approuvé',
			'data' => $article->load(['creator.profile', 'currentReviewer.profile', 'folder'])
		]);
	}

	/**
	 * Rejeter un article
	 */
	public function reject(Request $request, Article $article)
	{
		// Vérifier que c'est le réviseur ou le directeur
		if ($article->current_reviewer_id !== $request->user()->id && !$request->user()->hasPermission('articles:approve')) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		if ($article->statut !== 'en_relecture') {
			return response()->json(['success' => false, 'message' => 'Article non en révision'], 422);
		}

		$validated = $request->validate([
			'reason' => ['required', 'string', 'max:1000']
		]);

		// Mettre à jour l'article
		$article->update([
			'statut' => 'brouillon',
			'statut_workflow' => 'rejected',
			'rejected_reason' => $validated['reason'],
			'reviewed_at' => now(),
			'current_reviewer_id' => null,
		]);

		// Notifier le créateur avec la raison
		$creator = $article->creator;
		$this->notificationService->notifyUser(
			$creator,
			'warning',
			'Article rejeté',
			"\"{$article->titre}\" a été rejeté. Raison: " . $validated['reason'],
			"/articles/{$article->id}",
			[
				'article_id' => $article->id,
				'article_title' => $article->titre,
				'rejected_by' => $request->user()->name,
				'reason' => $validated['reason']
			],
			$article->id,
			'article'
		);

		return response()->json([
			'success' => true,
			'message' => 'Article rejeté',
			'data' => $article->load(['creator.profile', 'currentReviewer.profile', 'folder'])
		]);
	}

	/**
	 * Partager un article avec d'autres journalistes
	 */
	public function shareArticle(Request $request, Article $article)
	{
		// Vérifier que c'est le créateur
		if ($article->created_by !== $request->user()->id) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		$validated = $request->validate([
			'user_id' => ['required', 'exists:users,id'],
			'permission' => ['nullable', 'in:view,edit'],
			'message' => ['nullable', 'string', 'max:1000']
		]);

		// Vérifier que l'utilisateur n'est pas le créateur
		if ($validated['user_id'] === $request->user()->id) {
			return response()->json(['success' => false, 'message' => 'Impossible de partager avec soi-même'], 422);
		}

		// Créer le partage
		$share = \App\Models\ArticleShare::firstOrCreate(
			[
				'article_id' => $article->id,
				'shared_with_user_id' => $validated['user_id']
			],
			[
				'shared_by_user_id' => $request->user()->id,
				'permission' => $validated['permission'] ?? 'edit',
				'message' => $validated['message'] ?? null,
			]
		);

		// Notifier l'utilisateur avec qui on partage
		$sharedUser = User::find($validated['user_id']);
		$this->notificationService->notifyUser(
			$sharedUser,
			'collaboration',
			'Article partagé avec vous',
			$request->user()->name . " a partagé \"{$article->titre}\" avec vous",
			"/articles/{$article->id}",
			[
				'article_id' => $article->id,
				'article_title' => $article->titre,
				'shared_by' => $request->user()->name,
				'message' => $validated['message'] ?? null
			],
			$article->id,
			'article'
		);

		return response()->json([
			'success' => true,
			'message' => 'Article partagé',
			'data' => $share
		], 201);
	}

	/**
	 * Récupérer les articles supprimés (corbeille)
	 */
	public function trashed(Request $request)
	{
		$articles = Article::onlyTrashed()
			->with(['creator.profile', 'folder'])
			->where('created_by', $request->user()->id)
			->orderByDesc('deleted_at')
			->get();

		return response()->json([
			'success' => true,
			'data' => $articles
		]);
	}

	/**
	 * Restaurer un article supprimé
	 */
	public function restore(Request $request, $id)
	{
		$article = Article::onlyTrashed()->findOrFail($id);

		// Vérifier que c'est le créateur
		if ($article->created_by !== $request->user()->id && !$request->user()->hasPermission('articles:manage')) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		$article->restore();

		return response()->json([
			'success' => true,
			'message' => 'Article restauré',
			'data' => $article->load(['creator.profile', 'folder'])
		]);
	}

	/**
	 * Supprimer définitivement un article
	 */
	public function forceDelete(Request $request, $id)
	{
		$article = Article::onlyTrashed()->findOrFail($id);

		// Vérifier que c'est le créateur ou un admin
		if ($article->created_by !== $request->user()->id && !$request->user()->hasPermission('articles:manage')) {
			return response()->json(['success' => false, 'message' => 'Non autorisé'], 403);
		}

		$article->forceDelete();

		return response()->json(['success' => true], 204);
	}
}


