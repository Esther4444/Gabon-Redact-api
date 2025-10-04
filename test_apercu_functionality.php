<?php

/**
 * Script de test pour vérifier les nouvelles fonctionnalités d'aperçu et de pagination
 *
 * Ce fichier peut être utilisé pour tester les nouvelles routes et fonctionnalités
 * via des requêtes HTTP ou des tests unitaires.
 */

// Exemples de requêtes à tester

echo "=== TESTS DES NOUVELLES FONCTIONNALITÉS ===\n\n";

echo "1. TEST DE LA PAGINATION\n";
echo "GET /api/articles?page=1&per_page=10\n";
echo "Réponse attendue :\n";
echo "- Articles de la page 1\n";
echo "- Informations de pagination complètes\n";
echo "- Total d'articles dans la base de données\n\n";

echo "2. TEST DE LA RECHERCHE ÉTENDUE\n";
echo "GET /api/articles?search=test&status=draft\n";
echo "Réponse attendue :\n";
echo "- Articles contenant 'test' dans le titre, contenu ou SEO\n";
echo "- Seulement les articles en statut 'draft'\n\n";

echo "3. TEST DE LA PRÉVISUALISATION (Authentifié)\n";
echo "GET /api/articles/123/preview\n";
echo "Réponse attendue :\n";
echo "- Données complètes de l'article\n";
echo "- URL de prévisualisation publique\n";
echo "- Relations chargées (creator, assignee, folder)\n\n";

echo "4. TEST DE LA PRÉVISUALISATION PUBLIQUE\n";
echo "GET /api/articles/preview/mon-article-slug\n";
echo "Réponse attendue :\n";
echo "- Données de l'article par slug\n";
echo "- Métadonnées SEO\n";
echo "- Accessible sans authentification\n\n";

echo "=== STRUCTURE DE RÉPONSE ATTENDUE ===\n\n";

echo "Liste avec pagination :\n";
echo json_encode([
    'success' => true,
    'data' => [
        [
            'id' => 1,
            'title' => 'Mon article',
            'status' => 'draft',
            'slug' => 'mon-article',
            'created_at' => '2025-01-10T10:00:00.000000Z',
            'updated_at' => '2025-01-10T10:00:00.000000Z',
            'creator' => [
                'id' => 1,
                'name' => 'Auteur',
                'profile' => ['avatar' => null]
            ],
            'assignee' => null,
            'folder' => [
                'id' => 1,
                'name' => 'Santé & Bien-être'
            ]
        ]
    ],
    'pagination' => [
        'current_page' => 1,
        'last_page' => 5,
        'per_page' => 15,
        'total' => 67,
        'from' => 1,
        'to' => 15,
        'has_more_pages' => true,
        'prev_page_url' => null,
        'next_page_url' => 'http://localhost/api/articles?page=2'
    ]
], JSON_PRETTY_PRINT);

echo "\n\nPrévisualisation :\n";
echo json_encode([
    'success' => true,
    'data' => [
        'id' => 1,
        'title' => 'Mon article',
        'content' => 'Contenu de l\'article...',
        'status' => 'draft',
        'slug' => 'mon-article',
        'seo_title' => 'SEO Title',
        'seo_description' => 'SEO Description',
        'seo_keywords' => ['mot-clé1', 'mot-clé2'],
        'creator' => [
            'id' => 1,
            'name' => 'Auteur',
            'profile' => ['avatar' => null]
        ],
        'assignee' => null,
        'folder' => [
            'id' => 1,
            'name' => 'Santé & Bien-être'
        ]
    ],
    'preview_url' => 'http://localhost/api/articles/preview/mon-article'
], JSON_PRETTY_PRINT);

echo "\n\n=== INSTRUCTIONS D'UTILISATION ===\n\n";

echo "1. Pour tester la pagination :\n";
echo "   - Utilisez Postman ou curl pour faire des requêtes GET\n";
echo "   - Testez avec différents paramètres : page, per_page, search, status\n\n";

echo "2. Pour tester la prévisualisation :\n";
echo "   - Connectez-vous d'abord pour obtenir un token d'authentification\n";
echo "   - Utilisez le token dans l'header Authorization: Bearer {token}\n";
echo "   - Testez les deux routes de prévisualisation\n\n";

echo "3. Pour tester côté frontend :\n";
echo "   - Consultez le fichier APERCU_INTEGRATION_GUIDE.md\n";
echo "   - Implémentez les exemples HTML/CSS/JavaScript fournis\n";
echo "   - Adaptez selon votre framework frontend (Vue.js, React, etc.)\n\n";

echo "=== NOTES IMPORTANTES ===\n\n";

echo "- La pagination utilise Laravel Paginator avec toutes ses fonctionnalités\n";
echo "- La recherche est étendue à tous les champs textuels de l'article\n";
echo "- La prévisualisation publique est accessible sans authentification\n";
echo "- Toutes les relations sont chargées pour éviter les requêtes N+1\n";
echo "- Les URLs de prévisualisation sont générées dynamiquement\n\n";

echo "Test terminé ! Consultez les logs Laravel pour voir les requêtes SQL générées.\n";
