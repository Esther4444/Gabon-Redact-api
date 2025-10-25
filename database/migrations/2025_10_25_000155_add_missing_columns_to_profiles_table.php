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
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'nom_complet')) {
                $table->string('nom_complet')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('profiles', 'matricule')) {
                $table->string('matricule', 20)->nullable()->unique()->after('nom_complet');
            }
            if (!Schema::hasColumn('profiles', 'url_avatar')) {
                $table->string('url_avatar')->nullable()->after('matricule');
            }
            if (!Schema::hasColumn('profiles', 'role')) {
                $table->string('role')->nullable()->after('url_avatar');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('profiles', function (Blueprint $table) {
            $columns = ['nom_complet', 'matricule', 'url_avatar', 'role'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
