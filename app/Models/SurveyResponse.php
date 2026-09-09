<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResponse extends Model
{
    protected $fillable = [
        'name',
        'service_date',
        'education',
        'age',
        'occupation',
        'is_disability',
        'disability_types',
        'message',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'is_disability' => 'boolean',
            'disability_types' => 'array',
        ];
    }

    public function answers()
    {
        return $this->hasMany(SurveyAnswer::class);
    }
}
