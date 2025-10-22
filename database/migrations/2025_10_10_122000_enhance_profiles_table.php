<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Améliorer la table profils avec informations supplémentaires
     */
    public function up(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            // Note: 'avatar_url' a été renommé 'url_avatar' dans la migration de francisation
            $table->text('bio')->nullable()->after('url_avatar');
            $table->json('social_links')->nullable()->after('bio');
            $table->text('signature')->nullable()->after('social_links');
            $table->string('phone')->nullable()->after('signature');
            $table->string('department')->nullable()->after('phone');
            $table->string('specialization')->nullable()->after('department');
            $table->string('timezone')->default('Africa/Libreville')->after('specialization');

            // Index pour les recherches
            $table->index(['department']);
            $table->index(['specialization']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profils', function (Blueprint $table) {
            $table->dropIndex(['department']);
            $table->dropIndex(['specialization']);

            $table->dropColumn([
                'bio',
                'social_links',
                'signature',
                'phone',
                'department',
                'specialization',
                'timezone'
            ]);
        });
    }
};
