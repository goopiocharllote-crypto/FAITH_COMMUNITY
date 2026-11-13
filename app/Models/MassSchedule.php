<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MassSchedule extends Model
{
    /** Intention requests close this many minutes before the Mass starts. */
    public const CLOSING_MINUTES_BEFORE_MASS = 10;

    protected $fillable = [
        'title',
        'mass_date',
        'closing_at',
        'livestream_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'mass_date' => 'datetime',
            'closing_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function massIntentions(): HasMany
    {
        return $this->hasMany(MassIntention::class, 'schedule_id');
    }

    /**
     * A Mass counts as "live" from 15 minutes before it starts until 2 hours after.
     */
    public function isLiveNow(): bool
    {
        return now()->between($this->mass_date->copy()->subMinutes(15), $this->mass_date->copy()->addHours(2));
    }

    /**
     * Embeddable player URL for YouTube and Facebook video links; null for other links
     * (those are opened in a new tab instead).
     */
    public function livestreamEmbedUrl(): ?string
    {
        $url = $this->livestream_url;

        if (! $url) {
            return null;
        }

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        if (in_array($host, ['youtu.be', 'www.youtu.be'], true)) {
            $id = trim($path, '/');
        } elseif (str_ends_with($host, 'youtube.com')) {
            $id = $query['v'] ?? null;

            if (! $id && preg_match('#^/(live|embed|shorts)/([^/?]+)#', $path, $matches)) {
                $id = $matches[2];
            }
        }

        if (isset($id) && $id && preg_match('/^[A-Za-z0-9_-]{6,20}$/', $id)) {
            return 'https://www.youtube.com/embed/'.$id;
        }

        if ((str_ends_with($host, 'facebook.com') || $host === 'fb.watch')
            && (str_contains($path, '/videos/') || str_starts_with($path, '/watch') || $host === 'fb.watch')) {
            return 'https://www.facebook.com/plugins/video.php?show_text=false&href='.rawurlencode($url);
        }

        return null;
    }
}
