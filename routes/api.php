<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\ArticleStatusController;
use App\Http\Controllers\ArticlePublishController;
use App\Http\Controllers\ArticleSlugController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamInvitationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\PodcastController;
use App\Http\Controllers\TranscriptionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\WorkflowController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| API Routes v1
|--------------------------------------------------------------------------
|
| Routes API versionnées pour l'application Gabon Quotidien Rédac Pro
| Toutes les routes sont préfixées par /api/v1/
|
*/

// ============================================================================
// ROUTES PUBLIQUES (sans authentification)
// ============================================================================

Route::prefix('v1')->group(function () {
    // Authentification
    Route::post('auth/login', [AuthController::class, 'login']);

    // Routes publiques
    Route::get('articles/preview/{slug}', [ArticleController::class, 'publicPreview']);

    // ============================================================================
    // ROUTES PROTÉGÉES (avec authentification)
    // ============================================================================

    // Route de test sans authentification
Route::get('/test-folders', function () {
    return response()->json([
        'success' => true,
        'data' => \App\Models\Folder::all(['id', 'nom', 'description', 'couleur', 'icone']),
        'message' => 'Test des dossiers sans authentification'
    ]);
});

// Route de test pour les utilisateurs sans authentification
Route::get('/test-users', function () {
    $users = \App\Models\User::with('profile')->get();
    return response()->json([
        'success' => true,
        'data' => $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->profile?->role ?? 'journaliste',
                'full_name' => $user->profile?->nom_complet ?? $user->name,
            ];
        }),
        'message' => 'Test des utilisateurs sans authentification'
    ]);
});

// Route de test pour les notifications sans authentification
Route::get('/test-notifications', function () {
    try {
        $notifications = \App\Models\Notification::with('user')->get();
        return response()->json([
            'success' => true,
            'data' => $notifications->map(function($notif) {
                return [
                    'id' => $notif->id,
                    'type' => $notif->type,
                    'title' => $notif->title,
                    'message' => $notif->message,
                    'read' => $notif->read,
                    'created_at' => $notif->created_at,
                    'user' => $notif->user?->name
                ];
            }),
            'count' => $notifications->count(),
            'message' => 'Test des notifications sans authentification'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors du test des notifications'
        ]);
    }
});

// Route de test pour les articles sans authentification
Route::get('/test-articles', function () {
    try {
        $articles = \App\Models\Article::with(['creator', 'folder'])->get();
        return response()->json([
            'success' => true,
            'data' => $articles->map(function($article) {
                return [
                    'id' => $article->id,
                    'title' => $article->titre,
                    'status' => $article->statut,
                    'author' => $article->creator?->name,
                    'folder' => $article->folder?->nom,
                    'created_at' => $article->created_at
                ];
            }),
            'count' => $articles->count(),
            'message' => 'Test des articles sans authentification'
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'message' => 'Erreur lors du test des articles'
        ]);
    }
});

Route::middleware('auth:sanctum')->group(function () {

        // ============================================================================
        // AUTHENTIFICATION ET UTILISATEURS
        // ============================================================================

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('verify-2fa', [AuthController::class, 'verify2FA']);
            Route::get('users', [AuthController::class, 'availableUsers'])->middleware('permission:users:read');
        });

        Route::prefix('users')->group(function () {
            Route::get('profile', [UserController::class, 'profile']);
            Route::put('profile', [UserController::class, 'updateProfile']);
            Route::get('/', [UserController::class, 'index']); // Temporairement sans permission
            Route::get('{user}', [UserController::class, 'show'])->middleware('permission:users:read');
            Route::put('{user}', [UserController::class, 'update'])->middleware('permission:users:manage');
            Route::delete('{user}', [UserController::class, 'destroy'])->middleware('permission:users:manage');
        });

        // ============================================================================
        // ARTICLES ET WORKFLOW
        // ============================================================================

        Route::prefix('articles')->group(function () {
            // Routes spéciales AVANT les routes avec paramètres
            Route::get('trashed', [ArticleController::class, 'trashed']);

            // CRUD Articles (permissions temporairement désactivées pour debug)
            Route::get('/', [ArticleController::class, 'index']);
            Route::post('/', [ArticleController::class, 'store']);
            Route::get('{article}', [ArticleController::class, 'show']);
            Route::put('{article}', [ArticleController::class, 'update']);
            Route::delete('{article}', [ArticleController::class, 'destroy']);

            // Actions spécifiques
            Route::get('{article}/preview', [ArticleController::class, 'preview']);
            Route::post('{article}/save', [ArticleController::class, 'save']);
            Route::post('{article}/slug', [ArticleSlugController::class, 'generate']);
            Route::patch('{article}/status', [ArticleStatusController::class, 'update']);

            // Workflow (permissions vérifiées dans les controllers)
            Route::post('{article}/submit-for-review', [ArticleController::class, 'submitForReview']);
            Route::post('{article}/approve', [ArticleController::class, 'approve']);
            Route::post('{article}/reject', [ArticleController::class, 'reject']);
            Route::post('{article}/share', [ArticleController::class, 'shareArticle']);

            // Gestion de la corbeille
            Route::post('{article}/restore', [ArticleController::class, 'restore']);
            Route::delete('{article}/force', [ArticleController::class, 'forceDelete']);

            // Commentaires
            Route::get('{article}/comments', [CommentController::class, 'index']);
            Route::post('{article}/comments', [CommentController::class, 'store']);
        });

        // Workflow global
        Route::prefix('workflow')->group(function () {
            Route::get('pending-articles', [WorkflowController::class, 'pendingArticles']);
            Route::get('stats', [WorkflowController::class, 'workflowStats']);
            Route::get('secretary', [WorkflowController::class, 'getSecretary']);
        });

        // ============================================================================
        // MESSAGERIE ET COLLABORATION
        // ============================================================================

        Route::prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index']);
            Route::post('/', [MessageController::class, 'store']);
            Route::get('{message}', [MessageController::class, 'show']);
            Route::delete('{message}', [MessageController::class, 'destroy']);
            Route::post('{message}/reply', [MessageController::class, 'reply']);
            Route::patch('{message}/read', [MessageController::class, 'markAsRead']);
            Route::patch('{message}/unread', [MessageController::class, 'markAsUnread']);
            Route::get('unread/count', [MessageController::class, 'unread']);
        });

        Route::get('conversations', [MessageController::class, 'conversations']);

        // Commentaires globaux
        Route::prefix('comments')->group(function () {
            Route::put('{comment}', [CommentController::class, 'update'])->middleware('permission:comments:write');
            Route::delete('{comment}', [CommentController::class, 'destroy'])->middleware('permission:comments:write');
        });

        // ============================================================================
        // MÉDIAS ET CONTENU MULTIMÉDIA
        // ============================================================================

        Route::prefix('media')->group(function () {
            Route::post('upload', [MediaController::class, 'upload']); // Permission temporairement désactivée pour debug
            Route::get('/', [MediaController::class, 'index'])->middleware('permission:media:read');
            Route::delete('{media}', [MediaController::class, 'destroy'])->middleware('permission:media:delete');
            Route::post('{media}/transcribe', [TranscriptionController::class, 'transcribe'])->middleware('permission:media:upload');
        });

        // Podcasts
        Route::prefix('podcasts')->group(function () {
            Route::post('upload', [PodcastController::class, 'upload'])->middleware('permission:media:upload');
            Route::get('/', [PodcastController::class, 'index'])->middleware('permission:media:read');
            Route::get('{podcast}/snippets', [PodcastController::class, 'snippets'])->middleware('permission:media:read');
        });

        // Lives
        Route::prefix('lives')->group(function () {
            Route::post('start', [LiveController::class, 'start'])->middleware('permission:media:upload');
            Route::post('{live}/end', [LiveController::class, 'end'])->middleware('permission:media:upload');
            Route::get('{live}/recording', [LiveController::class, 'recording'])->middleware('permission:media:read');
        });

        // ============================================================================
        // NOTIFICATIONS
        // ============================================================================

        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('{id}', [NotificationController::class, 'show']);
            Route::post('{id}/read', [NotificationController::class, 'markAsRead']);
            Route::post('mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('{id}', [NotificationController::class, 'destroy']);
            Route::get('unread/count', [NotificationController::class, 'unreadCount']);
            Route::post('cleanup', [NotificationController::class, 'cleanup']);
            Route::post('test', [NotificationController::class, 'createTest']); // Dev only
        });

        // ============================================================================
        // ÉQUIPE ET INVITATIONS
        // ============================================================================

        Route::prefix('team')->group(function () {
            Route::get('members', [TeamController::class, 'members'])->middleware('permission:team:manage');
            Route::put('members/{user}/role', [TeamController::class, 'updateRole'])->middleware('permission:team:manage');
            Route::delete('members/{user}', [TeamController::class, 'removeMember'])->middleware('permission:team:manage');
        });

        Route::prefix('invitations')->group(function () {
            Route::post('/', [TeamInvitationController::class, 'create'])->middleware('permission:team:manage');
            Route::get('{token}', [TeamInvitationController::class, 'validateToken']);
            Route::post('{token}/accept', [TeamInvitationController::class, 'accept']);
        });

        // ============================================================================
        // PLANIFICATION ET PUBLICATION
        // ============================================================================

        Route::prefix('schedules')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->middleware('permission:articles:publish');
            Route::post('articles/{article}', [ScheduleController::class, 'store'])->middleware('permission:articles:publish');
            Route::put('{schedule}', [ScheduleController::class, 'update'])->middleware('permission:articles:publish');
            Route::delete('{schedule}', [ScheduleController::class, 'destroy'])->middleware('permission:articles:publish');
        });

        // ============================================================================
        // ANALYTIQUE ET IA
        // ============================================================================

        Route::prefix('analytics')->group(function () {
            Route::get('dashboard', [AnalyticsController::class, 'dashboard'])->middleware('permission:analytics:read');
            Route::post('events', [AnalyticsController::class, 'store'])->middleware('permission:analytics:write');
        });

        Route::prefix('ai')->group(function () {
            Route::post('optimize-title', [AiController::class, 'optimizeTitle'])->middleware('permission:articles:write');
            Route::post('adapt-audience', [AiController::class, 'adaptAudience'])->middleware('permission:articles:write');
            Route::post('generate-content', [AiController::class, 'generateContent'])->middleware('permission:articles:write');
            Route::post('correct-style', [AiController::class, 'correctStyle'])->middleware('permission:articles:write');
            Route::post('seo-suggestions', [AiController::class, 'seoSuggestions'])->middleware('permission:articles:write');
        });

        // ============================================================================
        // DOSSIERS
        // ============================================================================

        Route::prefix('folders')->group(function () {
            // Routes spéciales AVANT les routes avec paramètres
            Route::get('trashed', [FolderController::class, 'trashed']);
            Route::get('hierarchy', [FolderController::class, 'hierarchy']);
            Route::get('most-active', [FolderController::class, 'mostActive']);

            // CRUD Folders
            Route::get('/', [FolderController::class, 'index']);
            Route::post('/', [FolderController::class, 'store']);
            Route::get('{folder}', [FolderController::class, 'show']);
            Route::put('{folder}', [FolderController::class, 'update']);
            Route::delete('{folder}', [FolderController::class, 'destroy']);

            // Actions spécifiques
            Route::get('{folder}/stats', [FolderController::class, 'stats']);

            // Gestion de la corbeille
            Route::post('{folder}/restore', [FolderController::class, 'restore']);
            Route::delete('{folder}/force', [FolderController::class, 'forceDelete']);
        });

        // ============================================================================
        // AUDIT ET CONFORMITÉ
        // ============================================================================

        Route::prefix('audit')->group(function () {
            Route::get('logs', [AuditLogController::class, 'index'])->middleware('permission:audit:read');
            Route::get('logs/{log}', [AuditLogController::class, 'show'])->middleware('permission:audit:read');
            Route::get('logs/export', [AuditLogController::class, 'export'])->middleware('permission:audit:read');
        });
    });
});
