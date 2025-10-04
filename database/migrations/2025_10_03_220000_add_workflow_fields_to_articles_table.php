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
        Schema::table('articles', function (Blueprint $table) {
            // Nouveaux statuts pour le workflow
            $table->string('workflow_status')->default('draft')->after('status'); // draft, submitted, in_review, approved, rejected, published
            $table->foreignId('current_reviewer_id')->nullable()->constrained('users')->nullOnDelete()->after('assigned_to');
            $table->timestamp('submitted_at')->nullable()->after('published_at');
            $table->timestamp('reviewed_at')->nullable()->after('submitted_at');
            $table->timestamp('approved_at')->nullable()->after('reviewed_at');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->json('workflow_history')->nullable()->after('rejection_reason');

            $table->index(['workflow_status']);
            $table->index(['current_reviewer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropIndex(['workflow_status']);
            $table->dropIndex(['current_reviewer_id']);
            $table->dropColumn([
                'workflow_status',
                'current_reviewer_id',
                'submitted_at',
                'reviewed_at',
                'approved_at',
                'rejection_reason',
                'workflow_history'
            ]);
        });
    }
};
