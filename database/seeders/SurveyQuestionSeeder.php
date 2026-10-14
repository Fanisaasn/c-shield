<?php

namespace Database\Seeders;

use App\Models\SurveyQuestion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class SurveyQuestionSeeder extends Seeder
{
    /**
     * Seed the standard public-service satisfaction survey statements
     * (mengacu pada unsur pelayanan publik Permenpan RB No. 14 Tahun 2017),
     * answered on a 4-point agreement scale.
     */
    public function run(): void
    {
        $questions = [
            'Informasi pelayanan tersedia melalui media elektronik maupun nonelektronik',
            'Persyaratan yang diminta sesuai dengan yang diinformasikan',
            'Prosedur pelayanan mudah dipahami dan diikuti',
            'Waktu pelayanan sesuai dengan yang telah dijanjikan',
            'Petugas/pengelola memiliki kompetensi yang memadai dalam memberikan pelayanan',
            'Petugas/pengelola bersikap sopan dan ramah dalam memberikan pelayanan',
            'Pengaduan atau masukan pengguna layanan ditangani dengan baik',
            'Sarana dan prasarana yang tersedia mendukung kenyamanan pengguna layanan',
            'Secara keseluruhan saya puas dengan pelayanan yang diberikan',
        ];

        // Pertanyaan yang diubah lewat admin, hasil `php artisan content:export-seeders`.
        $jsonPath = database_path('seeders/data/survey_questions.json');

        if (File::exists($jsonPath)) {
            $exported = json_decode(File::get($jsonPath), true);
            $questions = $exported ? array_column($exported, 'question') : $questions;
        }

        foreach ($questions as $order => $question) {
            SurveyQuestion::query()->updateOrCreate(
                ['question' => $question],
                ['sort_order' => $order]
            );
        }
    }
}
