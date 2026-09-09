<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'question',
        'order',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(AssessmentOption::class);
    }
}
