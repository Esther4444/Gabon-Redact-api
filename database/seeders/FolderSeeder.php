<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Folder;
use App\Models\User;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Désactiver temporairement les contraintes de clé étrangère
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Folder::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $users = User::all();

        // Créer exactement 10 dossiers thématiques
        $folders = [
            [
                'nom' => 'Actualités Politiques',
                'description' => 'Actualités politiques du Gabon et de la sous-région',
                'couleur' => '#3B82F6', // Bleu
                'icone' => 'flag',
                'parent_id' => null,
                'sort_order' => 1
            ],
            [
                'nom' => 'Économie',
                'description' => 'Actualités économiques, finances et business',
                'couleur' => '#10B981', // Vert
                'icone' => 'trending-up',
                'parent_id' => null,
                'sort_order' => 2
            ],
            [
                'nom' => 'Sports',
                'description' => 'Actualités sportives nationales et internationales',
                'couleur' => '#F59E0B', // Orange
                'icone' => 'trophy',
                'parent_id' => null,
                'sort_order' => 3
            ],
            [
                'nom' => 'Culture',
                'description' => 'Culture, arts, musique et traditions gabonaises',
                'couleur' => '#8B5CF6', // Violet
                'icone' => 'music',
                'parent_id' => null,
                'sort_order' => 4
            ],
            [
                'nom' => 'International',
                'description' => 'Actualités internationales et relations diplomatiques',
                'couleur' => '#EF4444', // Rouge
                'icone' => 'globe',
                'parent_id' => null,
                'sort_order' => 5
            ],
            [
                'nom' => 'Société',
                'description' => 'Faits de société, éducation et santé',
                'couleur' => '#06B6D4', // Cyan
                'icone' => 'users',
                'parent_id' => null,
                'sort_order' => 6
            ],
            [
                'nom' => 'Environnement',
                'description' => 'Écologie, développement durable et nature',
                'couleur' => '#84CC16', // Vert lime
                'icone' => 'leaf',
                'parent_id' => null,
                'sort_order' => 7
            ],
            [
                'nom' => 'Technologie',
                'description' => 'Innovation, numérique et nouvelles technologies',
                'couleur' => '#6366F1', // Indigo
                'icone' => 'cpu',
                'parent_id' => null,
                'sort_order' => 8
            ],
            [
                'nom' => 'Santé',
                'description' => 'Actualités médicales et bien-être',
                'couleur' => '#EC4899', // Rose
                'icone' => 'heart',
                'parent_id' => null,
                'sort_order' => 9
            ],
            [
                'nom' => 'Urgent',
                'description' => 'Breaking news et actualités urgentes',
                'couleur' => '#DC2626', // Rouge foncé
                'icone' => 'alert-triangle',
                'parent_id' => null,
                'sort_order' => 10
            ]
        ];

        foreach ($folders as $folderData) {
            $user = $users->random();
            Folder::create([
                'owner_id' => $user->id,
                'nom' => $folderData['nom'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
