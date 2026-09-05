<?php

namespace Database\Seeders;

use App\Models\Flyer;
use Illuminate\Database\Seeder;

class FlyerSeeder extends Seeder
{
    /**
     * Seed sample flyers. The image field is left empty until an admin
     * uploads the actual flyer image through the admin panel.
     */
    public function run(): void
    {
        $flyers = [
            [
                'title' => 'Waspada Modus Penipuan Online Terbaru',
                'description' => 'Flyer sosialisasi mengenai modus penipuan digital yang marak terjadi di masyarakat.',
            ],
            [
                'title' => '5 Langkah Amankan Akun Media Sosial Anda',
                'description' => 'Flyer edukasi singkat mengenai pengamanan akun media sosial pribadi.',
            ],
            [
                'title' => 'Kenali Ciri Website dan Aplikasi Palsu',
                'description' => 'Flyer panduan mengenali situs dan aplikasi palsu yang berpotensi mencuri data.',
            ],
        ];

        foreach ($flyers as $order => $data) {
            Flyer::query()->updateOrCreate(
                ['title' => $data['title']],
                [
                    'description' => $data['description'],
                    'is_published' => true,
                    'published_at' => now()->subDays(count($flyers) - $order),
                ]
            );
        }
    }
}
