<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LivePlatform extends Model
{
    use HasFactory;

    protected $fillable = [
        'live_id',
        'platform',
        'stream_key',
        'rtmp_url',
        'playback_url',
        'external_id',
        'external_url',
        'statut',
        'message_erreur',
        'viewers_actuels',
        'viewers_max',
        'likes',
        'commentaires',
        'partages',
        'connecte_le',
        'deconnecte_le',
        'metadonnees',
    ];

    protected $casts = [
        'connecte_le' => 'datetime',
        'deconnecte_le' => 'datetime',
        'metadonnees' => 'array',
        'viewers_actuels' => 'integer',
        'viewers_max' => 'integer',
        'likes' => 'integer',
        'commentaires' => 'integer',
        'partages' => 'integer',
    ];

    // Relations
    public function live()
    {
        return $this->belongsTo(Live::class);
    }

    // Méthodes métier
    public function connect()
    {
        $this->update([
            'statut' => 'active',
            'connecte_le' => now(),
        ]);

        return $this;
    }

    public function disconnect()
    {
        $this->update([
            'statut' => 'inactive',
            'deconnecte_le' => now(),
        ]);

        return $this;
    }

    public function updateViewers($count)
    {
        $this->update([
            'viewers_actuels' => $count,
        ]);

        if ($count > $this->viewers_max) {
            $this->update(['viewers_max' => $count]);
        }

        return $this;
    }

    public function connectToFacebook($accessToken)
    {
        // Cette méthode sera implémentée en Phase 3
        // Elle se connectera à l'API Facebook Live

        try {
            $this->update([
                'external_id' => 'fb_live_' . uniqid(),
                'external_url' => 'https://facebook.com/live/' . uniqid(),
                'rtmp_url' => 'rtmps://live-api-s.facebook.com:443/rtmp/',
                'stream_key' => 'FB-' . bin2hex(random_bytes(16)),
            ]);

            $this->connect();

            return true;
        } catch (\Exception $e) {
            $this->update([
                'statut' => 'error',
                'message_erreur' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function connectToYouTube($apiKey)
    {
        // Cette méthode sera implémentée en Phase 3
        // Elle se connectera à l'API YouTube Live

        try {
            $this->update([
                'external_id' => 'yt_live_' . uniqid(),
                'external_url' => 'https://youtube.com/watch?v=' . uniqid(),
                'rtmp_url' => 'rtmp://a.rtmp.youtube.com/live2/',
                'stream_key' => 'YT-' . bin2hex(random_bytes(16)),
            ]);

            $this->connect();

            return true;
        } catch (\Exception $e) {
            $this->update([
                'statut' => 'error',
                'message_erreur' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function connectToTwitter($credentials)
    {
        // Cette méthode sera implémentée en Phase 3
        // Elle se connectera à l'API Twitter/X Live

        try {
            $this->update([
                'external_id' => 'tw_live_' . uniqid(),
                'external_url' => 'https://twitter.com/i/broadcasts/' . uniqid(),
                'rtmp_url' => 'rtmp://publish.pscp.tv/live/',
                'stream_key' => 'TW-' . bin2hex(random_bytes(16)),
            ]);

            $this->connect();

            return true;
        } catch (\Exception $e) {
            $this->update([
                'statut' => 'error',
                'message_erreur' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function getStats()
    {
        return [
            'platform' => $this->platform,
            'viewers_actuels' => $this->viewers_actuels,
            'viewers_max' => $this->viewers_max,
            'likes' => $this->likes,
            'commentaires' => $this->commentaires,
            'partages' => $this->partages,
            'duree_connexion' => $this->connecte_le ? $this->connecte_le->diffInMinutes($this->deconnecte_le ?? now()) : 0,
        ];
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('statut', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('statut', 'inactive');
    }

    public function scopeWithError($query)
    {
        return $query->where('statut', 'error');
    }

    public function scopeForLive($query, $liveId)
    {
        return $query->where('live_id', $liveId);
    }

    public function scopeForPlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }
}
