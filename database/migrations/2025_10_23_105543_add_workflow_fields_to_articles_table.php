<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Ajouter le champ du réviseur assigné
            if (!Schema::hasColumn('articles', 'current_reviewer_id')) {
                $table->foreignId('current_reviewer_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete()
                    ->after('assigned_to');
            }

            // Ajouter la raison du rejet
            if (!Schema::hasColumn('articles', 'rejected_reason')) {
                $table->text('rejected_reason')
                    ->nullable()
                    ->after('raison_rejet');
            }

            // Ajouter la date de soumission
            if (!Schema::hasColumn('articles', 'submitted_at')) {
                $table->timestamp('submitted_at')
                    ->nullable()
                    ->after('publie_le');
            }

            // Ajouter la date de révision
            if (!Schema::hasColumn('articles', 'reviewed_at')) {
                $table->timestamp('reviewed_at')
                    ->nullable()
                    ->after('submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['current_reviewer_id']);
            $table->dropColumnIfExists('current_reviewer_id');
            $table->dropColumnIfExists('rejected_reason');
            $table->dropColumnIfExists('submitted_at');
            $table->dropColumnIfExists('reviewed_at');
        });
    }
};
