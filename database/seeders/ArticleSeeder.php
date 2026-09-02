<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ArticleSeeder extends Seeder
{
    /**
     * Seed sample educational articles.
     */
    public function run(): void
    {
        $articles = [
            [
                'title' => 'Membuat dan Mengelola Kata Sandi yang Kuat',
                'excerpt' => 'Panduan dasar menyusun kata sandi yang sulit ditebak sekaligus mudah diingat.',
                'content' => "Kata sandi merupakan lapisan pertahanan pertama dalam melindungi akun digital. Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol dengan panjang minimal 12 karakter, hindari penggunaan data pribadi seperti tanggal lahir, serta jangan menggunakan kata sandi yang sama pada beberapa layanan sekaligus. Untuk keamanan tambahan, aktifkan autentikasi dua faktor (2FA) pada akun-akun penting seperti email dan layanan pemerintahan digital.",
            ],
            [
                'title' => 'Mengenali Ciri-Ciri Email dan Pesan Phishing',
                'excerpt' => 'Kenali pola umum yang digunakan pelaku phishing agar tidak menjadi korban.',
                'content' => "Phishing adalah upaya penipuan yang menyamar sebagai pihak resmi untuk mencuri data pribadi. Waspadai pesan yang mendesak tindakan segera, meminta data sensitif, menyertakan tautan mencurigakan, atau berasal dari alamat pengirim yang tidak sesuai dengan domain resmi instansi. Selalu verifikasi informasi melalui kanal resmi sebelum mengklik tautan atau memberikan data apa pun.",
            ],
            [
                'title' => 'Tips Aman Menggunakan Wi-Fi Publik',
                'excerpt' => 'Langkah sederhana menjaga data tetap aman saat terhubung ke jaringan publik.',
                'content' => "Jaringan Wi-Fi publik rentan terhadap penyadapan data. Hindari mengakses layanan perbankan atau memasukkan data sensitif saat terhubung ke Wi-Fi publik tanpa proteksi tambahan seperti VPN. Pastikan juga fitur berbagi file pada perangkat dalam keadaan nonaktif ketika berada di jaringan yang tidak terpercaya.",
            ],
        ];

        foreach ($articles as $order => $data) {
            Article::query()->updateOrCreate(
                ['slug' => Str::slug($data['title'])],
                [
                    'title' => $data['title'],
                    'excerpt' => $data['excerpt'],
                    'content' => $data['content'],
                    'is_published' => true,
                    'published_at' => now()->subDays(count($articles) - $order),
                ]
            );
        }
    }
}
