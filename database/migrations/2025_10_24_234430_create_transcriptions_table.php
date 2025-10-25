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
        Schema::create('transcriptions', function (Blueprint $table) {
            $table->id();

            // Relation polymorphique (Live ou Podcast)
            $table->morphs('transcribable'); // Crée transcribable_type et transcribable_id

            // Statut de la transcription
            $table->enum('statut', ['queued', 'processing', 'completed', 'failed'])->default('queued');

            // Contenu de la transcription
            $table->longText('texte_complet')->nullable();
            $table->json('segments')->nullable(); // Timestamps + texte par segment

            // Service utilisé
            $table->string('service_utilise')->nullable(); // 'whisper', 'assemblyai', 'google', etc.
            $table->decimal('cout_api', 10, 4)->nullable(); // Coût de l'API

            // Qualité et métadonnées
            $table->decimal('confidence_score', 5, 2)->nullable(); // Score de confiance (0-100)
            $table->string('langue')->default('fr'); // Langue détectée
            $table->integer('duree_traitement')->nullable(); // Durée du traitement en secondes

            // Erreurs
            $table->text('message_erreur')->nullable();

            // Métadonnées supplémentaires
            $table->json('metadonnees')->nullable();

            $table->timestamps();

            // Index
            $table->index('statut');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcriptions');
    }
};
