<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentQuestion extends Model
{
    protected $fillable = [
        'assessment_category_id',
        'question',
        'order',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssessmentCategory::class, 'assessment_category_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(AssessmentOption::class);
    }
}
