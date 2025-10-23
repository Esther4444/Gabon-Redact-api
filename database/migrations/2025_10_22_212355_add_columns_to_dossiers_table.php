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
        Schema::table('dossiers', function (Blueprint $table) {
            $table->text('description')->nullable()->after('nom');
            $table->string('couleur', 7)->nullable()->after('description');
            $table->string('icone', 50)->nullable()->after('couleur');
            $table->unsignedBigInteger('parent_id')->nullable()->after('icone');
            $table->integer('sort_order')->default(0)->after('parent_id');
            $table->boolean('is_active')->default(true)->after('sort_order');

            $table->foreign('parent_id')->references('id')->on('dossiers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dossiers', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['description', 'couleur', 'icone', 'parent_id', 'sort_order', 'is_active']);
        });
    }
};
