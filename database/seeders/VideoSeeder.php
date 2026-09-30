<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoSeeder extends Seeder
{
    /**
     * Seed videos. If database/seeders/data/videos.json exists (generated
     * via `php artisan content:export-seeders`), the real videos added by
     * the team are seeded from there, files included. Otherwise falls
     * back to a few sample placeholder videos.
     */
    public function run(): void
    {
        $this->seedInteractiveVideo();

        $jsonPath = database_path('seeders/data/videos.json');

        if (File::exists($jsonPath)) {
            $this->seedFromExport($jsonPath);

            return;
        }

        $this->seedSampleVideos();
        $this->seedUploadedFileDemoVideo();
    }

    protected function seedFromExport(string $jsonPath): void
    {
        $videos = json_decode(File::get($jsonPath), true) ?? [];

        foreach ($videos as $data) {
            Video::query()->updateOrCreate(
                ['slug' => $data['slug']],
                [
                    'title' => $data['title'],
                    'description' => $data['description'] ?? null,
                    'video_url' => $data['video_url'] ?? null,
                    'video_path' => $this->restoreAsset($data['video_path'] ?? null),
                    'thumbnail' => $this->restoreAsset($data['thumbnail'] ?? null),
                    'is_published' => $data['is_published'] ?? true,
                    'published_at' => $data['published_at'] ?? null,
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

    protected function seedInteractiveVideo(): void
    {
        Video::query()->updateOrCreate(
            ['slug' => 'jaga-data-jaga-diri'],
            [
                'title' => 'Jaga Data, Jaga Diri',
                'description' => 'Video interaktif ini membahas perilaku aman dalam menggunakan perangkat, akun, dan informasi digital. Pengguna akan menghadapi situasi keamanan dan menentukan apakah perilaku tersebut aman atau berisiko.',
                'type' => 'interactive',
                'video_url' => 'interactive://jaga-data-jaga-diri',
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }

    protected function seedSampleVideos(): void
    {
        $videos = [
            [
                'title' => 'Dasar-Dasar Keamanan Siber untuk Pemula',
                'description' => 'Pengenalan konsep dasar keamanan siber dan mengapa hal ini penting bagi setiap pengguna internet.',
                'video_url' => 'https://www.youtube.com/watch?v=CONTOH-VIDEO-01',
            ],
            [
                'title' => 'Cara Mengenali dan Menghindari Serangan Phishing',
                'description' => 'Simulasi dan contoh nyata pesan phishing beserta cara mengidentifikasinya.',
                'video_url' => 'https://www.youtube.com/watch?v=CONTOH-VIDEO-02',
            ],
            [
                'title' => 'Melindungi Data Pribadi di Media Sosial',
                'description' => 'Pengaturan privasi yang perlu diperhatikan saat menggunakan media sosial.',
                'video_url' => 'https://www.youtube.com/watch?v=CONTOH-VIDEO-03',
            ],
        ];

        foreach ($videos as $order => $data) {
            Video::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'video_url' => $data['video_url'],
                    'is_published' => true,
                    'published_at' => now()->subDays(count($videos) - $order),
                ]
            );
        }
    }

    /**
     * Seed one example video that is uploaded directly to C-SHIELD (as
     * opposed to embedded from an external video_url), to demonstrate a
     * self-hosted video actually playing on the site.
     */
    protected function seedUploadedFileDemoVideo(): void
    {
        $title = 'Contoh Video: File Diputar Langsung di C-SHIELD';
        $slug = Str::slug($title);

        $video = Video::query()->where('slug', $slug)->first();
        if ($video && $video->video_path && Storage::disk('public')->exists($video->video_path)) {
            return;
        }

        $assetPath = __DIR__.'/assets/demo-video.webm';
        if (! is_file($assetPath)) {
            return;
        }

        $videoPath = 'videos/demo-uploaded-file.webm';
        Storage::disk('public')->put($videoPath, file_get_contents($assetPath));

        Video::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'title' => $title,
                'description' => 'Contoh video yang berkasnya diunggah langsung ke C-SHIELD (bukan link Instagram/YouTube), sehingga diputar dari server sendiri. Untuk video yang diambil dari Instagram/YouTube, isi field "Link video" saja di form admin agar otomatis tampil sebagai embed yang diputar dari sumbernya.',
                'video_path' => $videoPath,
                'is_published' => true,
                'published_at' => now(),
            ]
        );
    }
}
