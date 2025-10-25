<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$migrationName = '2025_10_22_212355_add_columns_to_dossiers_table';

// Vérifier si déjà marquée
$exists = DB::table('migrations')->where('migration', $migrationName)->exists();

if ($exists) {
    echo "[INFO] Migration déjà marquée comme exécutée.\n";
} else {
    DB::table('migrations')->insert([
        'migration' => $migrationName,
        'batch' => DB::table('migrations')->max('batch') + 1
    ]);
    echo "[OK] Migration marquée comme exécutée : $migrationName\n";
}

