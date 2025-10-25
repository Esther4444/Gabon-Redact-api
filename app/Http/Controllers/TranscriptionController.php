<?php

namespace App\Http\Controllers;

use App\Models\Transcription;
use App\Models\Live;
use App\Models\Podcast;
use Illuminate\Http\Request;

class TranscriptionController extends Controller
{
    /**
     * Liste des transcriptions de l'utilisateur
     */
    public function index(Request $request)
    {
        $query = Transcription::with('transcribable')
            ->whereHasMorph('transcribable', [Live::class, Podcast::class], function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            })
            ->orderByDesc('created_at');

        // Filtrer par statut
        if ($statut = $request->query('statut')) {
            $query->where('statut', $statut);
        }

        // Filtrer par type
        if ($type = $request->query('type')) {
            $typeClass = $type === 'live' ? Live::class : Podcast::class;
            $query->where('transcribable_type', $typeClass);
        }

        $transcriptions = $query->paginate($request->query('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $transcriptions->items(),
            'pagination' => [
                'current_page' => $transcriptions->currentPage(),
                'last_page' => $transcriptions->lastPage(),
                'per_page' => $transcriptions->perPage(),
                'total' => $transcriptions->total(),
            ]
        ]);
    }

    /**
     * Afficher une transcription spécifique
     */
    public function show(Request $request, Transcription $transcription)
    {
        $transcription->load('transcribable');

        return response()->json([
            'success' => true,
            'data' => $transcription
        ]);
    }

    /**
     * Demander une transcription pour un média
     */
    public function transcribe(Request $request, $mediaId)
    {
        $validated = $request->validate([
            'type' => ['required', 'in:live,podcast'],
            'service' => ['nullable', 'in:whisper,assemblyai'],
        ]);

        $type = $validated['type'];
        $model = $type === 'live' ? Live::class : Podcast::class;
        $media = $model::findOrFail($mediaId);

        // Vérifier que c'est le propriétaire
        if ($media->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 403);
        }

        // Vérifier qu'il y a un fichier à transcrire
        $audioUrl = $type === 'live' ? $media->recording_url : $media->audio_url;
        if (!$audioUrl) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun fichier audio disponible'
            ], 404);
        }

        // Créer la transcription
        $transcription = Transcription::create([
            'transcribable_type' => $model,
            'transcribable_id' => $mediaId,
            'statut' => 'queued',
            'service_utilise' => $validated['service'] ?? 'whisper',
            'langue' => 'fr',
        ]);

        // TODO: Déclencher le job de transcription asynchrone
        // dispatch(new TranscribeMediaJob($transcription));

        return response()->json([
            'success' => true,
            'message' => 'Transcription ajoutée à la file d\'attente',
            'data' => $transcription
        ], 201);
    }

    /**
     * Vérifier le statut d'une transcription
     */
    public function status(Request $request, Transcription $transcription)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $transcription->id,
                'statut' => $transcription->statut,
                'progress' => $this->getProgress($transcription),
                'message_erreur' => $transcription->message_erreur,
            ]
        ]);
    }

    /**
     * Récupérer le texte d'une transcription
     */
    public function text(Request $request, Transcription $transcription)
    {
        if ($transcription->statut !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Transcription non terminée',
                'statut' => $transcription->statut
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'texte_complet' => $transcription->texte_complet,
                'segments' => $transcription->segments,
                'word_count' => $transcription->getWordCount(),
                'confidence_score' => $transcription->confidence_score,
            ]
        ]);
    }

    /**
     * Annuler une transcription en cours
     */
    public function cancel(Request $request, Transcription $transcription)
    {
        if (!in_array($transcription->statut, ['queued', 'processing'])) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible d\'annuler cette transcription'
            ], 422);
        }

        $transcription->markAsFailed('Annulée par l\'utilisateur');

        return response()->json([
            'success' => true,
            'message' => 'Transcription annulée'
        ]);
    }

    /**
     * Générer un article depuis une transcription
     */
    public function generateArticle(Request $request, Transcription $transcription)
    {
        if ($transcription->statut !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Transcription non terminée'
            ], 422);
        }

        $articleData = $transcription->generateArticleFromTranscription();

        if (!$articleData) {
            return response()->json([
                'success' => false,
                'message' => 'Impossible de générer l\'article'
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Article généré',
            'data' => $articleData
        ]);
    }

    /**
     * Exporter une transcription
     */
    public function export(Request $request, Transcription $transcription)
    {
        $validated = $request->validate([
            'format' => ['required', 'in:txt,srt,vtt,json'],
        ]);

        if ($transcription->statut !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Transcription non terminée'
            ], 422);
        }

        $format = $validated['format'];
        $content = $this->formatTranscription($transcription, $format);

        return response()->json([
            'success' => true,
            'data' => [
                'format' => $format,
                'content' => $content,
                'filename' => "transcription_{$transcription->id}.{$format}",
            ]
        ]);
    }

    /**
     * Calculer le progrès d'une transcription
     */
    private function getProgress(Transcription $transcription)
    {
        switch ($transcription->statut) {
            case 'queued':
                return 0;
            case 'processing':
                return 50; // TODO: Calculer le vrai progrès
            case 'completed':
                return 100;
            case 'failed':
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Formater une transcription selon le format demandé
     */
    private function formatTranscription(Transcription $transcription, string $format)
    {
        switch ($format) {
            case 'txt':
                return $transcription->texte_complet;

            case 'srt':
                // Format SRT (SubRip)
                return $this->generateSRT($transcription);

            case 'vtt':
                // Format WebVTT
                return $this->generateVTT($transcription);

            case 'json':
                return json_encode([
                    'texte_complet' => $transcription->texte_complet,
                    'segments' => $transcription->segments,
                    'confidence_score' => $transcription->confidence_score,
                    'langue' => $transcription->langue,
                ], JSON_PRETTY_PRINT);

            default:
                return $transcription->texte_complet;
        }
    }

    /**
     * Générer un fichier SRT
     */
    private function generateSRT(Transcription $transcription)
    {
        if (!$transcription->segments) {
            return $transcription->texte_complet;
        }

        $srt = '';
        foreach ($transcription->segments as $index => $segment) {
            $srt .= ($index + 1) . "\n";
            $srt .= $this->formatTime($segment['start'] ?? 0) . " --> " . $this->formatTime($segment['end'] ?? 0) . "\n";
            $srt .= $segment['text'] . "\n\n";
        }

        return $srt;
    }

    /**
     * Générer un fichier VTT
     */
    private function generateVTT(Transcription $transcription)
    {
        $vtt = "WEBVTT\n\n";

        if (!$transcription->segments) {
            $vtt .= "00:00:00.000 --> 00:00:10.000\n";
            $vtt .= $transcription->texte_complet . "\n";
            return $vtt;
        }

        foreach ($transcription->segments as $segment) {
            $vtt .= $this->formatTime($segment['start'] ?? 0, true) . " --> " . $this->formatTime($segment['end'] ?? 0, true) . "\n";
            $vtt .= $segment['text'] . "\n\n";
        }

        return $vtt;
    }

    /**
     * Formater le temps pour SRT/VTT
     */
    private function formatTime($seconds, $includeMilliseconds = false)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        $millis = ($seconds - floor($seconds)) * 1000;

        if ($includeMilliseconds) {
            return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $secs, $millis);
        }

        return sprintf('%02d:%02d:%02d,%03d', $hours, $minutes, $secs, $millis);
    }
}
