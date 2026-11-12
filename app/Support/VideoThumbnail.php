<?php

namespace App\Support;

use App\Models\LearningMaterial;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class VideoThumbnail
{
    private const AUTO_PREFIX = 'videos/auto-';

    /**
     * Whether a stored thumbnail was fetched from a video link rather than
     * uploaded by an admin.
     */
    public static function isAuto(?string $path): bool
    {
        return $path !== null && str_starts_with($path, self::AUTO_PREFIX);
    }

    /**
     * Download the cover image of a YouTube/Instagram link to the public
     * disk and return its path, or null if there is none to be had. The
     * image is kept locally because Instagram's image URLs expire.
     */
    public static function fetch(string $url): ?string
    {
        $youtubeId = LearningMaterial::parseYoutubeId($url);
        $instagramPost = LearningMaterial::parseInstagramPost($url);

        try {
            $image = match (true) {
                $youtubeId !== null => self::download("https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg"),
                $instagramPost !== null => self::downloadInstagramCover(...$instagramPost),
                default => null,
            };
        } catch (ConnectionException) {
            return null;
        }

        if ($image === null) {
            return null;
        }

        $path = self::AUTO_PREFIX.($youtubeId ?? $instagramPost[1]).'.jpg';

        return Storage::disk('public')->put($path, $image) ? $path : null;
    }

    private static function downloadInstagramCover(string $kind, string $code): ?string
    {
        $page = self::client()->get("https://www.instagram.com/{$kind}/{$code}/embed/captioned/");

        if (! $page->successful() || ! preg_match('/EmbeddedMediaImage"[^>]*?\ssrc="([^"]+)"/', $page->body(), $matches)) {
            return null;
        }

        $imageUrl = html_entity_decode($matches[1]);

        // The URL comes from a fetched page, so only ever follow it to Instagram's own CDN.
        if (! preg_match('#^https://[a-z0-9.-]+\.(cdninstagram\.com|fbcdn\.net)/#i', $imageUrl)) {
            return null;
        }

        return self::download($imageUrl);
    }

    private static function download(string $url): ?string
    {
        $response = self::client()->get($url);

        if (! $response->successful()) {
            return null;
        }

        $info = @getimagesizefromstring($response->body());

        return $info && $info[2] === IMAGETYPE_JPEG ? $response->body() : null;
    }

    private static function client()
    {
        return Http::withUserAgent('Mozilla/5.0')->timeout(10);
    }
}
