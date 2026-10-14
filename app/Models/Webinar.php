<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Webinar extends Model
{
    protected $fillable = [
        'title',
        'description',
        'speaker',
        'webinar_date',
        'platform',
        'registration_url',
        'meeting_link',
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

    public function registrations(): HasMany
    {
        return $this->hasMany(WebinarRegistration::class);
    }
}
