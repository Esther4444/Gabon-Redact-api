<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ajouter les champs manquants utilisés par le frontend
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Catégorie et tags - utilisés dans ArticleBasicForm
            $table->string('category')->nullable()->after('statut');
            $table->json('tags')->nullable()->after('category');

            // Image et contenu
            $table->string('featured_image')->nullable()->after('contenu');
            $table->text('excerpt')->nullable()->after('featured_image');

            // Métriques de contenu - calculées automatiquement
            $table->integer('reading_time')->nullable()->after('excerpt'); // en minutes
            $table->integer('word_count')->nullable()->after('reading_time');
            $table->integer('character_count')->nullable()->after('word_count');

            // Personnalisation
            $table->text('author_bio')->nullable()->after('character_count');
            $table->text('custom_css')->nullable()->after('author_bio');
            $table->text('custom_js')->nullable()->after('custom_css');
            $table->string('template')->default('default')->after('custom_js');

            // Métadonnées
            $table->string('language')->default('fr')->after('template');
            $table->boolean('is_featured')->default(false)->after('language');
            $table->boolean('is_breaking_news')->default(false)->after('is_featured');
            $table->boolean('allow_comments')->default(true)->after('is_breaking_news');

            // Données sociales
            $table->json('social_media_data')->nullable()->after('allow_comments');

            // Index pour les recherches
            $table->index(['category']);
            $table->index(['is_featured']);
            $table->index(['is_breaking_news']);
            $table->index(['language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_featured']);
            $table->dropIndex(['is_breaking_news']);
            $table->dropIndex(['language']);

            $table->dropColumn([
                'category',
                'tags',
                'featured_image',
                'excerpt',
                'reading_time',
                'word_count',
                'character_count',
                'author_bio',
                'custom_css',
                'custom_js',
                'template',
                'language',
                'is_featured',
                'is_breaking_news',
                'allow_comments',
                'social_media_data'
            ]);
        });
    }
};










