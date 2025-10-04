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
        Schema::create('article_workflow', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('from_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('to_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('action'); // 'submitted', 'reviewed', 'approved', 'rejected', 'published'
            $table->string('status'); // 'pending', 'completed', 'rejected'
            $table->text('comment')->nullable();
            $table->timestamp('action_at')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'status']);
            $table->index(['to_user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('article_workflow');
    }
};
