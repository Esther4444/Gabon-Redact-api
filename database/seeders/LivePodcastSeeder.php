<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Live;
use App\Models\Podcast;
use App\Models\Transcription;
use App\Models\Snippet;
use App\Models\LivePlatform;

class LivePodcastSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer un journaliste
        $journaliste = User::whereHas('profile', function ($q) {
            $q->where('role', 'journaliste');
        })->first();

        if (!$journaliste) {
            $this->command->warn('Aucun journaliste trouvé. Exécutez d\'abord UserSeeder.');
            return;
        }

        // ============================================================================
        // LIVES
        // ============================================================================

        // Live 1 : Programmé
        $live1 = Live::create([
            'user_id' => $journaliste->id,
            'titre' => 'Débat Présidentiel 2025',
            'description' => 'Grand débat en direct avec les candidats à l\'élection présidentielle',
            'statut' => 'scheduled',
            'date_debut' => now()->addDays(3),
            'platforms' => ['facebook', 'youtube', 'twitter'],
        ]);
        $live1->generateStreamKey();

        // Créer les configurations de plateformes
        foreach ($live1->platforms as $platform) {
            LivePlatform::create([
                'live_id' => $live1->id,
                'platform' => $platform,
                'statut' => 'inactive',
            ]);
        }

        // Live 2 : En direct
        $live2 = Live::create([
            'user_id' => $journaliste->id,
            'titre' => 'Conférence de Presse - Ministre de l\'Économie',
            'description' => 'Point sur les mesures économiques du gouvernement',
            'statut' => 'live',
            'date_debut' => now()->subMinutes(30),
            'viewers_max' => 1520,
            'viewers_total' => 2840,
            'likes' => 342,
            'commentaires' => 128,
            'platforms' => ['facebook', 'youtube'],
        ]);
        $live2->generateStreamKey();

        foreach ($live2->platforms as $platform) {
            LivePlatform::create([
                'live_id' => $live2->id,
                'platform' => $platform,
                'statut' => 'active',
                'viewers_actuels' => rand(500, 800),
                'viewers_max' => rand(800, 1000),
                'connecte_le' => now()->subMinutes(30),
            ]);
        }

        // Live 3 : Terminé avec enregistrement
        $live3 = Live::create([
            'user_id' => $journaliste->id,
            'titre' => 'Reportage : La Forêt Gabonaise',
            'description' => 'Exploration des richesses naturelles du Gabon',
            'statut' => 'ended',
            'date_debut' => now()->subDays(2),
            'date_fin' => now()->subDays(2)->addHours(1),
            'duree_secondes' => 3600,
            'viewers_max' => 3450,
            'viewers_total' => 5820,
            'likes' => 892,
            'commentaires' => 234,
            'recording_url' => '/storage/uploads/lives/foret-gabonaise.mp4',
            'recording_size' => 1024 * 1024 * 500, // 500 MB
            'thumbnail_url' => '/storage/uploads/lives/foret-gabonaise-thumb.jpg',
            'platforms' => ['facebook', 'youtube'],
        ]);

        foreach ($live3->platforms as $platform) {
            LivePlatform::create([
                'live_id' => $live3->id,
                'platform' => $platform,
                'statut' => 'inactive',
                'viewers_max' => rand(1500, 2000),
                'connecte_le' => $live3->date_debut,
                'deconnecte_le' => $live3->date_fin,
            ]);
        }

        // Créer une transcription pour live3
        Transcription::create([
            'transcribable_type' => Live::class,
            'transcribable_id' => $live3->id,
            'statut' => 'completed',
            'texte_complet' => 'Bienvenue dans ce reportage sur la forêt gabonaise. Aujourd\'hui, nous explorons les richesses naturelles du Gabon...',
            'segments' => [
                ['start' => 0, 'end' => 15, 'text' => 'Bienvenue dans ce reportage sur la forêt gabonaise.'],
                ['start' => 15, 'end' => 35, 'text' => 'Aujourd\'hui, nous explorons les richesses naturelles du Gabon.'],
                ['start' => 35, 'end' => 60, 'text' => 'La biodiversité de cette région est exceptionnelle.'],
            ],
            'service_utilise' => 'whisper',
            'confidence_score' => 94.5,
            'langue' => 'fr',
            'duree_traitement' => 180,
        ]);

        // ============================================================================
        // PODCASTS
        // ============================================================================

        // Podcast 1 : Publié
        $podcast1 = Podcast::create([
            'user_id' => $journaliste->id,
            'titre' => 'L\'Économie Gabonaise en 2025',
            'description' => 'Analyse approfondie de l\'économie nationale avec des experts',
            'statut' => 'published',
            'audio_url' => '/storage/uploads/podcasts/economie-gabon-2025.mp3',
            'audio_path' => 'public/uploads/podcasts/economie-gabon-2025.mp3',
            'duree_secondes' => 2745, // 45:45
            'taille_fichier' => 1024 * 1024 * 42, // 42 MB
            'format_audio' => 'mp3',
            'nombre_telecharges' => 1234,
            'nombre_vues' => 3456,
            'likes' => 234,
            'partages' => 89,
            'publie_le' => now()->subDays(7),
            'image_couverture' => '/storage/uploads/podcasts/economie-cover.jpg',
            'categorie' => 'Économie',
            'tags' => ['économie', 'gabon', 'analyse', 'expert'],
        ]);

        // Transcription du podcast 1
        Transcription::create([
            'transcribable_type' => Podcast::class,
            'transcribable_id' => $podcast1->id,
            'statut' => 'completed',
            'texte_complet' => 'Bonjour à tous, bienvenue dans ce nouvel épisode consacré à l\'économie gabonaise...',
            'segments' => [
                ['start' => 0, 'end' => 10, 'text' => 'Bonjour à tous, bienvenue dans ce nouvel épisode.'],
                ['start' => 10, 'end' => 25, 'text' => 'Aujourd\'hui, nous parlons d\'économie.'],
            ],
            'service_utilise' => 'whisper',
            'confidence_score' => 96.2,
            'langue' => 'fr',
            'duree_traitement' => 240,
        ]);

        // Snippets du podcast 1
        Snippet::create([
            'podcast_id' => $podcast1->id,
            'titre' => 'Les défis économiques actuels',
            'description' => 'Extrait sur les défis économiques',
            'start_time' => 300,
            'end_time' => 360,
            'duree_secondes' => 60,
            'video_url' => '/storage/uploads/snippets/economie-snippet-1.mp4',
            'statut' => 'ready',
            'genere_par_ia' => true,
            'score_pertinence' => 92.5,
            'vues' => 450,
            'partages' => 23,
            'likes' => 67,
        ]);

        Snippet::create([
            'podcast_id' => $podcast1->id,
            'titre' => 'Solutions pour la croissance',
            'description' => 'Extrait sur les solutions proposées',
            'start_time' => 1200,
            'end_time' => 1255,
            'duree_secondes' => 55,
            'video_url' => '/storage/uploads/snippets/economie-snippet-2.mp4',
            'statut' => 'ready',
            'genere_par_ia' => true,
            'score_pertinence' => 88.3,
            'vues' => 320,
            'partages' => 15,
            'likes' => 42,
        ]);

        // Podcast 2 : En traitement
        $podcast2 = Podcast::create([
            'user_id' => $journaliste->id,
            'titre' => 'Interview : Ministre de la Culture',
            'description' => 'Discussion sur les politiques culturelles du pays',
            'statut' => 'processing',
            'audio_url' => '/storage/uploads/podcasts/culture-ministre.mp3',
            'audio_path' => 'public/uploads/podcasts/culture-ministre.mp3',
            'duree_secondes' => 1935, // 32:15
            'taille_fichier' => 1024 * 1024 * 30, // 30 MB
            'format_audio' => 'mp3',
            'categorie' => 'Culture',
            'tags' => ['culture', 'interview', 'ministre', 'politique'],
        ]);

        // Transcription en cours
        Transcription::create([
            'transcribable_type' => Podcast::class,
            'transcribable_id' => $podcast2->id,
            'statut' => 'processing',
            'service_utilise' => 'whisper',
            'langue' => 'fr',
        ]);

        // Podcast 3 : Brouillon
        Podcast::create([
            'user_id' => $journaliste->id,
            'titre' => 'Santé Publique au Gabon',
            'description' => 'État des lieux du système de santé gabonais',
            'statut' => 'draft',
            'categorie' => 'Santé',
            'tags' => ['santé', 'publique', 'système', 'gabon'],
        ]);

        $this->command->info('✅ Lives et Podcasts créés avec succès !');
        $this->command->info('   - 3 Lives (scheduled, live, ended)');
        $this->command->info('   - 3 Podcasts (published, processing, draft)');
        $this->command->info('   - 2 Transcriptions complétées');
        $this->command->info('   - 2 Snippets vidéo');
    }
}
