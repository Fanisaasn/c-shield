<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentUser extends Model
{
    protected $fillable = [
        'name',
        'phone_last_digits',
        'gender',
        'age',
        'education',
        'domicile',
        'occupation_status',
    ];

    public function attempts(): HasMany
    {
        return $this->hasMany(AssessmentAttempt::class);
    }
}
