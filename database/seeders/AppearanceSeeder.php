<?php

namespace Database\Seeders;

use App\Models\DashboardSlide;
use Illuminate\Database\Seeder;

class AppearanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultSlides = [
            [
                'title' => 'Cimahi Cyber Security Hub',
                'description' => 'Pusat informasi dan edukasi keamanan siber terpadu Kota Cimahi.',
                'image_path' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=2200&q=82',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'title' => 'Infrastruktur Keamanan Digital',
                'description' => 'Meningkatkan ketahanan sistem dan perlindungan data aparatur serta masyarakat.',
                'image_path' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=2200&q=82',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'title' => 'Edukasi & Kesadaran Siber',
                'description' => 'Tingkatkan kesadaran keamanan digital melalui materi interaktif dan self-assessment.',
                'image_path' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=2200&q=82',
                'sort_order' => 3,
                'is_active' => true,
            ],
        ];

        foreach ($defaultSlides as $slideData) {
            DashboardSlide::firstOrCreate(
                ['title' => $slideData['title']],
                $slideData
            );
        }
    }
}
