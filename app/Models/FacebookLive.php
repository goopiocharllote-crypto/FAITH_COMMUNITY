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

    /**
     * Admins may paste Facebook's "Embed" code (an <iframe> whose src is
     * facebook.com/plugins/video.php?href=VIDEO_LINK...) or that plugin URL instead of a
     * plain link. Pull the video link out of it; anything else is returned trimmed.
     */
    public static function extractLink(string $input): string
    {
        $input = trim($input);

        if (preg_match('/<iframe\b[^>]*\bsrc\s*=\s*(["\'])(.*?)\1/is', $input, $match)) {
            $input = trim(html_entity_decode($match[2]));
        }

        $host = strtolower((string) parse_url($input, PHP_URL_HOST));

        if (self::isFacebookUrl($input)
            && str_contains($host, 'facebook.com')
            && str_starts_with((string) parse_url($input, PHP_URL_PATH), '/plugins/')) {
            parse_str((string) parse_url($input, PHP_URL_QUERY), $query);

            if (isset($query['href']) && is_string($query['href']) && $query['href'] !== '') {
                return trim($query['href']);
            }
        }

        return $input;
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

    /**
     * Facebook only embeds links that point at one specific video, e.g.
     * facebook.com/{page}/videos/{id}, facebook.com/watch/?v={id} or
     * facebook.com/reel/{id}. Page links like facebook.com/{page}/live cannot.
     */
    public static function isEmbeddableVideoUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);

        $isFacebookCom = in_array($host, ['facebook.com', 'fb.com'], true)
            || str_ends_with($host, '.facebook.com')
            || str_ends_with($host, '.fb.com');

        if (! $isFacebookCom || ! str_starts_with(strtolower((string) parse_url($url, PHP_URL_SCHEME)), 'https')) {
            return false;
        }

        if (preg_match('#/videos/(?:[^/]+/)?\d+#', $path) || preg_match('#^/reel/\d+#', $path)) {
            return true;
        }

        return str_starts_with($path, '/watch') && isset($query['v']) && preg_match('/^\d+$/', (string) $query['v']);
    }

    /** Short or share links (fb.watch, /share/v/...) that may redirect to an embeddable video. */
    public static function isShortShareUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        return in_array($host, ['fb.watch', 'www.fb.watch', 'fb.me'], true) || str_starts_with($path, '/share/');
    }

    /**
     * A facebook.com/share/v/... or /share/r/... link copied from the Facebook app. Facebook
     * documents video links, not share links, for its player, so these are tried on a
     * best-effort basis (we still try to resolve them to the real video link when saving).
     */
    public static function isShareVideoUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $path = (string) parse_url($url, PHP_URL_PATH);

        $isFacebookCom = $host === 'facebook.com' || str_ends_with($host, '.facebook.com');

        return $isFacebookCom
            && str_starts_with(strtolower((string) parse_url($url, PHP_URL_SCHEME)), 'https')
            && (str_starts_with($path, '/share/v/') || str_starts_with($path, '/share/r/'));
    }

    /** Facebook's embedded video player URL, or null when the link can't be embedded. */
    public function embedUrl(): ?string
    {
        if (! self::isEmbeddableVideoUrl($this->url) && ! self::isShareVideoUrl($this->url)) {
            return null;
        }

        return 'https://www.facebook.com/plugins/video.php?'.http_build_query([
            'href' => $this->url,
            'show_text' => 'false',
            'autoplay' => 'true',
            'allowfullscreen' => 'true',
        ], '', '&', PHP_QUERY_RFC3986);
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
