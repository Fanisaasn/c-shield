<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'order',
    ];

    public function questions(): HasMany
    {
        return $this->hasMany(AssessmentQuestion::class);
    }
}
