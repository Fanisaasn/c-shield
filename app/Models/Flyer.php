<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Flyer extends Model
{
    protected $fillable = [
        'title',
        'description',
        'source_url',
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

    protected static function booted(): void
    {
        static::deleting(function (Flyer $flyer) {
            foreach ($flyer->images as $image) {
                Storage::disk('public')->delete($image->image);
            }
        });
    }

    public function images()
    {
        return $this->hasMany(FlyerImage::class)->orderBy('sort_order');
    }
}
