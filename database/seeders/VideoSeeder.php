<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoSeeder extends Seeder
{
    /**
     * Seed sample educational videos. video_url values are placeholders
     * for the admin to replace with real links via the admin panel.
     */
    public function run(): void
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

        $this->seedUploadedFileDemoVideo();
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
