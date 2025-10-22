<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des utilisateurs avec les rôles spécifiques
        $users = [
            [
                'name' => 'Directeur de Publication',
                'email' => 'directeur@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'directeur_publication',
            ],
            [
                'name' => 'Secrétaire de Rédaction',
                'email' => 'secretaire@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'secretaire_redaction',
            ],
            [
                'name' => 'Journaliste Principal',
                'email' => 'journaliste@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
            ],
            [
                'name' => 'Journaliste Senior',
                'email' => 'journaliste2@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
            ],
            [
                'name' => 'Journaliste Junior',
                'email' => 'journaliste3@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
            ],
            [
                'name' => 'Social Media Manager',
                'email' => 'socialmedia@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'social_media_manager',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            unset($userData['role']);

            $user = User::create($userData);

            // Créer le profil avec le bon rôle
            $user->profile()->create([
                'nom_complet' => $user->name,
                'url_avatar' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=random",
                'role' => $role,
                'preferences' => json_encode([
                    'theme' => 'light',
                    'language' => 'fr',
                    'notifications' => true,
                    'email_digest' => 'daily'
                ]),
            ]);
        }

        // Utilisateurs Faker désactivés pour éviter la confusion
        // User::factory(5)->create();
    }
}
