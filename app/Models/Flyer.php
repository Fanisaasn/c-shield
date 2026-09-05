<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flyer extends Model
{
    protected $fillable = [
        'title',
        'image',
        'description',
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
}
