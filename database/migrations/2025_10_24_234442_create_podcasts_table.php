<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('podcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Informations de base
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['draft', 'processing', 'published', 'archived'])->default('draft');

            // Fichier audio
            $table->string('audio_url')->nullable();
            $table->string('audio_path')->nullable(); // Chemin dans storage
            $table->integer('duree_secondes')->nullable();
            $table->bigInteger('taille_fichier')->nullable(); // Taille en octets
            $table->string('format_audio')->nullable(); // mp3, wav, etc.

            // Statistiques
            $table->integer('nombre_telecharges')->default(0);
            $table->integer('nombre_vues')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('partages')->default(0);

            // Publication
            $table->timestamp('publie_le')->nullable();
            $table->string('image_couverture')->nullable();

            // RSS Feed
            $table->string('rss_feed_url')->nullable();
            $table->string('spotify_episode_id')->nullable();
            $table->string('apple_podcast_id')->nullable();

            // Catégorisation
            $table->string('categorie')->nullable();
            $table->json('tags')->nullable();

            // Métadonnées
            $table->json('metadonnees')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id');
            $table->index('statut');
            $table->index('publie_le');
            $table->index('categorie');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('podcasts');
    }
};
