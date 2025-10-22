<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Obtenir les notifications de l'utilisateur connecté
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $limit = $request->get('limit', 20);
        $unreadOnly = $request->boolean('unread_only', false);

        $notifications = $this->notificationService->getUserNotifications(
            $user->id,
            $limit,
            $unreadOnly
        );

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'unread_count' => $unreadCount,
            'meta' => [
                'total' => $notifications->count(),
                'unread_only' => $unreadOnly
            ]
        ]);
    }

    /**
     * Obtenir une notification spécifique
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $notification = Notification::forUser($user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $notification
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        $success = $this->notificationService->markAsRead($id, $user->id);

        if (!$success) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Notification marquée comme lue',
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();
        $updated = $this->notificationService->markAllAsRead($user->id);

        return response()->json([
            'success' => true,
            'message' => "{$updated} notifications marquées comme lues",
            'updated_count' => $updated
        ]);
    }

    /**
     * Supprimer une notification
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $notification = Notification::forUser($user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notification non trouvée'
            ], 404);
        }

        $notification->delete();
        $unreadCount = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'message' => 'Notification supprimée',
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function unreadCount(): JsonResponse
    {
        $user = Auth::user();
        $count = $this->notificationService->getUnreadCount($user->id);

        return response()->json([
            'success' => true,
            'unread_count' => $count
        ]);
    }

    /**
     * Nettoyer les anciennes notifications (admin)
     */
    public function cleanup(): JsonResponse
    {
        $user = Auth::user();

        // Vérifier que l'utilisateur est admin
        if (!$user->hasRole('admin') && !$user->hasRole('directeur')) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        $deleted = $this->notificationService->cleanupOldNotifications();

        return response()->json([
            'success' => true,
            'message' => "{$deleted} anciennes notifications supprimées",
            'deleted_count' => $deleted
        ]);
    }

    /**
     * Créer une notification de test (dev seulement)
     */
    public function createTest(Request $request): JsonResponse
    {
        // Vérifier que c'est un environnement de développement
        if (!app()->environment('local', 'development')) {
            return response()->json([
                'success' => false,
                'message' => 'Fonctionnalité disponible uniquement en développement'
            ], 403);
        }

        $user = Auth::user();
        $type = $request->get('type', 'info');
        $title = $request->get('title', 'Notification de test');
        $message = $request->get('message', 'Ceci est une notification de test');

        $notification = $this->notificationService->create(
            $user->id,
            $type,
            $title,
            $message,
            '/dashboard',
            ['test' => true]
        );

        return response()->json([
            'success' => true,
            'message' => 'Notification de test créée',
            'data' => $notification
        ]);
    }
}
