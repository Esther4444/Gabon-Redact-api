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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('login', [AuthController::class, 'login']);
Route::get('auth/users', [AuthController::class, 'availableUsers']);

// Route publique pour la prévisualisation des articles
Route::get('articles/preview/{slug}', [ArticleController::class, 'publicPreview']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('user/profile', [UserController::class, 'profile']);
    Route::put('user/profile', [UserController::class, 'updateProfile']);

    Route::apiResource('articles', ArticleController::class);
    Route::get('articles/{article}/preview', [ArticleController::class, 'preview']);
    Route::patch('articles/{id}/status', [ArticleStatusController::class, 'update']);
    Route::post('articles/{id}/publish', [ArticlePublishController::class, 'publish']);
    Route::post('articles/{id}/slug', [ArticleSlugController::class, 'generate']);
    Route::post('articles/{id}/save', [ArticleController::class, 'save']);

    // Routes du workflow
    Route::post('articles/{article}/submit-review', [WorkflowController::class, 'submitForReview']);
    Route::post('articles/{article}/review', [WorkflowController::class, 'review']);
    Route::post('articles/{article}/approve', [WorkflowController::class, 'approve']);
    Route::post('articles/{article}/reject', [WorkflowController::class, 'reject']);
    Route::post('articles/{article}/publish', [WorkflowController::class, 'publish']);
    Route::get('workflow/pending-articles', [WorkflowController::class, 'pendingArticles']);
    Route::get('articles/{article}/workflow-history', [WorkflowController::class, 'workflowHistory']);
    Route::get('workflow/stats', [WorkflowController::class, 'workflowStats']);

    // Routes de messagerie
    Route::apiResource('messages', MessageController::class)->except(['update']);
    Route::post('messages/{message}/reply', [MessageController::class, 'reply']);
    Route::patch('messages/{message}/read', [MessageController::class, 'markAsRead']);
    Route::patch('messages/{message}/unread', [MessageController::class, 'markAsUnread']);
    Route::get('messages/unread/count', [MessageController::class, 'unread']);
    Route::get('conversations', [MessageController::class, 'conversations']);

    Route::apiResource('folders', FolderController::class);

    Route::get('articles/{id}/comments', [CommentController::class, 'index']);
    Route::post('articles/{id}/comments', [CommentController::class, 'store']);
    Route::put('comments/{id}', [CommentController::class, 'update']);
    Route::delete('comments/{id}', [CommentController::class, 'destroy']);

    Route::post('media/upload', [MediaController::class, 'upload']);
    Route::get('media', [MediaController::class, 'index']);
    Route::delete('media/{id}', [MediaController::class, 'destroy']);

    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('notifications', [NotificationController::class, 'store']);
    Route::post('notifications/workflow', [NotificationController::class, 'sendWorkflowNotification']);
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead']);

    Route::get('team/members', [TeamController::class, 'members']);
    Route::put('team/members/{user_id}/role', [TeamController::class, 'updateRole']);
    Route::delete('team/members/{user_id}', [TeamController::class, 'removeMember']);
    Route::post('team/invitations', [TeamInvitationController::class, 'create']);
    Route::get('team/invitations/{token}', [TeamInvitationController::class, 'validateToken']);
    Route::post('team/invitations/{token}/accept', [TeamInvitationController::class, 'accept']);

    Route::get('publication-schedules', [ScheduleController::class, 'index']);
    Route::post('articles/{id}/schedule', [ScheduleController::class, 'store']);
    Route::put('publication-schedules/{schedule_id}', [ScheduleController::class, 'update']);
    Route::delete('publication-schedules/{schedule_id}', [ScheduleController::class, 'destroy']);

    Route::post('analytics/events', [AnalyticsController::class, 'store']);
    Route::get('analytics/dashboard', [AnalyticsController::class, 'dashboard']);

    Route::post('ai/optimize-title', [AiController::class, 'optimizeTitle']);
    Route::post('ai/adapt-audience', [AiController::class, 'adaptAudience']);
    Route::post('ai/generate-content', [AiController::class, 'generateContent']);
    Route::post('ai/correct-style', [AiController::class, 'correctStyle']);
    Route::post('ai/seo-suggestions', [AiController::class, 'seoSuggestions']);

    Route::post('lives/start', [LiveController::class, 'start']);
    Route::post('lives/{live_id}/end', [LiveController::class, 'end']);
    Route::get('lives/{live_id}/recording', [LiveController::class, 'recording']);

    Route::post('podcasts/upload', [PodcastController::class, 'upload']);
    Route::post('media/{media_id}/transcribe', [TranscriptionController::class, 'transcribe']);
    Route::get('podcasts/{podcast_id}/snippets', [PodcastController::class, 'snippets']);

    Route::get('audit-logs', [AuditLogController::class, 'index'])->middleware('can:viewAuditLogs');
});
