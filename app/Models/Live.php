<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Live extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'titre',
        'description',
        'statut',
        'date_debut',
        'date_fin',
        'duree_secondes',
        'viewers_max',
        'viewers_total',
        'likes',
        'commentaires',
        'rtmp_url',
        'stream_key',
        'platforms',
        'recording_url',
        'recording_size',
        'thumbnail_url',
        'metadonnees',
    ];

    protected $casts = [
        'date_debut' => 'datetime',
        'date_fin' => 'datetime',
        'platforms' => 'array',
        'metadonnees' => 'array',
        'duree_secondes' => 'integer',
        'viewers_max' => 'integer',
        'viewers_total' => 'integer',
        'likes' => 'integer',
        'commentaires' => 'integer',
        'recording_size' => 'integer',
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

    public function livePlatforms()
    {
        return $this->hasMany(LivePlatform::class);
    }

    // Méthodes métier
    public function start()
    {
        $this->update([
            'statut' => 'live',
            'date_debut' => now(),
        ]);

        return $this;
    }

    public function stop()
    {
        $dateFin = now();
        $duree = $this->date_debut ? $this->date_debut->diffInSeconds($dateFin) : 0;

        $this->update([
            'statut' => 'ended',
            'date_fin' => $dateFin,
            'duree_secondes' => $duree,
        ]);

        return $this;
    }

    public function requestTranscription()
    {
        if (!$this->recording_url) {
            return null;
        }

        return Transcription::create([
            'transcribable_type' => self::class,
            'transcribable_id' => $this->id,
            'statut' => 'queued',
            'langue' => 'fr',
        ]);
    }

    public function incrementViewers()
    {
        $this->increment('viewers_total');

        if ($this->viewers_total > $this->viewers_max) {
            $this->update(['viewers_max' => $this->viewers_total]);
        }

        return $this;
    }

    public function generateStreamKey()
    {
        $this->update([
            'stream_key' => 'live_' . uniqid() . '_' . bin2hex(random_bytes(8)),
            'rtmp_url' => config('app.url') . '/rtmp/live/' . $this->id,
        ]);

        return $this;
    }

    // Scopes
    public function scopeScheduled($query)
    {
        return $query->where('statut', 'scheduled');
    }

    public function scopeLive($query)
    {
        return $query->where('statut', 'live');
    }

    public function scopeEnded($query)
    {
        return $query->where('statut', 'ended');
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
