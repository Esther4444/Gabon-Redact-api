<?php
require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Article;
use App\Models\Folder;

echo "=== CRÉATION D'UN ARTICLE DE TEST ===\n\n";

// Récupérer le journaliste MALEMBA Esther
$journaliste = User::where('email', 'journaliste@redacgabon.com')->first();

if (!$journaliste) {
    echo "[ERREUR] Journaliste non trouvé\n";
    exit(1);
}

// Récupérer un dossier
$dossier = Folder::first();

// Créer un article de test
$article = Article::create([
    'titre' => 'Test Article - ' . now()->format('Y-m-d H:i:s'),
    'contenu' => 'Contenu de test pour vérifier l\'affichage des informations auteur',
    'statut' => 'brouillon',
    'slug' => 'test-article-' . time(),
    'created_by' => $journaliste->id,
    'dossier_id' => $dossier ? $dossier->id : null,
]);

echo "[OK] Article créé avec succès!\n";
echo "    ID: {$article->id}\n";
echo "    Titre: {$article->titre}\n";
echo "    Auteur: {$journaliste->name} ({$journaliste->profile->matricule})\n";
echo "    Rôle: {$journaliste->profile->role}\n";
if ($dossier) {
    echo "    Dossier: {$dossier->nom}\n";
}
echo "\n[SUCCESS] Article de test créé!\n";

