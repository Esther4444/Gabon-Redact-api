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
        Schema::create('live_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('live_id')->constrained('lives')->onDelete('cascade');

            // Plateforme
            $table->enum('platform', ['facebook', 'youtube', 'twitter', 'twitch', 'instagram'])->default('facebook');

            // Configuration streaming
            $table->string('stream_key')->nullable();
            $table->string('rtmp_url')->nullable();
            $table->string('playback_url')->nullable(); // URL de lecture publique

            // IDs externes
            $table->string('external_id')->nullable(); // ID du live sur la plateforme externe
            $table->string('external_url')->nullable(); // URL du live sur la plateforme

            // Statut
            $table->enum('statut', ['active', 'inactive', 'error'])->default('inactive');
            $table->text('message_erreur')->nullable();

            // Statistiques par plateforme
            $table->integer('viewers_actuels')->default(0);
            $table->integer('viewers_max')->default(0);
            $table->integer('likes')->default(0);
            $table->integer('commentaires')->default(0);
            $table->integer('partages')->default(0);

            // Connexion
            $table->timestamp('connecte_le')->nullable();
            $table->timestamp('deconnecte_le')->nullable();

            // Métadonnées
            $table->json('metadonnees')->nullable();

            $table->timestamps();

            // Index
            $table->index('live_id');
            $table->index('platform');
            $table->index('statut');
            $table->unique(['live_id', 'platform']); // Un live ne peut avoir qu'une config par plateforme
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_platforms');
    }
};
