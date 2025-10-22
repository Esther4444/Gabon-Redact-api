<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Créer une notification
     */
    public function create(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'action_url' => $actionUrl,
            'metadata' => $metadata,
            'related_id' => $relatedId,
            'related_type' => $relatedType,
        ]);
    }

    /**
     * Notifier un utilisateur
     */
    public function notifyUser(
        User $user,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): Notification {
        return $this->create(
            $user->id,
            $type,
            $title,
            $message,
            $actionUrl,
            $metadata,
            $relatedId,
            $relatedType
        );
    }

    /**
     * Notifier plusieurs utilisateurs
     */
    public function notifyUsers(
        array $userIds,
        string $type,
        string $title,
        string $message,
        ?string $actionUrl = null,
        ?array $metadata = null,
        ?int $relatedId = null,
        ?string $relatedType = null
    ): Collection {
        $notifications = collect();

        foreach ($userIds as $userId) {
            $notifications->push($this->create(
                $userId,
                $type,
                $title,
                $message,
                $actionUrl,
                $metadata,
                $relatedId,
                $relatedType
            ));
        }

        return $notifications;
    }

    /**
     * Obtenir les notifications d'un utilisateur
     */
    public function getUserNotifications(
        int $userId,
        int $limit = 20,
        bool $unreadOnly = false
    ): Collection {
        $query = Notification::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit);

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->get();
    }

    /**
     * Compter les notifications non lues
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::forUser($userId)->unread()->count();
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::forUser($userId)->find($notificationId);

        if ($notification) {
            $notification->markAsRead();
            return true;
        }

        return false;
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::forUser($userId)
            ->unread()
            ->update([
                'read' => true,
                'read_at' => now()
            ]);
    }

    /**
     * Supprimer les anciennes notifications (plus de 30 jours)
     */
    public function cleanupOldNotifications(): int
    {
        return Notification::where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    // ========================================
    // NOTIFICATIONS SPÉCIFIQUES (Types 1-6)
    // ========================================

    /**
     * 1. Article approuvé (success)
     */
    public function notifyArticleApproved(User $user, $article): Notification
    {
        return $this->notifyUser(
            $user,
            'success',
            'Article approuvé',
            "\"{$article->titre}\" a été approuvé par le directeur",
            "/dashboard/articles/{$article->id}",
            ['article_id' => $article->id, 'article_title' => $article->titre],
            $article->id,
            'article'
        );
    }

    /**
     * 2. Article publié (success)
     */
    public function notifyArticlePublished(User $user, $article): Notification
    {
        return $this->notifyUser(
            $user,
            'success',
            'Article publié',
            "\"{$article->titre}\" a été publié avec succès",
            "/dashboard/articles/{$article->id}",
            ['article_id' => $article->id, 'article_title' => $article->titre, 'published_at' => now()],
            $article->id,
            'article'
        );
    }

    /**
     * 3. Article sauvegardé (success)
     */
    public function notifyArticleSaved(User $user, $article): Notification
    {
        return $this->notifyUser(
            $user,
            'success',
            'Article sauvegardé',
            "\"{$article->titre}\" a été sauvegardé automatiquement",
            "/dashboard/articles/{$article->id}",
            ['article_id' => $article->id, 'article_title' => $article->titre, 'saved_at' => now()],
            $article->id,
            'article'
        );
    }

    /**
     * 4. Correction demandée (warning)
     */
    public function notifyCorrectionRequested(User $user, $article, ?User $reviewer = null): Notification
    {
        $reviewerName = $reviewer ? $reviewer->name : 'Un relecteur';

        return $this->notifyUser(
            $user,
            'warning',
            'Correction demandée',
            "\"{$article->titre}\" nécessite des ajustements de la part de {$reviewerName}",
            "/dashboard/articles/{$article->id}",
            [
                'article_id' => $article->id,
                'article_title' => $article->titre,
                'reviewer_id' => $reviewer?->id,
                'reviewer_name' => $reviewerName,
                'requested_at' => now()
            ],
            $article->id,
            'article'
        );
    }

    /**
     * 5. Deadline approche (warning)
     */
    public function notifyDeadlineApproaching(User $user, $article, int $hoursLeft = 24): Notification
    {
        return $this->notifyUser(
            $user,
            'warning',
            'Deadline approche',
            "\"{$article->titre}\" doit être terminé dans {$hoursLeft}h",
            "/dashboard/articles/{$article->id}",
            [
                'article_id' => $article->id,
                'article_title' => $article->titre,
                'hours_left' => $hoursLeft,
                'deadline' => $article->deadline ?? null
            ],
            $article->id,
            'article'
        );
    }

    /**
     * 6. Article soumis (info)
     */
    public function notifyArticleSubmitted(User $user, $article): Notification
    {
        return $this->notifyUser(
            $user,
            'info',
            'Article soumis',
            "\"{$article->titre}\" a été soumis pour relecture",
            "/dashboard/articles/{$article->id}",
            ['article_id' => $article->id, 'article_title' => $article->titre, 'submitted_at' => now()],
            $article->id,
            'article'
        );
    }
}



