<?php

namespace Database\Seeders;

use App\Models\AssessmentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AssessmentSeeder extends Seeder
{
    /**
     * Seed assessment categories, questions, and options used by the
     * Pre-Assessment / Post-Assessment self-assessment feature.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Keamanan Kata Sandi',
                'description' => 'Pemahaman mengenai praktik pembuatan dan pengelolaan kata sandi yang aman.',
                'questions' => [
                    [
                        'question' => 'Manakah contoh kata sandi yang paling aman untuk digunakan?',
                        'options' => [
                            ['text' => '123456', 'correct' => false],
                            ['text' => 'P@ssw0rd!2026', 'correct' => true],
                            ['text' => 'namasaya', 'correct' => false],
                            ['text' => 'qwerty', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Apa langkah yang sebaiknya dilakukan agar akun tetap aman?',
                        'options' => [
                            ['text' => 'Menggunakan kata sandi yang sama di semua akun', 'correct' => false],
                            ['text' => 'Mengaktifkan autentikasi dua faktor (2FA)', 'correct' => true],
                            ['text' => 'Menuliskan kata sandi di kertas dan menempelkannya di monitor', 'correct' => false],
                            ['text' => 'Membagikan kata sandi kepada rekan kerja', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Kapan sebaiknya sebuah kata sandi penting segera diganti?',
                        'options' => [
                            ['text' => 'Tidak perlu pernah diganti', 'correct' => false],
                            ['text' => 'Secara berkala dan segera setelah ada indikasi kebocoran', 'correct' => true],
                            ['text' => 'Hanya jika lupa', 'correct' => false],
                            ['text' => 'Setahun sekali tanpa alasan tertentu', 'correct' => false],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Phishing & Rekayasa Sosial',
                'description' => 'Kemampuan mengenali dan merespons upaya penipuan digital.',
                'questions' => [
                    [
                        'question' => 'Apa yang dimaksud dengan phishing?',
                        'options' => [
                            ['text' => 'Aktivitas menjaga jaringan dari virus', 'correct' => false],
                            ['text' => 'Upaya penipuan untuk mencuri data pribadi melalui email/pesan palsu', 'correct' => true],
                            ['text' => 'Proses backup data ke cloud', 'correct' => false],
                            ['text' => 'Aplikasi antivirus resmi', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Tindakan yang tepat saat menerima pesan mencurigakan yang meminta data pribadi adalah...',
                        'options' => [
                            ['text' => 'Langsung membalas dengan data yang diminta', 'correct' => false],
                            ['text' => 'Mengklik semua tautan untuk memastikan', 'correct' => false],
                            ['text' => 'Tidak mengklik tautan dan melaporkan/menghapus pesan tersebut', 'correct' => true],
                            ['text' => 'Meneruskan ke rekan kerja lain tanpa verifikasi', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Ciri umum pesan phishing adalah...',
                        'options' => [
                            ['text' => 'Menggunakan bahasa formal dari instansi resmi', 'correct' => false],
                            ['text' => 'Mendesak korban bertindak cepat dengan ancaman atau iming-iming', 'correct' => true],
                            ['text' => 'Tidak memiliki tautan apa pun', 'correct' => false],
                            ['text' => 'Dikirim dari alamat email resmi yang telah terverifikasi', 'correct' => false],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Keamanan Perangkat & Jaringan',
                'description' => 'Kesadaran terhadap risiko teknis pada perangkat dan jaringan yang digunakan sehari-hari.',
                'questions' => [
                    [
                        'question' => 'Mengapa penting untuk selalu memperbarui (update) sistem operasi dan aplikasi?',
                        'options' => [
                            ['text' => 'Untuk menambah ruang penyimpanan', 'correct' => false],
                            ['text' => 'Untuk menutup celah keamanan yang telah ditemukan', 'correct' => true],
                            ['text' => 'Agar tampilan aplikasi lebih menarik', 'correct' => false],
                            ['text' => 'Tidak memberikan manfaat apa pun', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Apa risiko menggunakan Wi-Fi publik tanpa proteksi tambahan?',
                        'options' => [
                            ['text' => 'Tidak ada risiko sama sekali', 'correct' => false],
                            ['text' => 'Data dapat disadap oleh pihak yang tidak bertanggung jawab', 'correct' => true],
                            ['text' => 'Baterai perangkat menjadi lebih awet', 'correct' => false],
                            ['text' => 'Koneksi otomatis menjadi lebih cepat', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Apa fungsi utama antivirus pada sebuah perangkat?',
                        'options' => [
                            ['text' => 'Mempercepat kinerja perangkat', 'correct' => false],
                            ['text' => 'Mendeteksi dan mencegah perangkat lunak berbahaya (malware)', 'correct' => true],
                            ['text' => 'Menghapus seluruh data secara otomatis', 'correct' => false],
                            ['text' => 'Mengganti kata sandi secara berkala', 'correct' => false],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Privasi & Perlindungan Data',
                'description' => 'Pemahaman mengenai pentingnya menjaga kerahasiaan data pribadi.',
                'questions' => [
                    [
                        'question' => 'Data pribadi yang sebaiknya tidak dibagikan sembarangan di media sosial adalah...',
                        'options' => [
                            ['text' => 'Nomor KTP, alamat rumah, dan data keuangan', 'correct' => true],
                            ['text' => 'Nama akun media sosial', 'correct' => false],
                            ['text' => 'Judul foto profil', 'correct' => false],
                            ['text' => 'Nama aplikasi yang sedang digunakan', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Sebelum menginstal sebuah aplikasi, sebaiknya pengguna...',
                        'options' => [
                            ['text' => 'Langsung menyetujui semua izin akses tanpa membaca', 'correct' => false],
                            ['text' => 'Memeriksa sumber aplikasi dan izin akses yang diminta', 'correct' => true],
                            ['text' => 'Menginstal dari sumber tidak resmi agar gratis', 'correct' => false],
                            ['text' => 'Mengabaikan kebijakan privasi aplikasi', 'correct' => false],
                        ],
                    ],
                ],
            ],
            [
                'name' => 'Etika & Perilaku Digital Aman',
                'description' => 'Kesadaran akan tanggung jawab dan etika dalam berinteraksi di ruang siber.',
                'questions' => [
                    [
                        'question' => 'Apa yang sebaiknya dilakukan jika menemukan informasi yang belum jelas kebenarannya (hoaks)?',
                        'options' => [
                            ['text' => 'Langsung membagikan ke banyak grup', 'correct' => false],
                            ['text' => 'Memverifikasi kebenaran informasi sebelum membagikannya', 'correct' => true],
                            ['text' => 'Menambahkan judul yang lebih provokatif', 'correct' => false],
                            ['text' => 'Mengabaikan tanpa memeriksa lebih lanjut', 'correct' => false],
                        ],
                    ],
                    [
                        'question' => 'Mengapa penting melaporkan insiden keamanan siber yang dialami?',
                        'options' => [
                            ['text' => 'Agar dapat ditindaklanjuti dan mencegah dampak yang lebih luas', 'correct' => true],
                            ['text' => 'Karena wajib menurut hukum tanpa manfaat lain', 'correct' => false],
                            ['text' => 'Agar mendapatkan hadiah', 'correct' => false],
                            ['text' => 'Tidak ada gunanya melaporkan', 'correct' => false],
                        ],
                    ],
                ],
            ],
        ];

        foreach ($categories as $categoryOrder => $categoryData) {
            $category = AssessmentCategory::query()->updateOrCreate(
                ['slug' => Str::slug($categoryData['name'])],
                [
                    'name' => $categoryData['name'],
                    'description' => $categoryData['description'],
                    'order' => $categoryOrder + 1,
                ]
            );

            foreach ($categoryData['questions'] as $questionOrder => $questionData) {
                $question = $category->questions()->updateOrCreate(
                    ['question' => $questionData['question']],
                    ['order' => $questionOrder + 1]
                );

                foreach ($questionData['options'] as $optionOrder => $optionData) {
                    $question->options()->updateOrCreate(
                        ['option_text' => $optionData['text']],
                        [
                            'is_correct' => $optionData['correct'],
                            'order' => $optionOrder + 1,
                        ]
                    );
                }
            }
        }
    }
}
