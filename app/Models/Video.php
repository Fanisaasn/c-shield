<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    // Slug video demo yang memakai paket interaktif bawaan di public/interactive-video.
    public const BUNDLED_INTERACTIVE_SLUG = 'jaga-data-jaga-diri';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'type',
        'video_url',
        'video_path',
        'interactive_path',
        'thumbnail',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function isInteractive(): bool
    {
        return $this->type === 'interactive' || $this->slug === self::BUNDLED_INTERACTIVE_SLUG;
    }

    public function usesBundledInteractivePackage(): bool
    {
        return $this->isInteractive() && ! $this->interactive_path;
    }
}
