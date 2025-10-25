<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transcription extends Model
{
    use HasFactory;

    protected $fillable = [
        'transcribable_type',
        'transcribable_id',
        'statut',
        'texte_complet',
        'segments',
        'service_utilise',
        'cout_api',
        'confidence_score',
        'langue',
        'duree_traitement',
        'message_erreur',
        'metadonnees',
    ];

    protected $casts = [
        'segments' => 'array',
        'metadonnees' => 'array',
        'cout_api' => 'decimal:4',
        'confidence_score' => 'decimal:2',
        'duree_traitement' => 'integer',
    ];

    // Relations polymorphiques
    public function transcribable()
    {
        return $this->morphTo();
    }

    // Méthodes métier
    public function markAsProcessing()
    {
        $this->update([
            'statut' => 'processing',
        ]);

        return $this;
    }

    public function markAsCompleted($texteComplet, $segments = null)
    {
        $this->update([
            'statut' => 'completed',
            'texte_complet' => $texteComplet,
            'segments' => $segments,
        ]);

        return $this;
    }

    public function markAsFailed($errorMessage)
    {
        $this->update([
            'statut' => 'failed',
            'message_erreur' => $errorMessage,
        ]);

        return $this;
    }

    public function processWithWhisper()
    {
        // Cette méthode sera implémentée en Phase 3 avec OpenAI Whisper
        // Pour l'instant, elle simule le processus

        $this->markAsProcessing();

        try {
            // Simulation d'un appel API Whisper
            // En production, cela appellerait l'API OpenAI

            $texteSimule = "Transcription automatique du contenu audio. Cette fonctionnalité sera connectée à OpenAI Whisper en Phase 3.";

            $this->markAsCompleted($texteSimule, [
                ['start' => 0, 'end' => 10, 'text' => 'Introduction du podcast'],
                ['start' => 10, 'end' => 30, 'text' => 'Discussion principale'],
                ['start' => 30, 'end' => 45, 'text' => 'Conclusion'],
            ]);

            return true;
        } catch (\Exception $e) {
            $this->markAsFailed($e->getMessage());
            return false;
        }
    }

    public function processWithAssemblyAI()
    {
        // Alternative à Whisper - sera implémentée en Phase 3
        $this->markAsProcessing();

        // Logique AssemblyAI ici

        return $this;
    }

    public function generateArticleFromTranscription()
    {
        if ($this->statut !== 'completed' || !$this->texte_complet) {
            return null;
        }

        // Cette méthode pourrait créer automatiquement un article
        // basé sur la transcription

        return [
            'titre' => "Résumé - " . ($this->transcribable->titre ?? 'Sans titre'),
            'contenu' => $this->texte_complet,
            'type' => 'transcription',
        ];
    }

    public function getWordCount()
    {
        if (!$this->texte_complet) {
            return 0;
        }

        return str_word_count($this->texte_complet);
    }

    public function getEstimatedCost()
    {
        // Estimation du coût basée sur la durée
        // Whisper : ~$0.006 par minute
        if ($this->duree_traitement) {
            return round(($this->duree_traitement / 60) * 0.006, 4);
        }

        return 0;
    }

    // Scopes
    public function scopeQueued($query)
    {
        return $query->where('statut', 'queued');
    }

    public function scopeProcessing($query)
    {
        return $query->where('statut', 'processing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('statut', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'failed');
    }

    public function scopeForLives($query)
    {
        return $query->where('transcribable_type', Live::class);
    }

    public function scopeForPodcasts($query)
    {
        return $query->where('transcribable_type', Podcast::class);
    }
}
