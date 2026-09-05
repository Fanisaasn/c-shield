<?php

namespace Database\Seeders;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentCategory;
use App\Models\AssessmentUser;
use Illuminate\Database\Seeder;

class AssessmentResultDemoSeeder extends Seeder
{
    private const RESPONDENTS_PER_CATEGORY = 10;

    private const GENDERS = ['Laki-laki', 'Perempuan'];

    private const EDUCATIONS = ['SD/Sederajat', 'SMP/Sederajat', 'SMA/SMK/Sederajat', 'Diploma (D1-D4)', 'S1', 'S2', 'S3'];

    private const DOMICILES = ['Cimahi Utara', 'Cimahi Tengah', 'Cimahi Selatan'];

    private const OCCUPATIONS = ['Pelajar', 'Mahasiswa', 'ASN/Pegawai Pemerintah', 'Karyawan Swasta', 'Wiraswasta', 'Tidak Bekerja', 'Lainnya'];

    private const NAMES = [
        'Ahmad R.', 'Siti N.', 'Budi S.', 'Dewi A.', 'Rian P.', 'Maya K.', 'Fajar W.', 'Indah L.',
        'Yusuf H.', 'Rina D.', 'Agus T.', 'Nur F.', 'Dedi M.', 'Sri W.', 'Hendra Y.', 'Lestari B.',
        'Fitri A.', 'Bayu S.', 'Wulan C.', 'Iqbal R.', 'Putri M.', 'Reza F.', 'Anita S.', 'Doni P.',
        'Sinta W.', 'Arif N.', 'Melati D.', 'Taufik H.', 'Rahma S.', 'Wahyu K.', 'Diana P.', 'Eko S.',
        'Nina R.', 'Gilang A.', 'Ratih D.', 'Hadi P.', 'Salma N.', 'Ilham F.', 'Yuni A.', 'Firman B.',
        'Ayu L.', 'Rizky T.', 'Vina S.', 'Andra P.', 'Citra M.', 'Aldi R.', 'Tania W.', 'Fauzi H.',
        'Mega S.', 'Rendi A.',
    ];

    /**
     * Seed example respondents & completed Pre-Assessment attempts (spread
     * across every theme, gender, age, education, and domicile) purely so
     * the aggregate statistics charts on the homepage and admin dashboard
     * have something to display. Safe to remove later with
     * `php artisan tinker` (see the README note in this class) once real
     * respondent data exists.
     */
    public function run(): void
    {
        $categories = AssessmentCategory::with(['questions.options'])->orderBy('order')->get();

        if ($categories->isEmpty()) {
            $this->command?->warn('Tidak ada kategori assessment. Jalankan AssessmentSeeder terlebih dahulu.');

            return;
        }

        $nameIndex = 0;

        foreach ($categories as $category) {
            for ($i = 0; $i < self::RESPONDENTS_PER_CATEGORY; $i++) {
                $name = self::NAMES[$nameIndex % count(self::NAMES)] . ' ' . (intdiv($nameIndex, count(self::NAMES)) + 1);
                $nameIndex++;

                $user = AssessmentUser::create([
                    'name' => $name,
                    'phone_last_digits' => str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT),
                    'gender' => self::GENDERS[array_rand(self::GENDERS)],
                    'age' => random_int(15, 65),
                    'education' => self::EDUCATIONS[array_rand(self::EDUCATIONS)],
                    'domicile' => self::DOMICILES[array_rand(self::DOMICILES)],
                    'occupation_status' => self::OCCUPATIONS[array_rand(self::OCCUPATIONS)],
                ]);

                $attempt = AssessmentAttempt::create([
                    'assessment_user_id' => $user->id,
                    'type' => 'pre',
                    'started_at' => now()->subMinutes(random_int(5, 15)),
                ]);

                // Vary how many questions each respondent answers correctly
                // so results spread across all five awareness levels.
                $correctRate = [0.1, 0.3, 0.5, 0.7, 0.9][random_int(0, 4)];

                foreach ($category->questions as $question) {
                    $answerCorrectly = (mt_rand() / mt_getrandmax()) < $correctRate;
                    $option = $answerCorrectly
                        ? $question->options->firstWhere('is_correct', true)
                        : $question->options->firstWhere('is_correct', false);
                    $option ??= $question->options->first();

                    AssessmentAnswer::create([
                        'assessment_attempt_id' => $attempt->id,
                        'assessment_question_id' => $question->id,
                        'assessment_option_id' => $option->id,
                        'is_correct' => $option->is_correct,
                    ]);
                }

                $attempt->finalizeScore();
            }
        }

        $this->command?->info('Berhasil membuat ' . ($categories->count() * self::RESPONDENTS_PER_CATEGORY) . ' contoh hasil assessment.');
    }
}
