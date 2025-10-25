<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== CRÉATION DES UTILISATEURS DE TEST ===\n\n";

// Supprimer tous les users existants
User::query()->delete();

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
];

foreach ($users as $userData) {
    $role = $userData['role'];
    $matricule = $userData['matricule'];
    unset($userData['role'], $userData['matricule']);

    $user = User::create($userData);

    $user->profile()->create([
        'full_name' => $user->name,
        'nom_complet' => $user->name,
        'matricule' => $matricule,
        'avatar_url' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=random",
        'url_avatar' => "https://ui-avatars.com/api/?name=" . urlencode($user->name) . "&background=random",
        'role' => $role,
        'preferences' => json_encode([
            'theme' => 'light',
            'language' => 'fr',
            'notifications' => true,
            'email_digest' => 'daily'
        ]),
    ]);

    echo "[OK] Utilisateur créé : {$user->name} ({$role})\n";
}

echo "\n[SUCCESS] Tous les utilisateurs de test ont été créés !\n";

