<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;

echo "=== UTILISATEURS EXISTANTS ===\n\n";

$users = User::with('profile')->get();

foreach ($users as $user) {
    echo "Email: " . $user->email . "\n";
    echo "Role: " . ($user->profile->role ?? 'N/A') . "\n";
    echo "Nom: " . $user->name . "\n";
    echo "---\n";
}

echo "\nTotal: " . $users->count() . " utilisateurs\n";

