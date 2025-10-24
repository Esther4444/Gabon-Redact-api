<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== MISE À JOUR DES PROFILS UTILISATEURS ===\n\n";

$usersData = [
    'directeur@redacgabon.com' => [
        'name' => 'OBAME NGUEMA Jean-Pierre',
        'matricule' => 'DIR001',
        'role' => 'directeur_publication'
    ],
    'secretaire@redacgabon.com' => [
        'name' => 'NTOUTOUME Marie-Claire',
        'matricule' => 'SEC001',
        'role' => 'secretaire_redaction'
    ],
    'journaliste@redacgabon.com' => [
        'name' => 'MALEMBA Esther',
        'matricule' => 'MALE1A',
        'role' => 'journaliste'
    ],
    'journaliste2@redacgabon.com' => [
        'name' => 'MOUSSAVOU Patrick',
        'matricule' => 'MOUS2B',
        'role' => 'journaliste'
    ],
    'journaliste3@redacgabon.com' => [
        'name' => 'ONDO MBA Sylvie',
        'matricule' => 'ONDO3C',
        'role' => 'journaliste'
    ],
    'socialmedia@redacgabon.com' => [
        'name' => 'EKOMY David',
        'matricule' => 'SOC001',
        'role' => 'social_media_manager'
    ]
];

foreach ($usersData as $email => $data) {
    $user = User::where('email', $email)->first();

    if ($user) {
        // Mettre à jour le nom de l'utilisateur
        $user->name = $data['name'];
        $user->save();

        // Mettre à jour ou créer le profil
        if ($user->profile) {
            $user->profile->nom_complet = $data['name'];
            $user->profile->matricule = $data['matricule'];
            $user->profile->role = $data['role'];
            $user->profile->url_avatar = "https://ui-avatars.com/api/?name=" . urlencode($data['name']) . "&background=random";
            $user->profile->save();
            echo "[OK] Profil mis à jour pour: {$data['name']} ({$data['matricule']})\n";
        } else {
            $user->profile()->create([
                'nom_complet' => $data['name'],
                'matricule' => $data['matricule'],
                'role' => $data['role'],
                'url_avatar' => "https://ui-avatars.com/api/?name=" . urlencode($data['name']) . "&background=random",
                'preferences' => json_encode([
                    'theme' => 'light',
                    'language' => 'fr',
                    'notifications' => true,
                    'email_digest' => 'daily'
                ])
            ]);
            echo "[OK] Profil créé pour: {$data['name']} ({$data['matricule']})\n";
        }
    } else {
        echo "[WARN] Utilisateur non trouvé: $email\n";
    }
}

echo "\n[SUCCESS] Tous les profils ont été mis à jour !\n";

