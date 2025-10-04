<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Media;
use App\Models\Article;
use App\Models\User;

class MediaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $articles = Article::all();
        $users = User::all();

        $mediaTypes = ['image', 'video', 'document', 'audio'];
        $mediaUrls = [
            'https://picsum.photos/800/600?random=1',
            'https://picsum.photos/800/600?random=2',
            'https://picsum.photos/800/600?random=3',
            'https://picsum.photos/800/600?random=4',
            'https://picsum.photos/800/600?random=5',
        ];

        foreach ($articles as $article) {
            // Créer 1-3 médias par article
            $mediaCount = rand(1, 3);

            for ($i = 0; $i < $mediaCount; $i++) {
                $mediaType = $mediaTypes[array_rand($mediaTypes)];
                $mediaUrl = $mediaUrls[array_rand($mediaUrls)];

                Media::create([
                    'user_id' => $users->random()->id,
                    'disk' => 'public',
                    'path' => $mediaUrl,
                    'mime_type' => 'image/jpeg',
                    'size_bytes' => rand(100000, 5000000), // 100KB à 5MB
                    'meta' => json_encode([
                        'article_id' => $article->id,
                        'media_type' => $mediaType,
                        'alt_text' => 'Image illustrative pour l\'article : ' . $article->title,
                        'width' => 800,
                        'height' => 600,
                        'format' => 'JPEG',
                        'quality' => 'high'
                    ]),
                ]);
            }
        }
    }
}
