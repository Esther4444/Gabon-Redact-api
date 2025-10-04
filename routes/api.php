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
            Route::get('/', [UserController::class, 'index'])->middleware('permission:users:read');
            Route::get('{user}', [UserController::class, 'show'])->middleware('permission:users:read');
            Route::put('{user}', [UserController::class, 'update'])->middleware('permission:users:manage');
            Route::delete('{user}', [UserController::class, 'destroy'])->middleware('permission:users:manage');
        });

        // ============================================================================
        // ARTICLES ET WORKFLOW
        // ============================================================================

        Route::prefix('articles')->group(function () {
            // CRUD Articles
            Route::get('/', [ArticleController::class, 'index'])->middleware('permission:articles:read');
            Route::post('/', [ArticleController::class, 'store'])->middleware('permission:articles:write');
            Route::get('{article}', [ArticleController::class, 'show'])->middleware('permission:articles:read');
            Route::put('{article}', [ArticleController::class, 'update'])->middleware('permission:articles:write');
            Route::delete('{article}', [ArticleController::class, 'destroy'])->middleware('permission:articles:own:delete');

            // Actions spécifiques
            Route::get('{article}/preview', [ArticleController::class, 'preview'])->middleware('permission:articles:read');
            Route::post('{article}/save', [ArticleController::class, 'save'])->middleware('permission:articles:write');
            Route::post('{article}/slug', [ArticleSlugController::class, 'generate'])->middleware('permission:articles:write');
            Route::patch('{article}/status', [ArticleStatusController::class, 'update'])->middleware('permission:articles:write');

            // Workflow
            Route::post('{article}/submit-review', [WorkflowController::class, 'submitForReview'])->middleware('permission:articles:write');
            Route::post('{article}/review', [WorkflowController::class, 'review'])->middleware('permission:articles:review');
            Route::post('{article}/approve', [WorkflowController::class, 'approve'])->middleware('permission:articles:approve');
            Route::post('{article}/reject', [WorkflowController::class, 'reject'])->middleware('permission:articles:approve');
            Route::post('{article}/publish', [WorkflowController::class, 'publish'])->middleware('permission:articles:publish');
            Route::get('{article}/workflow-history', [WorkflowController::class, 'workflowHistory'])->middleware('permission:articles:read');

            // Commentaires
            Route::get('{article}/comments', [CommentController::class, 'index'])->middleware('permission:comments:read');
            Route::post('{article}/comments', [CommentController::class, 'store'])->middleware('permission:comments:write');
        });

        // Workflow global
        Route::prefix('workflow')->group(function () {
            Route::get('pending-articles', [WorkflowController::class, 'pendingArticles'])->middleware('permission:articles:review');
            Route::get('stats', [WorkflowController::class, 'workflowStats'])->middleware('permission:articles:read');
        });

        // ============================================================================
        // MESSAGERIE ET COLLABORATION
        // ============================================================================

        Route::prefix('messages')->group(function () {
            Route::get('/', [MessageController::class, 'index'])->middleware('permission:messages:read');
            Route::post('/', [MessageController::class, 'store'])->middleware('permission:messages:write');
            Route::get('{message}', [MessageController::class, 'show'])->middleware('permission:messages:read');
            Route::delete('{message}', [MessageController::class, 'destroy'])->middleware('permission:messages:write');
            Route::post('{message}/reply', [MessageController::class, 'reply'])->middleware('permission:messages:write');
            Route::patch('{message}/read', [MessageController::class, 'markAsRead'])->middleware('permission:messages:read');
            Route::patch('{message}/unread', [MessageController::class, 'markAsUnread'])->middleware('permission:messages:read');
            Route::get('unread/count', [MessageController::class, 'unread'])->middleware('permission:messages:read');
        });

        Route::get('conversations', [MessageController::class, 'conversations'])->middleware('permission:messages:read');

        // Commentaires globaux
        Route::prefix('comments')->group(function () {
            Route::put('{comment}', [CommentController::class, 'update'])->middleware('permission:comments:write');
            Route::delete('{comment}', [CommentController::class, 'destroy'])->middleware('permission:comments:write');
        });

        // ============================================================================
        // MÉDIAS ET CONTENU MULTIMÉDIA
        // ============================================================================

        Route::prefix('media')->group(function () {
            Route::post('upload', [MediaController::class, 'upload'])->middleware('permission:media:upload');
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
            Route::get('/', [NotificationController::class, 'index'])->middleware('permission:notifications:read');
            Route::post('/', [NotificationController::class, 'store'])->middleware('permission:notifications:write');
            Route::post('workflow', [NotificationController::class, 'sendWorkflowNotification'])->middleware('permission:notifications:write');
            Route::patch('{notification}/read', [NotificationController::class, 'markRead'])->middleware('permission:notifications:read');
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

        Route::apiResource('folders', FolderController::class)->middleware('permission:articles:read');

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
