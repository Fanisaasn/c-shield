<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssessmentAttempt extends Model
{
    private const LEVELS = [
        20 => 'Sangat Rendah',
        40 => 'Rendah',
        60 => 'Cukup',
        80 => 'Baik',
        100 => 'Sangat Baik',
    ];

    protected $fillable = [
        'assessment_user_id',
        'type',
        'score',
        'level',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'score' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(AssessmentUser::class, 'assessment_user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(AssessmentAnswer::class);
    }

    public function calculateScore(): float
    {
        $total = $this->answers()->count();

        if ($total === 0) {
            return 0;
        }

        $correct = $this->answers()->where('is_correct', true)->count();

        return round(($correct / $total) * 100, 2);
    }

    public function determineLevel(float $score): string
    {
        foreach (self::LEVELS as $maxScore => $label) {
            if ($score <= $maxScore) {
                return $label;
            }
        }

        return 'Sangat Baik';
    }

    public function finalizeScore(): void
    {
        $score = $this->calculateScore();

        $this->update([
            'score' => $score,
            'level' => $this->determineLevel($score),
            'completed_at' => now(),
        ]);
    }
}
