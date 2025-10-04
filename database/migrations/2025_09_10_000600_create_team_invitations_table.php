<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
	public function up(): void
	{
		Schema::create('team_invitations', function (Blueprint $table) {
			$table->id();
			$table->string('email')->index();
			$table->string('role');
			$table->string('token')->unique();
			$table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
			$table->timestamp('expires_at')->nullable();
			$table->timestamp('accepted_at')->nullable();
			$table->timestamps();
		});
	}

	public function down(): void
	{
		Schema::dropIfExists('team_invitations');
	}
};


