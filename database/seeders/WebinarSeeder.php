<?php

namespace Database\Seeders;

use App\Models\Webinar;
use Illuminate\Database\Seeder;

class WebinarSeeder extends Seeder
{
    /**
     * Seed sample webinars. registration_url points to a placeholder
     * domain until the admin sets the real external registration link.
     */
    public function run(): void
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
