<?php

namespace App\Http\Controllers;

use App\Models\Live;
use App\Models\LivePlatform;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LiveController extends Controller
{
    /**
     * Liste des lives de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = Live::with(['user.profile', 'livePlatforms', 'transcription'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        // Filtrer par statut
        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }

        $lives = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $lives->items(),
            'pagination' => [
                'current_page' => $lives->currentPage(),
                'last_page' => $lives->lastPage(),
                'per_page' => $lives->perPage(),
                'total' => $lives->total(),
            ]
        ]);
    }

    /**
     * Afficher un live spécifique
     */
    public function show(Request $request, Live $live)
    {
        $live->load(['user.profile', 'livePlatforms', 'transcription']);

        return response()->json([
            'success' => true,
            'data' => $live
        ]);
    }

    /**
     * Créer un nouveau live
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_debut' => ['nullable', 'date', 'after:now'],
            'platforms' => ['nullable', 'array'],
            'platforms.*' => ['string', 'in:facebook,youtube,twitter,twitch,instagram'],
        ]);

        $live = Live::create([
            'user_id' => $request->user()->id,
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'date_debut' => $validated['date_debut'] ?? null,
            'statut' => 'scheduled',
            'platforms' => $validated['platforms'] ?? [],
        ]);

        // Générer la clé de stream
        $live->generateStreamKey();

        // Créer les configurations de plateforme
        if (!empty($validated['platforms'])) {
            foreach ($validated['platforms'] as $platform) {
                LivePlatform::create([
                    'live_id' => $live->id,
                    'platform' => $platform,
                    'statut' => 'inactive',
                ]);
            }
        }

        $live->load(['livePlatforms']);

        return response()->json([
            'success' => true,
            'message' => 'Live créé avec succès',
            'data' => $live
        ], 201);
    }

    /**
     * Mettre à jour un live
     */
    public function update(Request $request, Live $live)
    {
        // Vérifier que c'est le propriétaire
        if ($live->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validated = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'date_debut' => ['sometimes', 'nullable', 'date'],
            'thumbnail_url' => ['sometimes', 'nullable', 'string'],
        ]);

        $live->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Live mis à jour',
            'data' => $live
        ]);
    }

    /**
     * Supprimer un live
     */
    public function destroy(Request $request, Live $live)
    {
        // Vérifier que c'est le propriétaire
        if ($live->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $live->delete();

        return response()->json([
            'success' => true,
            'message' => 'Live supprimé'
        ]);
    }

    /**
     * Démarrer un live
     */
    public function start(Request $request, Live $live)
    {
        // Vérifier que c'est le propriétaire
        if ($live->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($live->statut === 'live') {
            return response()->json([
                'success' => false,
                'message' => 'Le live est déjà en cours'
            ], 422);
        }

        $live->start();

        // Activer les plateformes
        foreach ($live->livePlatforms as $platform) {
            $platform->connect();
        }

        return response()->json([
            'success' => true,
            'message' => 'Live démarré',
            'data' => $live->load(['livePlatforms'])
        ]);
    }

    /**
     * Arrêter un live
     */
    public function stop(Request $request, Live $live)
    {
        // Vérifier que c'est le propriétaire
        if ($live->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if ($live->statut !== 'live') {
            return response()->json([
                'success' => false,
                'message' => 'Le live n\'est pas en cours'
            ], 422);
        }

        $live->stop();

        // Déconnecter les plateformes
        foreach ($live->livePlatforms as $platform) {
            $platform->disconnect();
        }

        return response()->json([
            'success' => true,
            'message' => 'Live arrêté',
            'data' => $live->load(['livePlatforms'])
        ]);
    }

    /**
     * Récupérer l'enregistrement d'un live
     */
    public function recording(Request $request, Live $live)
    {
        if (!$live->recording_url) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun enregistrement disponible'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'recording_url' => $live->recording_url,
                'recording_size' => $live->recording_size,
                'thumbnail_url' => $live->thumbnail_url,
                'duree_secondes' => $live->duree_secondes,
            ]
        ]);
    }

    /**
     * Demander une transcription
     */
    public function transcribe(Request $request, Live $live)
    {
        // Vérifier que c'est le propriétaire
        if ($live->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if (!$live->recording_url) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun enregistrement disponible'
            ], 404);
        }

        $transcription = $live->requestTranscription();

        if (!$transcription) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de créer la transcription'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Transcription demandée',
            'data' => $transcription
        ]);
    }

    /**
     * Statistiques d'un live
     */
    public function stats(Request $request, Live $live)
    {
        $platformStats = $live->livePlatforms->map(function ($platform) {
            return $platform->getStats();
        });

        return response()->json([
            'success' => true,
            'data' => [
                'live' => [
                    'viewers_max' => $live->viewers_max,
                    'viewers_total' => $live->viewers_total,
                    'likes' => $live->likes,
                    'commentaires' => $live->commentaires,
                    'duree_secondes' => $live->duree_secondes,
                ],
                'platforms' => $platformStats,
            ]
        ]);
    }
}
