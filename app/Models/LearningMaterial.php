<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * One study material (PDF, PPT, video, or interactive video) a respondent
 * must go through, in order, between the Pre-Assessment and the
 * Post-Assessment; see ordered().
 */
class LearningMaterial extends Model
{
    protected $fillable = [
        'sort_order',
        'title',
        'description',
        'file_path',
        'external_url',
        'file_type',
        'duration_seconds',
        'original_name',
    ];

    /**
     * Human-readable label for each file_type.
     */
    public const TYPE_LABELS = [
        'pdf' => 'PDF',
        'ppt' => 'Presentasi',
        'video' => 'Video',
        'interactive' => 'Video interaktif',
        'youtube' => 'Video YouTube',
        'instagram' => 'Video Instagram',
    ];

    /**
     * Material types embedded from a link (external_url) instead of a file.
     */
    public const LINK_TYPES = ['youtube', 'instagram'];

    /**
     * The link type ("youtube" or "instagram") of a video URL, or null if it
     * is not a YouTube/Instagram video link.
     */
    public static function linkTypeOf(string $url): ?string
    {
        return match (true) {
            self::parseYoutubeId($url) !== null => 'youtube',
            self::parseInstagramPost($url) !== null => 'instagram',
            default => null,
        };
    }

    private static function parseYoutubeId(string $url): ?string
    {
        return preg_match('/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i', $url, $matches)
            ? $matches[1]
            : null;
    }

    /**
     * [kind, code] of an Instagram post/reel URL, e.g. ['reel', 'C1a2B3'].
     */
    private static function parseInstagramPost(string $url): ?array
    {
        if (! preg_match('#instagram\.com/(?:[A-Za-z0-9_.]+/)?(p|reels?|tv)/([A-Za-z0-9_-]+)#i', $url, $matches)) {
            return null;
        }

        return [strtolower($matches[1]) === 'reels' ? 'reel' : strtolower($matches[1]), $matches[2]];
    }

    /**
     * Every material, in the order the respondent must study them.
     *
     * @return Collection<int, self>
     */
    public static function ordered(): Collection
    {
        return static::query()->orderBy('sort_order')->orderBy('id')->get();
    }

    /**
     * Every material that has a file the respondent can study, in order.
     *
     * @return Collection<int, self>
     */
    public static function readySequence(): Collection
    {
        return static::ordered()->filter->isReady()->values();
    }

    /**
     * Whether the material has a file the respondent can study.
     */
    public function isReady(): bool
    {
        if ($this->isLink()) {
            return $this->external_url !== null;
        }

        return $this->file_path !== null && $this->file_type !== null;
    }

    public function isLink(): bool
    {
        return in_array($this->file_type, self::LINK_TYPES, true);
    }

    public function youtubeId(): ?string
    {
        return $this->file_type === 'youtube' && $this->external_url ? self::parseYoutubeId($this->external_url) : null;
    }

    public function instagramEmbedUrl(): ?string
    {
        $post = $this->file_type === 'instagram' && $this->external_url ? self::parseInstagramPost($this->external_url) : null;

        return $post ? "https://www.instagram.com/{$post[0]}/{$post[1]}/embed/" : null;
    }

    /**
     * Video length as m:ss, e.g. "3:05", or null if unknown.
     */
    public function durationLabel(): ?string
    {
        return $this->duration_seconds ? intdiv($this->duration_seconds, 60).':'.str_pad($this->duration_seconds % 60, 2, '0', STR_PAD_LEFT) : null;
    }

    public function isInteractive(): bool
    {
        return $this->file_type === 'interactive';
    }

    public function typeLabel(): string
    {
        return self::TYPE_LABELS[$this->file_type] ?? strtoupper((string) $this->file_type);
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}
