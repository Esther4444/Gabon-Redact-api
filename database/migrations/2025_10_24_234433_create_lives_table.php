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
        Schema::create('lives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Informations de base
            $table->string('titre');
            $table->text('description')->nullable();
            $table->enum('statut', ['scheduled', 'live', 'ended', 'archived'])->default('scheduled');

            // Dates et durée
            $table->timestamp('date_debut')->nullable();
            $table->timestamp('date_fin')->nullable();
            $table->integer('duree_secondes')->nullable(); // Durée en secondes

            // Statistiques
            $table->integer('viewers_max')->default(0);
            $table->integer('viewers_total')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('commentaires')->default(0);

            // Configuration streaming
            $table->string('rtmp_url')->nullable();
            $table->string('stream_key')->nullable();
            $table->json('platforms')->nullable(); // ['facebook', 'youtube', 'twitter']

            // Enregistrement
            $table->string('recording_url')->nullable();
            $table->bigInteger('recording_size')->nullable(); // Taille en octets
            $table->string('thumbnail_url')->nullable();

            // Métadonnées
            $table->json('metadonnees')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index('user_id');
            $table->index('statut');
            $table->index('date_debut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lives');
    }
};
