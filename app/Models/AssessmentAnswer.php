<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AssessmentAnswer extends Model
{
    protected $fillable = [
        'assessment_attempt_id',
        'assessment_question_id',
        'is_correct',
    ];

    protected function casts(): array
    {
        return [
            'is_correct' => 'boolean',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(AssessmentAttempt::class, 'assessment_attempt_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id');
    }

    /**
     * The options the respondent picked for this question (one or more).
     */
    public function options(): BelongsToMany
    {
        return $this->belongsToMany(AssessmentOption::class, 'assessment_answer_options');
    }
}
