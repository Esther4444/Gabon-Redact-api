<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Article;
use App\Models\User;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = Article::all();
        $users = User::all();

        foreach ($articles as $article) {
            // Créer 2-5 commentaires par article
            $commentCount = rand(2, 5);

            for ($i = 0; $i < $commentCount; $i++) {
                $commentTemplates = [
                    'Excellent article, très informatif !',
                    'Merci pour cette analyse approfondie.',
                    'Je ne suis pas d\'accord avec certains points, mais l\'article est bien structuré.',
                    'Très bon travail de rédaction.',
                    'Cet article apporte une perspective intéressante sur le sujet.',
                    'J\'aimerais en savoir plus sur ce point spécifique.',
                    'Bravo pour la qualité de l\'écriture.',
                    'Article très pertinent pour comprendre la situation actuelle.'
                ];

                Comment::create([
                    'article_id' => $article->id,
                    'author_id' => $users->random()->id,
                    'body' => $commentTemplates[array_rand($commentTemplates)],
                ]);
            }
        }
    }
}
