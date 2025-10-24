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
                'name' => 'OBAME NGUEMA Jean-Pierre',
                'email' => 'directeur@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'directeur_publication',
                'matricule' => 'DIR001',
            ],
            [
                'name' => 'NTOUTOUME Marie-Claire',
                'email' => 'secretaire@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'secretaire_redaction',
                'matricule' => 'SEC001',
            ],
            [
                'name' => 'MALEMBA Esther',
                'email' => 'journaliste@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
                'matricule' => 'MALE1A',
            ],
            [
                'name' => 'MOUSSAVOU Patrick',
                'email' => 'journaliste2@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
                'matricule' => 'MOUS2B',
            ],
            [
                'name' => 'ONDO MBA Sylvie',
                'email' => 'journaliste3@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'journaliste',
                'matricule' => 'ONDO3C',
            ],
            [
                'name' => 'EKOMY David',
                'email' => 'socialmedia@redacgabon.com',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'role' => 'social_media_manager',
                'matricule' => 'SOC001',
            ],
        ];

        foreach ($users as $userData) {
            $role = $userData['role'];
            $matricule = $userData['matricule'];
            unset($userData['role']);
            unset($userData['matricule']);

            $user = User::create($userData);

            // Créer le profil avec le bon rôle et matricule
            $user->profile()->create([
                'nom_complet' => $user->name,
                'matricule' => $matricule,
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
