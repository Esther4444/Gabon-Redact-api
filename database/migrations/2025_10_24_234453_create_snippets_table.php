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
        Schema::create('snippets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('podcast_id')->constrained('podcasts')->onDelete('cascade');

            // Informations de base
            $table->string('titre');
            $table->text('description')->nullable();

            // Timing dans le podcast original
            $table->integer('start_time')->nullable(); // En secondes
            $table->integer('end_time')->nullable(); // En secondes
            $table->integer('duree_secondes')->nullable();

            // Fichier vidéo généré
            $table->string('video_url')->nullable();
            $table->string('video_path')->nullable();
            $table->bigInteger('taille_fichier')->nullable();
            $table->string('thumbnail_url')->nullable();

            // Statut de génération
            $table->enum('statut', ['pending', 'generating', 'ready', 'failed'])->default('pending');
            $table->text('message_erreur')->nullable();

            // Statistiques
            $table->integer('vues')->default(0);
            $table->integer('partages')->default(0);
            $table->integer('likes')->default(0);

            // Génération par IA
            $table->boolean('genere_par_ia')->default(false);
            $table->decimal('score_pertinence', 5, 2)->nullable(); // Score donné par l'IA

            // Métadonnées
            $table->json('metadonnees')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('podcast_id');
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('snippets');
    }
};
