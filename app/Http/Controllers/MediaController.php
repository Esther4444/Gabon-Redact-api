<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Liste des médias de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = Media::where('user_id', $request->user()->id)
            ->orderByDesc('created_at');

        // Filtrer par type (dans metadonnees)
        if ($type = $request->query('type')) {
            $query->whereJsonContains('metadonnees->type', $type);
        }

        $media = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $media->items(),
            'pagination' => [
                'current_page' => $media->currentPage(),
                'last_page' => $media->lastPage(),
                'per_page' => $media->perPage(),
                'total' => $media->total(),
            ]
        ]);
    }

    /**
     * Upload d'un fichier
     */
    public function upload(Request $request)
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:10240'], // 10 MB max
            'type' => ['nullable', 'string', 'in:image,video,document,audio'],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        $size = $file->getSize();

        // Déterminer le type basé sur le MIME type si non fourni
        if (!isset($validated['type'])) {
            $validated['type'] = $this->detectMediaType($mimeType);
        }

        // Générer un nom de fichier unique
        $filename = Str::uuid() . '.' . $extension;

        // Déterminer le dossier de stockage basé sur le type
        $folder = match($validated['type']) {
            'image' => 'images',
            'video' => 'videos',
            'audio' => 'audio',
            default => 'documents'
        };

        // Stocker le fichier
        $path = $file->storeAs("public/uploads/{$folder}", $filename);
        $url = Storage::url($path);

        // Générer une miniature si c'est une image
        $thumbnailUrl = null;
        if ($validated['type'] === 'image') {
            $thumbnailUrl = $this->generateThumbnail($file, $filename);
        }

        // Créer l'enregistrement en base de données (avec colonnes francisées)
        $media = Media::create([
            'user_id' => $request->user()->id,
            'disque' => 'public',
            'chemin' => $path,
            'type_mime' => $mimeType,
            'taille_octets' => $size,
            'metadonnees' => [
                'nom_fichier' => $originalName,
                'nom_stockage' => $filename,
                'url' => $url,
                'url_miniature' => $thumbnailUrl,
                'type' => $validated['type'],
                'texte_alternatif' => $validated['alt_text'] ?? null,
                'titre' => $validated['title'] ?? $originalName,
            ],
        ]);

        // Retourner les données avec URL accessible
        return response()->json([
            'success' => true,
            'message' => 'Fichier uploadé avec succès',
            'data' => [
                'id' => $media->id,
                'url' => $url,
                'thumbnail_url' => $thumbnailUrl,
                'filename' => $originalName,
                'type' => $validated['type'],
                'size' => $size,
                'mime_type' => $mimeType,
            ]
        ], 201);
    }

    /**
     * Supprimer un média
     */
    public function destroy(Media $media)
    {
        // Vérifier que l'utilisateur est le propriétaire
        if ($media->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Supprimer le fichier du stockage
        if (Storage::exists($media->chemin)) {
            Storage::delete($media->chemin);
        }

        // Supprimer la miniature si elle existe (stockée dans metadonnees)
        $thumbnailPath = $media->metadonnees['url_miniature'] ?? null;
        if ($thumbnailPath && Storage::exists($thumbnailPath)) {
            Storage::delete($thumbnailPath);
        }

        // Supprimer l'enregistrement
        $media->delete();

        return response()->json([
            'success' => true,
            'message' => 'Média supprimé avec succès'
        ]);
    }

    /**
     * Détecte le type de média basé sur le MIME type
     */
    private function detectMediaType(string $mimeType): string
    {
        return match(true) {
            str_starts_with($mimeType, 'image/') => 'image',
            str_starts_with($mimeType, 'video/') => 'video',
            str_starts_with($mimeType, 'audio/') => 'audio',
            default => 'document'
        };
    }

    /**
     * Génère une miniature pour une image
     */
    private function generateThumbnail($file, string $filename): ?string
    {
        try {
            // Pour l'instant, on retourne null
            // TODO: Implémenter la génération de miniature avec Intervention Image
            // ou GD Library
            return null;
        } catch (\Exception $e) {
            \Log::error('Erreur génération miniature: ' . $e->getMessage());
            return null;
        }
    }
}
