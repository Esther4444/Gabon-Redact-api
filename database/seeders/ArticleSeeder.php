<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver temporairement les contraintes de clé étrangère
        \DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Article::truncate();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = User::all();
        $folders = Folder::all();

        // Créer exactement 10 articles
        $articles = [
            [
                'title' => 'Gabon : Nouvelle politique économique du gouvernement',
                'content' => 'Le gouvernement gabonais a annoncé aujourd\'hui une série de mesures économiques visant à stimuler la croissance et à améliorer les conditions de vie des citoyens. Ces réformes touchent plusieurs secteurs clés de l\'économie nationale et visent à diversifier les sources de revenus du pays.',
                'status' => 'published',
                'seo_title' => 'Gabon politique économique gouvernement 2024',
                'seo_description' => 'Découvrez les nouvelles mesures économiques annoncées par le gouvernement gabonais pour stimuler la croissance.',
                'seo_keywords' => ['Gabon', 'économie', 'gouvernement', 'politique', 'croissance']
            ],
            [
                'title' => 'Libreville accueille le sommet de l\'Union Africaine',
                'content' => 'La capitale gabonaise se prépare à recevoir les dirigeants africains pour le prochain sommet de l\'Union Africaine. Cet événement majeur mettra en lumière les défis et opportunités du continent et renforcera la position du Gabon sur la scène internationale.',
                'status' => 'draft',
                'seo_title' => 'Sommet Union Africaine Libreville 2024',
                'seo_description' => 'Libreville accueille le sommet de l\'Union Africaine avec les dirigeants du continent.',
                'seo_keywords' => ['Union Africaine', 'Libreville', 'sommet', 'Afrique', 'diplomatie']
            ],
            [
                'title' => 'Innovation technologique au Gabon : Les startups à l\'honneur',
                'content' => 'Le secteur des technologies au Gabon connaît un essor remarquable avec l\'émergence de nombreuses startups innovantes. Ces entreprises contribuent à la transformation numérique du pays et créent de nouveaux emplois pour les jeunes diplômés.',
                'status' => 'review',
                'seo_title' => 'Startups technologie Gabon innovation 2024',
                'seo_description' => 'Découvrez l\'écosystème des startups technologiques au Gabon et leur impact sur l\'économie.',
                'seo_keywords' => ['startup', 'technologie', 'Gabon', 'innovation', 'numérique']
            ],
            [
                'title' => 'Protection de l\'environnement : Le Gabon s\'engage',
                'content' => 'Face aux défis climatiques, le Gabon renforce ses efforts en matière de protection de l\'environnement. De nouvelles initiatives voient le jour pour préserver la biodiversité exceptionnelle du pays et lutter contre le changement climatique.',
                'status' => 'published',
                'seo_title' => 'Environnement Gabon biodiversité protection',
                'seo_description' => 'Le Gabon renforce ses initiatives de protection de l\'environnement et de la biodiversité.',
                'seo_keywords' => ['environnement', 'biodiversité', 'Gabon', 'climat', 'protection']
            ],
            [
                'title' => 'Éducation : Réforme du système scolaire gabonais',
                'content' => 'Le ministère de l\'Éducation a présenté un plan de réforme ambitieux pour moderniser le système éducatif gabonais. Cette initiative vise à améliorer la qualité de l\'enseignement et à mieux préparer les élèves aux défis du monde moderne.',
                'status' => 'draft',
                'seo_title' => 'Réforme éducation Gabon système scolaire',
                'seo_description' => 'Découvrez les nouvelles réformes du système éducatif gabonais pour améliorer la qualité.',
                'seo_keywords' => ['éducation', 'réforme', 'Gabon', 'école', 'enseignement']
            ],
            [
                'title' => 'Santé publique : Campagne de vaccination massive au Gabon',
                'content' => 'Le ministère de la Santé lance une nouvelle campagne de vaccination pour protéger la population contre les maladies infectieuses. Cette initiative s\'inscrit dans le cadre de la politique de santé publique du gouvernement.',
                'status' => 'published',
                'seo_title' => 'Vaccination Gabon santé publique campagne',
                'seo_description' => 'Le Gabon lance une campagne de vaccination massive pour protéger sa population.',
                'seo_keywords' => ['santé', 'vaccination', 'Gabon', 'public', 'prévention']
            ],
            [
                'title' => 'Infrastructure : Nouveau pont sur l\'Ogooué',
                'content' => 'La construction du nouveau pont sur l\'Ogooué marque une étape importante dans le développement des infrastructures gabonaises. Ce projet améliorera la circulation entre les régions et stimulera l\'économie locale.',
                'status' => 'draft',
                'seo_title' => 'Pont Ogooué infrastructure Gabon transport',
                'seo_description' => 'Le nouveau pont sur l\'Ogooué améliore les infrastructures de transport au Gabon.',
                'seo_keywords' => ['infrastructure', 'pont', 'Ogooué', 'transport', 'développement']
            ],
            [
                'title' => 'Culture : Festival des arts traditionnels à Port-Gentil',
                'content' => 'Port-Gentil accueille la 15ème édition du Festival des arts traditionnels gabonais. Cet événement culturel met à l\'honneur le patrimoine artistique du pays et attire des visiteurs de toute l\'Afrique centrale.',
                'status' => 'review',
                'seo_title' => 'Festival arts traditionnels Port-Gentil culture',
                'seo_description' => 'Le Festival des arts traditionnels de Port-Gentil célèbre la culture gabonaise.',
                'seo_keywords' => ['culture', 'festival', 'Port-Gentil', 'tradition', 'art']
            ],
            [
                'title' => 'Sport : L\'équipe nationale de football en préparation',
                'content' => 'L\'équipe nationale gabonaise de football entame sa préparation pour les prochaines compétitions internationales. Les Panthères du Gabon visent une qualification pour la Coupe d\'Afrique des Nations.',
                'status' => 'published',
                'seo_title' => 'Football Gabon équipe nationale Panthères',
                'seo_description' => 'L\'équipe nationale gabonaise de football se prépare pour les compétitions internationales.',
                'seo_keywords' => ['football', 'Gabon', 'équipe nationale', 'Panthères', 'sport']
            ],
            [
                'title' => 'Tourisme : Le Gabon mise sur l\'écotourisme',
                'content' => 'Le Gabon développe une stratégie d\'écotourisme pour valoriser ses parcs nationaux et sa biodiversité exceptionnelle. Cette approche durable vise à créer des emplois tout en préservant l\'environnement.',
                'status' => 'draft',
                'seo_title' => 'Écotourisme Gabon parcs nationaux biodiversité',
                'seo_description' => 'Le Gabon développe l\'écotourisme pour valoriser ses parcs nationaux et sa biodiversité.',
                'seo_keywords' => ['tourisme', 'écotourisme', 'Gabon', 'parcs', 'biodiversité']
            ]
        ];

        foreach ($articles as $index => $articleData) {
            $user = $users->random();
            $folder = $folders->random();

            Article::create([
                'title' => $articleData['title'],
                'slug' => Str::slug($articleData['title']) . '-' . ($index + 1),
                'content' => $articleData['content'],
                'status' => $articleData['status'],
                'folder_id' => $folder->id,
                'created_by' => $user->id,
                'assigned_to' => rand(0, 1) ? $users->random()->id : null,
                'seo_title' => $articleData['seo_title'],
                'seo_description' => $articleData['seo_description'],
                'seo_keywords' => $articleData['seo_keywords'],
                'published_at' => $articleData['status'] === 'published' ? now()->subDays(rand(1, 30)) : null,
                'metadata' => [
                    'word_count' => str_word_count($articleData['content']),
                    'reading_time' => ceil(str_word_count($articleData['content']) / 200),
                    'category' => $folder->name,
                    'tags' => $articleData['seo_keywords']
                ],
            ]);
        }
    }
}
