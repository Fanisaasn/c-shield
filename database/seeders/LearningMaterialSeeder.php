<?php

namespace Database\Seeders;

use App\Models\LearningMaterial;
use App\Support\InteractivePackage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class LearningMaterialSeeder extends Seeder
{
    /**
     * Seed the study materials shown between the Pre-Assessment and the
     * Post-Assessment from database/seeders/data/learning_materials.json
     * (generated via `php artisan content:export-seeders`), files included.
     * Does nothing if no material has been exported yet.
     */
    public function run(): void
    {
        foreach ($this->exportedMaterials() as $order => $data) {
            $type = $data['file_type'] ?? null;
            $filePath = match (true) {
                in_array($type, LearningMaterial::LINK_TYPES, true) => null,
                $type === 'interactive' => $this->restoreInteractivePackage($data['file_path'] ?? null),
                default => $this->restoreAsset($data['file_path'] ?? null),
            };
            $externalUrl = $data['external_url'] ?? null;

            LearningMaterial::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'sort_order' => $order + 1,
                    'description' => $data['description'] ?? null,
                    'file_path' => $filePath,
                    'external_url' => $externalUrl,
                    'file_type' => $filePath || $externalUrl ? $type : null,
                    'duration_seconds' => $data['duration_seconds'] ?? null,
                    'original_name' => $data['original_name'] ?? null,
                ]
            );
        }
    }

    /**
     * Every exported material, in order. Also reads the older single-material
     * learning_material.json export.
     */
    protected function exportedMaterials(): array
    {
        $listPath = database_path('seeders/data/learning_materials.json');
        $legacyPath = database_path('seeders/data/learning_material.json');

        if (File::exists($listPath)) {
            return json_decode(File::get($listPath), true) ?? [];
        }

        if (File::exists($legacyPath)) {
            $single = json_decode(File::get($legacyPath), true);

            return $single ? [$single] : [];
        }

        return [];
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

    /**
     * Copy every file of an exported interactive package back into the
     * public disk, keeping files that are already there.
     */
    protected function restoreInteractivePackage(?string $indexPath): ?string
    {
        $root = InteractivePackage::packageRoot($indexPath, 'learning-materials/interactive');
        $sourceRoot = $root ? database_path('seeders/assets/'.$root) : null;

        if (! $sourceRoot || ! File::isDirectory($sourceRoot)) {
            return null;
        }

        foreach (File::allFiles($sourceRoot) as $file) {
            $relativePath = $root.'/'.str_replace('\\', '/', $file->getRelativePathname());

            if (! Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->put($relativePath, File::get($file->getPathname()));
            }
        }

        return Storage::disk('public')->exists($indexPath) ? $indexPath : null;
    }
}
