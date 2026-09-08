<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentVideo extends Model
{
    protected $fillable = [
        'assessment_category_id',
        'title',
        'description',
        'video_url',
        'video_path',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }

    /**
     * Turn a regular YouTube link (watch, youtu.be, or shorts) into the
     * youtube.com/embed/{id} form YouTube allows to be shown in an
     * <iframe> — a plain watch/shorts URL refuses to load there. Links
     * from other platforms are passed through unchanged.
     */
    public function embedUrl(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        $patterns = [
            '~youtu\.be/([A-Za-z0-9_-]+)~',
            '~youtube\.com/watch\?v=([A-Za-z0-9_-]+)~',
            '~youtube\.com/shorts/([A-Za-z0-9_-]+)~',
            '~youtube\.com/embed/([A-Za-z0-9_-]+)~',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->video_url, $matches)) {
                return 'https://www.youtube.com/embed/'.$matches[1];
            }
        }

        return $this->video_url;
    }
}
