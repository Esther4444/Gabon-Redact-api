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

        // Créer exactement 10 dossiers
        $folders = [
            'Actualités Politiques',
            'Économie & Business',
            'Sports',
            'Culture & Société',
            'International',
            'Santé & Bien-être',
            'Technologie',
            'Environnement',
            'Éducation',
            'Brouillons'
        ];

        foreach ($folders as $folderName) {
            $user = $users->random();
            Folder::create([
                'owner_id' => $user->id,
                'name' => $folderName,
            ]);
        }
    }
}
