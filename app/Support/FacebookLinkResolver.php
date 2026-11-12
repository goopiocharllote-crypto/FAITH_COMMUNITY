<?php

namespace App\Support;

use App\Models\FacebookLive;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * The Facebook app's "Copy link" gives short share links (facebook.com/share/v/...
 * or fb.watch/...). Those can't be embedded, so we follow their redirects to find
 * the real video link. Best effort: on any failure the original link is kept.
 */
class FacebookLinkResolver
{
    private const MAX_REDIRECTS = 4;

    public function resolve(string $url): string
    {
        if (FacebookLive::isEmbeddableVideoUrl($url) || ! FacebookLive::isShortShareUrl($url)) {
            return $url;
        }

        $current = $url;

        try {
            for ($hop = 0; $hop < self::MAX_REDIRECTS; $hop++) {
                $response = Http::withoutRedirecting()
                    ->timeout(5)
                    ->withHeaders(['User-Agent' => 'Mozilla/5.0 (compatible; FaithCommunityPortal/1.0)'])
                    ->get($current);

                $location = $response->header('Location');

                if (! $response->redirect() || $location === '') {
                    break;
                }

                $next = $this->absolute($location, $current);

                // Never leave Facebook, and only accept https links.
                if (! str_starts_with($next, 'https://') || ! FacebookLive::isFacebookUrl($next)) {
                    break;
                }

                if (FacebookLive::isEmbeddableVideoUrl($next)) {
                    return $this->clean($next);
                }

                $current = $next;
            }
        } catch (Throwable $exception) {
            report($exception);
        }

        return $url;
    }

    private function absolute(string $location, string $base): string
    {
        if (str_starts_with($location, '//')) {
            return 'https:'.$location;
        }

        if (str_starts_with($location, '/')) {
            return 'https://'.parse_url($base, PHP_URL_HOST).$location;
        }

        return $location;
    }

    /** Drop tracking parameters but keep the video id (?v=) when present. */
    private function clean(string $url): string
    {
        $parts = parse_url($url);
        parse_str($parts['query'] ?? '', $query);
        $keep = isset($query['v']) ? '?v='.rawurlencode((string) $query['v']) : '';

        return 'https://'.$parts['host'].($parts['path'] ?? '/').$keep;
    }
}
