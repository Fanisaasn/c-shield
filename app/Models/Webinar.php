<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Webinar extends Model
{
    protected $fillable = [
        'title',
        'description',
        'speaker',
        'webinar_date',
        'platform',
        'registration_url',
        'poster_image',
        'is_published',
    ];

    protected function casts(): array
    {
        return [
            'webinar_date' => 'datetime',
            'is_published' => 'boolean',
        ];
    }
}
