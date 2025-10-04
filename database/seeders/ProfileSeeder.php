<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Profile;
use App\Models\User;

class ProfileSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();

        // Rôles spécifiques pour les utilisateurs principaux
        $specificRoles = [
            'directeur@redacgabon.com' => 'directeur_publication',
            'secretaire@redacgabon.com' => 'secretaire_redaction',
            'journaliste@redacgabon.com' => 'journaliste',
            'journaliste2@redacgabon.com' => 'journaliste',
            'journaliste3@redacgabon.com' => 'journaliste',
        ];

        foreach ($users as $user) {
            // Assigner le rôle spécifique ou journaliste par défaut
            $role = $specificRoles[$user->email] ?? 'journaliste';

            Profile::create([
                'user_id' => $user->id,
                'full_name' => $user->name,
                'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&background=random',
                'role' => $role,
                'preferences' => json_encode([
                    'theme' => 'light',
                    'language' => 'fr',
                    'notifications' => true,
                    'email_digest' => 'daily'
                ]),
            ]);
        }
    }
}
