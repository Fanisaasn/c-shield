<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LearningMaterialSeeder extends Seeder
{
    /**
     * Seed the study material shown between the Pre-Assessment and the
     * Post-Assessment from database/seeders/data/learning_material.json
     * (generated via `php artisan content:export-seeders`), file included.
     * Does nothing if no material has been exported yet.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/learning_material.json');

        if (! File::exists($jsonPath)) {
            return;
        }

        $data = json_decode(File::get($jsonPath), true);

        if (! $data) {
            return;
        }

        $material = LearningMaterial::current() ?? new LearningMaterial();

        $material->fill([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'file_path' => $this->restoreAsset($data['file_path'] ?? null),
            'file_type' => $data['file_type'] ?? null,
            'original_name' => $data['original_name'] ?? null,
        ])->save();
    }

    /**
     * Copy a file that was exported into database/seeders/assets back into
     * the public disk, if it isn't already there.
     */
    protected function restoreAsset(?string $relativePath): ?string
    {
        if (! $relativePath) {
            return null;
        }

        $source = database_path('seeders/assets/'.$relativePath);

        if (! File::exists($source)) {
            return null;
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->put($relativePath, File::get($source));
        }

        return $relativePath;
    }
}
