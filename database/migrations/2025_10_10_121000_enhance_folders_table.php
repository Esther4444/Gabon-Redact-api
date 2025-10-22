<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Améliorer la table dossiers avec hiérarchie et personnalisation
     */
    public function up(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Ajouter d'abord la description si elle n'existe pas
            $table->text('description')->nullable()->after('nom');
            $table->string('color')->default('#3b82f6')->after('description');
            $table->string('icon')->default('folder')->after('color');
            $table->foreignId('parent_id')->nullable()->constrained('dossiers')->nullOnDelete()->after('icon');
            $table->integer('sort_order')->default(0)->after('parent_id');
            $table->boolean('is_active')->default(true)->after('sort_order');

            // Index pour les performances
            $table->index(['parent_id']);
            $table->index(['sort_order']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            // Supprimer d'abord la contrainte de clé étrangère
            $table->dropForeign(['parent_id']);

            // Ensuite supprimer les index
            $table->dropIndex(['parent_id']);
            $table->dropIndex(['sort_order']);
            $table->dropIndex(['is_active']);

            // Enfin supprimer les colonnes
            $table->dropColumn([
                'description',
                'color',
                'icon',
                'parent_id',
                'sort_order',
                'is_active'
            ]);
        });
    }
};
