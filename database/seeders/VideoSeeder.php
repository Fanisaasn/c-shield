<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
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
    }
}
