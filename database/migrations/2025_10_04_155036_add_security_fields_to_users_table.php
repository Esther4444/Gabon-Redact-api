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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('email_verified_at');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');

            // Index pour les performances
            $table->index(['is_active', 'locked_until']);
            $table->index('last_login_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'locked_until']);
            $table->dropIndex(['last_login_at']);
            $table->dropColumn(['is_active', 'last_login_at', 'failed_login_attempts', 'locked_until']);
        });
    }
};
