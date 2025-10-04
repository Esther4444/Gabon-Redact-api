<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('publication_schedules', function (Blueprint $table) {
			$table->id();
			$table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
			$table->timestamp('scheduled_for')->index();
			$table->string('channel')->nullable();
			$table->string('status')->default('pending')->index();
			$table->text('failure_reason')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('publication_schedules');
	}
};


