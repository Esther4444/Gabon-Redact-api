<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Snippet extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'podcast_id',
        'titre',
        'description',
        'start_time',
        'end_time',
        'duree_secondes',
        'video_url',
        'video_path',
        'taille_fichier',
        'thumbnail_url',
        'statut',
        'message_erreur',
        'vues',
        'partages',
        'likes',
        'genere_par_ia',
        'score_pertinence',
        'metadonnees',
    ];

    protected $casts = [
        'start_time' => 'integer',
        'end_time' => 'integer',
        'duree_secondes' => 'integer',
        'taille_fichier' => 'integer',
        'vues' => 'integer',
        'partages' => 'integer',
        'likes' => 'integer',
        'genere_par_ia' => 'boolean',
        'score_pertinence' => 'decimal:2',
        'metadonnees' => 'array',
    ];

    // Relations
    public function podcast()
    {
        return $this->belongsTo(Podcast::class);
    }

    // Méthodes métier
    public function generate()
    {
        $this->update([
            'statut' => 'generating',
        ]);

        try {
            // Cette méthode sera implémentée en Phase 3
            // Elle utilisera FFmpeg pour extraire le segment audio
            // et créer une vidéo avec forme d'onde animée

            $this->calculateDuration();

            $this->update([
                'statut' => 'ready',
                'video_url' => storage_path("app/public/snippets/snippet_{$this->id}.mp4"),
            ]);

            return true;
        } catch (\Exception $e) {
            $this->update([
                'statut' => 'failed',
                'message_erreur' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function calculateDuration()
    {
        if ($this->start_time !== null && $this->end_time !== null) {
            $this->update([
                'duree_secondes' => $this->end_time - $this->start_time,
            ]);
        }

        return $this;
    }

    public function incrementViews()
    {
        $this->increment('vues');
        return $this;
    }

    public function incrementShares()
    {
        $this->increment('partages');
        return $this;
    }

    public function incrementLikes()
    {
        $this->increment('likes');
        return $this;
    }

    public function generateThumbnail()
    {
        // Cette méthode générerait une miniature depuis la vidéo
        // Utilise FFmpeg pour extraire une frame

        if ($this->video_url) {
            $this->update([
                'thumbnail_url' => str_replace('.mp4', '_thumb.jpg', $this->video_url),
            ]);
        }

        return $this;
    }

    public function exportForSocialMedia($platform = 'instagram')
    {
        // Adapte le format vidéo selon la plateforme
        // Instagram: 1:1 (carré), TikTok: 9:16, YouTube: 16:9

        $formats = [
            'instagram' => ['width' => 1080, 'height' => 1080],
            'tiktok' => ['width' => 1080, 'height' => 1920],
            'youtube' => ['width' => 1920, 'height' => 1080],
            'twitter' => ['width' => 1280, 'height' => 720],
        ];

        $format = $formats[$platform] ?? $formats['instagram'];

        return [
            'platform' => $platform,
            'format' => $format,
            'video_url' => $this->video_url,
            'optimized' => true,
        ];
    }

    public function getFormattedDuration()
    {
        if (!$this->duree_secondes) {
            return '00:00';
        }

        $minutes = floor($this->duree_secondes / 60);
        $seconds = $this->duree_secondes % 60;

        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    // Scopes
    public function scopeReady($query)
    {
        return $query->where('statut', 'ready');
    }

    public function scopeGenerating($query)
    {
        return $query->where('statut', 'generating');
    }

    public function scopeFailed($query)
    {
        return $query->where('statut', 'failed');
    }

    public function scopeByAI($query)
    {
        return $query->where('genere_par_ia', true);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderByDesc('vues')->limit($limit);
    }

    public function scopeForPodcast($query, $podcastId)
    {
        return $query->where('podcast_id', $podcastId);
    }
}
