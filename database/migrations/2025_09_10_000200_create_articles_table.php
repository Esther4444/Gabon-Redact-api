<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('articles', function (Blueprint $table) {
			$table->id();
			$table->string('title');
			$table->string('slug')->unique();
			$table->longText('content')->nullable();
			$table->string('status')->index();
			$table->foreignId('folder_id')->nullable()->constrained('folders')->nullOnDelete();
			$table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
			$table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
			$table->string('seo_title')->nullable();
			$table->text('seo_description')->nullable();
			$table->json('seo_keywords')->nullable();
			$table->timestamp('published_at')->nullable()->index();
			$table->json('metadata')->nullable();
			$table->timestamps();
			$table->softDeletes();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('articles');
	}
};


