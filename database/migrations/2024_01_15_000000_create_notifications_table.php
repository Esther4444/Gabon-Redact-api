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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // success, warning, info, error
            $table->string('title');
            $table->text('message');
            $table->boolean('read')->default(false);
            $table->string('action_url')->nullable();
            $table->json('metadata')->nullable(); // Données supplémentaires
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('related_id')->nullable(); // ID de l'article, commentaire, etc.
            $table->string('related_type')->nullable(); // 'article', 'comment', 'workflow', etc.
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            // Index pour les performances
            $table->index(['user_id', 'read']);
            $table->index(['user_id', 'created_at']);
            $table->index(['type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};



