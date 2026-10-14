<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DashboardSlide extends Model
{
    protected $fillable = [
        'title',
        'description',
        'image_path',
        'sort_order',
        'is_active',
        'link_url',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Scope query to only active slides.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope query to order slides by sort_order asc, then id asc.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    /**
     * Get the full URL of the slide image.
     */
    public function getImageUrlAttribute(): string
    {
        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        return asset('storage/' . ltrim($this->image_path, '/'));
    }
}
