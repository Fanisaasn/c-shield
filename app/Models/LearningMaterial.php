<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * The single study material (Social Engineering) a respondent must go
 * through between the Pre-Assessment and the Post-Assessment. Only one
 * row is ever used; see current().
 */
class LearningMaterial extends Model
{
    protected $fillable = [
        'title',
        'description',
        'file_path',
        'file_type',
        'original_name',
    ];

    /**
     * The material currently in use, or null if the admin has not set one.
     */
    public static function current(): ?self
    {
        return static::query()->oldest('id')->first();
    }

    /**
     * Whether the material has a file the respondent can study.
     */
    public function isReady(): bool
    {
        return $this->file_path !== null && $this->file_type !== null;
    }

    public function fileUrl(): ?string
    {
        return $this->file_path ? Storage::disk('public')->url($this->file_path) : null;
    }
}
