<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Create a test user for API testing';

    public function handle()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'role' => 'journaliste'
        ]);

        $this->info("Utilisateur de test créé avec l'ID: {$user->id}");
        $this->info("Email: test@test.com");
        $this->info("Mot de passe: password");

        return 0;
    }
}
