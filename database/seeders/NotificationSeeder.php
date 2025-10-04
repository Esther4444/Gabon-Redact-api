<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Models\Article;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $articles = Article::all();

        $notificationTypes = [
            'article_assigned',
            'article_approved',
            'article_published',
            'comment_received',
            'deadline_reminder',
            'system_update'
        ];

        $notificationTemplates = [
            'article_assigned' => 'Un nouvel article vous a été assigné : {title}',
            'article_approved' => 'Votre article "{title}" a été approuvé et est prêt pour publication.',
            'article_published' => 'Votre article "{title}" a été publié avec succès.',
            'comment_received' => 'Un nouveau commentaire a été ajouté à votre article "{title}".',
            'deadline_reminder' => 'Rappel : L\'échéance de votre article "{title}" approche.',
            'system_update' => 'Mise à jour du système : Nouvelles fonctionnalités disponibles.'
        ];

        foreach ($users as $user) {
            // Créer 5-10 notifications par utilisateur
            $notificationCount = rand(5, 10);

            for ($i = 0; $i < $notificationCount; $i++) {
                $type = $notificationTypes[array_rand($notificationTypes)];
                $article = $articles->random();
                $template = $notificationTemplates[$type];
                $message = str_replace('{title}', $article->title, $template);

                Notification::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'message' => $message,
                    'read' => rand(0, 1),
                    'data' => json_encode([
                        'article_id' => $article->id,
                        'article_title' => $article->title,
                        'timestamp' => now()->toISOString()
                    ]),
                ]);
            }
        }
    }
}
