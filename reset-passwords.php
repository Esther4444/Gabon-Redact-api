<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== RESET DES MOTS DE PASSE ===\n\n";

$users = [
    'journaliste@redacgabon.com',
    'secretaire@redacgabon.com',
    'directeur@redacgabon.com',
    'socialmedia@redacgabon.com'
];

foreach ($users as $email) {
    $user = User::where('email', $email)->first();
    if ($user) {
        $user->password = Hash::make('password123');
        $user->save();
        echo "[OK] Mot de passe reinitialise pour: $email (password123)\n";
    } else {
        echo "[ERREUR] Utilisateur non trouve: $email\n";
    }
}

echo "\nTermine!\n";

