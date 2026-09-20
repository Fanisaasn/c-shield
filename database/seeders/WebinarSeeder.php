<?php

namespace Database\Seeders;

use App\Models\Webinar;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebinarSeeder extends Seeder
{
    /**
     * Seed webinars. If database/seeders/data/webinars.json exists
     * (generated via `php artisan content:export-seeders`), the real
     * webinars added by the team are seeded from there, poster images
     * included. Otherwise falls back to a few sample placeholder webinars.
     */
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/webinars.json');

        if (File::exists($jsonPath)) {
            $this->seedFromExport($jsonPath);

            return;
        }

        $this->seedSampleWebinars();
    }

    protected function seedFromExport(string $jsonPath): void
    {
        $webinars = json_decode(File::get($jsonPath), true) ?? [];

        foreach ($webinars as $data) {
            Webinar::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'] ?? null,
                    'speaker' => $data['speaker'] ?? null,
                    'webinar_date' => $data['webinar_date'] ?? null,
                    'platform' => $data['platform'] ?? null,
                    'registration_url' => $data['registration_url'] ?? null,
                    'poster_image' => $this->restoreAsset($data['poster_image'] ?? null),
                    'is_published' => $data['is_published'] ?? true,
                ]
            );
        }
    }

    /**
     * Copy a file that was exported into database/seeders/assets back into
     * the public disk, if it isn't already there.
     */
    protected function restoreAsset(?string $relativePath): ?string
    {
        if (! $relativePath || Str::startsWith($relativePath, ['http://', 'https://'])) {
            return $relativePath;
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

    protected function seedSampleWebinars(): void
    {
        $webinars = [
            [
                'title' => 'Membangun Budaya Keamanan Siber di Lingkungan Kerja',
                'description' => 'Webinar edukasi bagi ASN dan masyarakat Kota Cimahi mengenai pentingnya budaya keamanan siber sehari-hari.',
                'speaker' => 'Tim Keamanan Informasi Diskominfo Kota Cimahi',
                'webinar_date' => now()->addDays(14)->setTime(9, 0),
                'platform' => 'Zoom Meeting',
                'registration_url' => 'https://example.com/register/webinar-budaya-keamanan-siber',
            ],
            [
                'title' => 'Mengenali dan Menghindari Penipuan Digital',
                'description' => 'Sesi berbagi pengalaman dan studi kasus penipuan digital yang sering terjadi di masyarakat.',
                'speaker' => 'Narasumber Diskominfo Kota Cimahi',
                'webinar_date' => now()->addDays(30)->setTime(13, 30),
                'platform' => 'Google Meet',
                'registration_url' => 'https://example.com/register/webinar-penipuan-digital',
            ],
        ];

        foreach ($webinars as $data) {
            Webinar::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'speaker' => $data['speaker'],
                    'webinar_date' => $data['webinar_date'],
                    'platform' => $data['platform'],
                    'registration_url' => $data['registration_url'],
                    'is_published' => true,
                ]
            );
        }
    }
}
