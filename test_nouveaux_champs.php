<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Models\Folder;
use App\Models\User;

echo "🔍 TEST DES NOUVEAUX CHAMPS\n";
echo "============================\n\n";

// Test 1: Vérifier la structure de la table articles
echo "1. Vérification des colonnes de la table 'articles'...\n";
$articleColumns = Schema::getColumnListing('articles');
$nouveauxChamps = ['category', 'tags', 'featured_image', 'excerpt', 'reading_time', 'word_count', 'character_count'];

foreach ($nouveauxChamps as $champ) {
    if (in_array($champ, $articleColumns)) {
        echo "   ✅ Colonne '$champ' existe\n";
    } else {
        echo "   ❌ Colonne '$champ' manquante\n";
    }
}

// Test 2: Vérifier la structure de la table dossiers
echo "\n2. Vérification des colonnes de la table 'dossiers'...\n";
$folderColumns = Schema::getColumnListing('dossiers');
$nouveauxChampsDossiers = ['description', 'color', 'icon', 'parent_id', 'sort_order'];

foreach ($nouveauxChampsDossiers as $champ) {
    if (in_array($champ, $folderColumns)) {
        echo "   ✅ Colonne '$champ' existe\n";
    } else {
        echo "   ❌ Colonne '$champ' manquante\n";
    }
}

// Test 3: Créer un article de test avec les nouveaux champs
echo "\n3. Test de création d'un article avec les nouveaux champs...\n";
try {
    $user = User::first();
    $folder = Folder::first();

    if (!$user || !$folder) {
        echo "   ⚠️  Aucun utilisateur ou dossier trouvé. Veuillez d'abord exécuter les seeders.\n";
    } else {
        $testArticle = Article::create([
            'titre' => 'Article de test avec nouveaux champs',
            'slug' => 'article-test-' . time(),
            'contenu' => '<p>Ceci est un article de test pour valider les nouveaux champs de la base de données. ' .
                        'Il contient suffisamment de texte pour calculer les métriques automatiquement.</p>',
            'statut' => 'brouillon',
            'statut_workflow' => 'draft',
            'dossier_id' => $folder->id,
            'created_by' => $user->id,

            // Nouveaux champs
            'category' => 'Test',
            'tags' => ['test', 'validation', 'nouveaux-champs'],
            'featured_image' => 'https://via.placeholder.com/800x600',
            'excerpt' => 'Ceci est un extrait de test',
            'language' => 'fr',
            'is_featured' => true,
            'is_breaking_news' => false,
            'allow_comments' => true,
            'social_media_data' => [
                'facebook' => 'test-facebook',
                'twitter' => 'test-twitter'
            ]
        ]);

        echo "   ✅ Article créé avec succès (ID: {$testArticle->id})\n";
        echo "   ✅ Catégorie: {$testArticle->category}\n";
        echo "   ✅ Tags: " . implode(', ', $testArticle->tags) . "\n";
        echo "   ✅ Image: {$testArticle->featured_image}\n";
        echo "   ✅ Featured: " . ($testArticle->is_featured ? 'Oui' : 'Non') . "\n";

        // Test des méthodes de calcul
        echo "\n4. Test des méthodes de calcul automatique...\n";
        $testArticle->calculateWordCount();
        $testArticle->calculateCharacterCount();
        $testArticle->calculateReadingTime();
        $testArticle->refresh();

        echo "   ✅ Nombre de mots: {$testArticle->word_count}\n";
        echo "   ✅ Nombre de caractères: {$testArticle->character_count}\n";
        echo "   ✅ Temps de lecture: {$testArticle->reading_time} min\n";

        // Test de génération d'extrait
        $testArticle->generateExcerpt(100);
        $testArticle->refresh();
        echo "   ✅ Extrait généré: " . substr($testArticle->excerpt, 0, 50) . "...\n";

        // Test des scopes
        echo "\n5. Test des nouveaux scopes...\n";
        $featured = Article::featured()->count();
        $byCategory = Article::byCategory('Test')->count();
        echo "   ✅ Articles en vedette: {$featured}\n";
        echo "   ✅ Articles catégorie 'Test': {$byCategory}\n";

        // Nettoyage
        echo "\n6. Nettoyage...\n";
        $testArticle->delete();
        echo "   ✅ Article de test supprimé\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

// Test 4: Test hiérarchie des dossiers
echo "\n7. Test de la hiérarchie des dossiers...\n";
try {
    $user = User::first();
    if ($user) {
        $parentFolder = Folder::create([
            'owner_id' => $user->id,
            'nom' => 'Dossier Parent Test',
            'description' => 'Test de hiérarchie',
            'color' => '#ff0000',
            'icon' => 'folder',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $childFolder = Folder::create([
            'owner_id' => $user->id,
            'nom' => 'Sous-dossier Test',
            'description' => 'Sous-dossier de test',
            'color' => '#00ff00',
            'icon' => 'folder-open',
            'parent_id' => $parentFolder->id,
            'sort_order' => 1,
            'is_active' => true,
        ]);

        echo "   ✅ Dossier parent créé (ID: {$parentFolder->id})\n";
        echo "   ✅ Sous-dossier créé (ID: {$childFolder->id})\n";
        echo "   ✅ Chemin complet: " . $childFolder->getFullPath() . "\n";
        echo "   ✅ Profondeur: " . $childFolder->getDepth() . "\n";
        echo "   ✅ Nombre d'enfants du parent: " . $parentFolder->children()->count() . "\n";

        // Nettoyage
        $childFolder->delete();
        $parentFolder->delete();
        echo "   ✅ Dossiers de test supprimés\n";
    }
} catch (Exception $e) {
    echo "   ❌ Erreur: " . $e->getMessage() . "\n";
}

echo "\n============================\n";
echo "✅ TESTS TERMINÉS\n";
echo "============================\n";












