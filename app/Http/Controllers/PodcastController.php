<?php

namespace App\Http\Controllers;

use App\Models\Podcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PodcastController extends Controller
{
    /**
     * Liste des podcasts
     */
    public function index(Request $request)
    {
        $query = Podcast::with(['user.profile', 'transcription', 'snippets'])
            ->where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        // Filtrer par statut
        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }

        // Filtrer par catégorie
        if ($categorie = $request->query('categorie')) {
            $query->where('categorie', $categorie);
        }

        $podcasts = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $podcasts->items(),
            'pagination' => [
                'current_page' => $podcasts->currentPage(),
                'last_page' => $podcasts->lastPage(),
                'per_page' => $podcasts->perPage(),
                'total' => $podcasts->total(),
            ]
        ]);
    }

    /**
     * Afficher un podcast spécifique
     */
    public function show(Request $request, Podcast $podcast)
    {
        $podcast->load(['user.profile', 'transcription', 'snippets']);

        return response()->json([
            'success' => true,
            'data' => $podcast
        ]);
    }

    /**
     * Créer un nouveau podcast
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'tags' => ['nullable', 'array'],
            'image_couverture' => ['nullable', 'string'],
        ]);

        $podcast = Podcast::create([
            'user_id' => $request->user()->id,
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'categorie' => $validated['categorie'] ?? null,
            'tags' => $validated['tags'] ?? [],
            'image_couverture' => $validated['image_couverture'] ?? null,
            'statut' => 'draft',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Podcast créé avec succès',
            'data' => $podcast
        ], 201);
    }

    /**
     * Upload d'un fichier audio pour un podcast
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'mimes:mp3,wav,m4a,ogg,flac', 'max:102400'], // 100 MB max
            'podcast_id' => ['required', 'exists:podcasts,id'],
        ]);

        $podcast = Podcast::findOrFail($validated['podcast_id']);

        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $size = $file->getSize();

        // Générer un nom de fichier unique
        $filename = Str::uuid() . '.' . $extension;

        // Stocker le fichier
        $path = $file->storeAs('public/uploads/podcasts', $filename);
        $url = Storage::url($path);

        // Mettre à jour le podcast
        $podcast->update([
            'audio_path' => $path,
            'audio_url' => $url,
            'taille_fichier' => $size,
            'format_audio' => $extension,
            'statut' => 'processing',
        ]);

        // TODO: Calculer la durée du fichier audio
        // Nécessite FFmpeg ou getID3

        return response()->json([
            'success' => true,
            'message' => 'Fichier audio uploadé avec succès',
            'data' => [
                'podcast' => $podcast,
                'audio_url' => $url,
                'taille_fichier' => $size,
            ]
        ], 201);
    }

    /**
     * Mettre à jour un podcast
     */
    public function update(Request $request, Podcast $podcast)
    {
        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validated = $request->validate([
            'titre' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'categorie' => ['sometimes', 'nullable', 'string', 'max:100'],
            'tags' => ['sometimes', 'array'],
            'image_couverture' => ['sometimes', 'nullable', 'string'],
            'statut' => ['sometimes', 'in:draft,processing,published,archived'],
        ]);

        $podcast->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Podcast mis à jour',
            'data' => $podcast
        ]);
    }

    /**
     * Supprimer un podcast
     */
    public function destroy(Request $request, Podcast $podcast)
    {
        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Supprimer le fichier audio
        if ($podcast->audio_path && Storage::exists($podcast->audio_path)) {
            Storage::delete($podcast->audio_path);
        }

        $podcast->delete();

        return response()->json([
            'success' => true,
            'message' => 'Podcast supprimé'
        ]);
    }

    /**
     * Publier un podcast
     */
    public function publish(Request $request, Podcast $podcast)
    {
        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if (!$podcast->audio_url) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun fichier audio uploadé'
            ], 422);
        }

        $podcast->publish();

        return response()->json([
            'success' => true,
            'message' => 'Podcast publié',
            'data' => $podcast
        ]);
    }

    /**
     * Demander une transcription
     */
    public function transcribe(Request $request, Podcast $podcast)
    {
        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        if (!$podcast->audio_url) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun fichier audio disponible'
            ], 404);
        }

        $transcription = $podcast->requestTranscription();

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
     * Générer des snippets
     */
    public function generateSnippets(Request $request, Podcast $podcast)
    {
        // Vérifier que c'est le propriétaire
        if ($podcast->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        $validated = $request->validate([
            'number' => ['nullable', 'integer', 'min:1', 'max:10'],
        ]);

        $numberOfSnippets = $validated['number'] ?? 3;

        $snippets = $podcast->generateSnippets($numberOfSnippets);

        return response()->json([
            'success' => true,
            'message' => 'Snippets créés',
            'data' => $snippets
        ]);
    }

    /**
     * Récupérer les snippets d'un podcast
     */
    public function snippets(Request $request, Podcast $podcast)
    {
        $snippets = $podcast->snippets()
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $snippets
        ]);
    }

    /**
     * Statistiques d'un podcast
     */
    public function stats(Request $request, Podcast $podcast)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'nombre_telecharges' => $podcast->nombre_telecharges,
                'nombre_vues' => $podcast->nombre_vues,
                'likes' => $podcast->likes,
                'partages' => $podcast->partages,
                'snippets_count' => $podcast->snippets()->count(),
                'transcription_status' => $podcast->transcription?->statut ?? null,
            ]
        ]);
    }

    /**
     * Incrémenter les téléchargements
     */
    public function incrementDownloads(Request $request, Podcast $podcast)
    {
        $podcast->incrementDownloads();

        return response()->json([
            'success' => true,
            'message' => 'Téléchargement enregistré'
        ]);
    }

    /**
     * Incrémenter les vues
     */
    public function incrementViews(Request $request, Podcast $podcast)
    {
        $podcast->incrementViews();

        return response()->json([
            'success' => true,
            'message' => 'Vue enregistrée'
        ]);
    }
}
