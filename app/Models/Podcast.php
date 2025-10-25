<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Podcast extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'statut',
        'audio_url',
        'audio_path',
        'duree_secondes',
        'taille_fichier',
        'format_audio',
        'nombre_telecharges',
        'nombre_vues',
        'likes',
        'partages',
        'publie_le',
        'image_couverture',
        'rss_feed_url',
        'spotify_episode_id',
        'apple_podcast_id',
        'categorie',
        'tags',
        'metadonnees',
    ];

    protected $casts = [
        'publie_le' => 'datetime',
        'tags' => 'array',
        'metadonnees' => 'array',
        'duree_secondes' => 'integer',
        'taille_fichier' => 'integer',
        'nombre_telecharges' => 'integer',
        'nombre_vues' => 'integer',
        'likes' => 'integer',
        'partages' => 'integer',
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transcription()
    {
        return $this->morphOne(Transcription::class, 'transcribable');
    }

    public function snippets()
    {
        return $this->hasMany(Snippet::class);
    }

    // Méthodes métier
    public function publish()
    {
        $this->update([
            'statut' => 'published',
            'publie_le' => now(),
        ]);

        return $this;
    }

    public function requestTranscription()
    {
        if (!$this->audio_url) {
            return null;
        }

        return Transcription::create([
            'transcribable_type' => self::class,
            'transcribable_id' => $this->id,
            'statut' => 'queued',
            'langue' => 'fr',
        ]);
    }

    public function generateSnippets($numberOfSnippets = 3)
    {
        // Cette méthode sera implémentée avec l'IA en Phase 3
        // Pour l'instant, elle crée juste des snippets en attente
        $snippets = [];

        for ($i = 1; $i <= $numberOfSnippets; $i++) {
            $snippets[] = Snippet::create([
                'podcast_id' => $this->id,
                'titre' => "Extrait #{$i} - {$this->titre}",
                'statut' => 'pending',
                'genere_par_ia' => true,
            ]);
        }

        return $snippets;
    }

    public function incrementDownloads()
    {
        $this->increment('nombre_telecharges');
        return $this;
    }

    public function incrementViews()
    {
        $this->increment('nombre_vues');
        return $this;
    }

    public function calculateDuration()
    {
        // Cette méthode pourrait utiliser getID3 ou FFmpeg
        // Pour l'instant, elle retourne la valeur existante
        return $this->duree_secondes;
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('statut', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('statut', 'draft');
    }

    public function scopeProcessing($query)
    {
        return $query->where('statut', 'processing');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('categorie', $category);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderByDesc('nombre_vues')->limit($limit);
    }
}
