<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * The parish's current Facebook Live link. Only one is kept: posting a new
 * link replaces the previous one.
 */
class FacebookLive extends Model
{
    public const DEFAULT_TITLE = 'Parish Live Mass';

    /** Hosts accepted for a Facebook Live link. */
    public const ALLOWED_HOSTS = ['facebook.com', 'fb.watch', 'fb.com', 'fb.me'];

    protected $fillable = [
        'title',
        'url',
        'is_live',
        'posted_by',
    ];

    protected function casts(): array
    {
        return [
            'is_live' => 'boolean',
        ];
    }

    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }

    public static function isFacebookUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        foreach (self::ALLOWED_HOSTS as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return false;
    }

    public function displayTitle(): string
    {
        return $this->title ?: self::DEFAULT_TITLE;
    }

    public function poster(): BelongsTo
    {
        return $this->belongsTo(User::class, 'posted_by');
    }
}
