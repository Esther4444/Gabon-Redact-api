<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Ajout de la colonne 'read' à la table notifications...\n";

try {
    if (!Schema::hasColumn('notifications', 'read')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('read')->default(false)->after('message');
        });
        echo "[OK] Colonne 'read' ajoutée avec succès\n";
    } else {
        echo "[INFO] La colonne 'read' existe déjà\n";
    }

    // Marquer la migration comme exécutée
    DB::table('migrations')->insert([
        'migration' => '2024_01_15_000000_create_notifications_table',
        'batch' => DB::table('migrations')->max('batch') + 1
    ]);
    echo "[OK] Migration marquée comme exécutée\n";

} catch (Exception $e) {
    echo "[ERREUR] " . $e->getMessage() . "\n";
}

