<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

echo "=== CORRECTION DE LA TABLE NOTIFICATIONS ===\n\n";

try {
    // Vérifier et ajouter 'title' si manquant
    if (!Schema::hasColumn('notifications', 'title')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->after('type');
        });
        echo "[OK] Colonne 'title' ajoutée\n";
    } else {
        echo "[INFO] Colonne 'title' existe déjà\n";
    }

    // Vérifier et ajouter 'read' si manquant
    if (!Schema::hasColumn('notifications', 'read')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->boolean('read')->default(false)->after('message');
        });
        echo "[OK] Colonne 'read' ajoutée\n";
    } else {
        echo "[INFO] Colonne 'read' existe déjà\n";
    }

    // Vérifier et ajouter 'read_at' si manquant
    if (!Schema::hasColumn('notifications', 'read_at')) {
        Schema::table('notifications', function (Blueprint $table) {
            $table->timestamp('read_at')->nullable()->after('read');
        });
        echo "[OK] Colonne 'read_at' ajoutée\n";
    } else {
        echo "[INFO] Colonne 'read_at' existe déjà\n";
    }

    echo "\n[SUCCESS] Table notifications corrigée avec succès!\n";

} catch (Exception $e) {
    echo "\n[ERREUR] " . $e->getMessage() . "\n";
}

