<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleWorkflow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WorkflowController extends Controller
{
    /**
     * Soumettre un article pour révision
     */
    public function submitForReview(Request $request, Article $article)
    {
        $request->validate([
            'reviewer_id' => 'required|exists:users,id',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'utilisateur peut soumettre cet article
        if ($article->created_by !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que l'article est en brouillon
        if ($article->workflow_status !== 'draft') {
            return response()->json(['error' => 'Article déjà soumis'], 400);
        }

        $article->submitForReview($request->reviewer_id, $request->comment);

        return response()->json([
            'success' => true,
            'message' => 'Article soumis pour révision',
            'data' => $article->load(['currentReviewer.profile', 'workflowSteps'])
        ]);
    }

    /**
     * Réviser un article (Secrétaire de rédaction)
     */
    public function review(Request $request, Article $article)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'utilisateur est le réviseur actuel
        if ($article->current_reviewer_id !== Auth::id()) {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que l'article est soumis
        if ($article->workflow_status !== 'submitted') {
            return response()->json(['error' => 'Article non soumis'], 400);
        }

        $article->review($request->comment);

        return response()->json([
            'success' => true,
            'message' => 'Article révisé et envoyé au directeur',
            'data' => $article->load(['currentReviewer.profile', 'workflowSteps'])
        ]);
    }

    /**
     * Approuver un article (Directeur de publication)
     */
    public function approve(Request $request, Article $article)
    {
        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'utilisateur est le directeur de publication
        $user = Auth::user();
        if (!$user->profile || $user->profile->role !== 'directeur_publication') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que l'article est en révision
        if ($article->workflow_status !== 'in_review') {
            return response()->json(['error' => 'Article non en révision'], 400);
        }

        $article->approve($request->comment);

        return response()->json([
            'success' => true,
            'message' => 'Article approuvé',
            'data' => $article->load(['currentReviewer.profile', 'workflowSteps'])
        ]);
    }

    /**
     * Rejeter un article (Directeur de publication)
     */
    public function reject(Request $request, Article $article)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Vérifier que l'utilisateur est le directeur de publication
        $user = Auth::user();
        if (!$user->profile || $user->profile->role !== 'directeur_publication') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que l'article est en révision
        if ($article->workflow_status !== 'in_review') {
            return response()->json(['error' => 'Article non en révision'], 400);
        }

        $article->reject($request->reason, $request->comment);

        return response()->json([
            'success' => true,
            'message' => 'Article rejeté',
            'data' => $article->load(['currentReviewer.profile', 'workflowSteps'])
        ]);
    }

    /**
     * Publier un article (Directeur de publication)
     */
    public function publish(Request $request, Article $article)
    {
        // Vérifier que l'utilisateur est le directeur de publication
        $user = Auth::user();
        if (!$user->profile || $user->profile->role !== 'directeur_publication') {
            return response()->json(['error' => 'Non autorisé'], 403);
        }

        // Vérifier que l'article est approuvé
        if ($article->workflow_status !== 'approved') {
            return response()->json(['error' => 'Article non approuvé'], 400);
        }

        $article->publish();

        return response()->json([
            'success' => true,
            'message' => 'Article publié sur le site',
            'data' => $article->load(['currentReviewer.profile', 'workflowSteps'])
        ]);
    }

    /**
     * Obtenir les articles en attente pour l'utilisateur connecté
     */
    public function pendingArticles()
    {
        $user = Auth::user();

        $articles = Article::with(['creator.profile', 'folder', 'workflowSteps'])
            ->where('current_reviewer_id', $user->id)
            ->whereIn('workflow_status', ['submitted', 'in_review'])
            ->orderBy('submitted_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $articles
        ]);
    }

    /**
     * Obtenir l'historique du workflow d'un article
     */
    public function workflowHistory(Article $article)
    {
        $workflowHistory = $article->workflowSteps()
            ->with(['fromUser.profile', 'toUser.profile'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $workflowHistory
        ]);
    }

    /**
     * Obtenir les statistiques du workflow
     */
    public function workflowStats()
    {
        $user = Auth::user();

        $stats = [
            'my_articles' => [
                'draft' => Article::where('created_by', $user->id)->where('workflow_status', 'draft')->count(),
                'submitted' => Article::where('created_by', $user->id)->where('workflow_status', 'submitted')->count(),
                'in_review' => Article::where('created_by', $user->id)->where('workflow_status', 'in_review')->count(),
                'approved' => Article::where('created_by', $user->id)->where('workflow_status', 'approved')->count(),
                'rejected' => Article::where('created_by', $user->id)->where('workflow_status', 'rejected')->count(),
                'published' => Article::where('created_by', $user->id)->where('workflow_status', 'published')->count(),
            ],
            'pending_review' => Article::where('current_reviewer_id', $user->id)
                ->whereIn('workflow_status', ['submitted', 'in_review'])
                ->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
