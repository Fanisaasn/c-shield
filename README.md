<h1 align="center">🛡️ C-SHIELD</h1>
<p align="center"><strong>Cimahi Cyber Security Hub & Awareness Field</strong></p>

<p align="center">
<img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?logo=php&logoColor=white" alt="PHP">
<img src="https://img.shields.io/badge/Laravel-12-FF2D20?logo=laravel&logoColor=white" alt="Laravel">
<img src="https://img.shields.io/badge/MySQL-8-4479A1?logo=mysql&logoColor=white" alt="MySQL">
<img src="https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?logo=tailwindcss&logoColor=white" alt="Tailwind CSS">
<img src="https://img.shields.io/badge/Chart.js-4-FF6384?logo=chartdotjs&logoColor=white" alt="Chart.js">
</p>

## Tentang C-SHIELD

**C-SHIELD** adalah platform edukasi dan peningkatan kesadaran keamanan siber (*cyber security awareness*) untuk masyarakat Kota Cimahi. Aplikasi ini dikembangkan sebagai proyek **Kerja Praktik** di **Dinas Komunikasi dan Informatika (Diskominfo) Kota Cimahi**.

Melalui C-SHIELD, masyarakat dapat:

- Membaca **Artikel** seputar keamanan siber.
- Menonton **Video** edukasi.
- Melihat **Flyer** kampanye keamanan digital.
- Mengikuti informasi **Webinar** dan mendaftar melalui tautan pendaftaran eksternal.
- Mengukur tingkat pemahaman keamanan siber melalui **Self Assessment** (Pre-Assessment & Post-Assessment) beserta perbandingan hasilnya.

## Aktor Sistem

| Aktor | Akses | Keterangan |
|---|---|---|
| **User (Masyarakat)** | Halaman publik | Tidak perlu login dan tidak memiliki akun. |
| **Administrator** | `/admin/*` | Login melalui `/admin/login`; semua rute admin dilindungi guard `auth:admin`. |

## Fitur

### Halaman Publik

| Fitur | Rute | Deskripsi |
|---|---|---|
| Beranda | `/` | Ringkasan konten dan diagram hasil Self Assessment. |
| Artikel | `/artikel` | Daftar dan detail artikel keamanan siber. |
| Video | `/video` | Daftar dan detail video edukasi. |
| Flyer | `/flyer` | Galeri flyer kampanye keamanan digital. |
| Webinar | `/webinar` | Info webinar dengan tombol **Register Now** ke tautan pendaftaran eksternal. |
| Self Assessment | `/self-assessment` | Kuis Pre/Post-Assessment dan perbandingan hasil. |

### Alur Self Assessment

1. Pilih **Pre-Assessment** di halaman awal (Post-Assessment terkunci sampai Pre-Assessment selesai).
2. Isi **identitas responden** (nama/inisial, 4 digit terakhir nomor HP, jenis kelamin, usia, pendidikan terakhir, domisili, status/pekerjaan).
3. Kerjakan **Pre-Assessment** → lihat hasil awal.
4. Halaman **pengingat belajar** yang mengarahkan ke Video, Artikel, dan Flyer.
5. Kerjakan **Post-Assessment** → lihat hasil akhir.
6. Lihat **perbandingan skor Pre vs Post**.

Skor berada pada rentang **0–100** dengan lima kategori:

| Skor | Kategori |
|---|---|
| 0–20 | Sangat Rendah |
| 21–40 | Rendah |
| 41–60 | Cukup |
| 61–80 | Baik |
| 81–100 | Sangat Baik |

### Panel Administrator

- Dashboard ringkasan data.
- Kelola Artikel, Video, Flyer, dan Webinar (CRUD).
- Kelola soal Self Assessment.
- Kelola pertanyaan survei.
- Laporan hasil assessment responden.

## Teknologi

- **Backend:** PHP 8.2+, Laravel 12
- **Database:** MySQL
- **Frontend:** Blade, Tailwind CSS 4, Vite
- **Visualisasi:** Chart.js
- **Editor konten:** Trix

## Instalasi

### Prasyarat

- PHP ≥ 8.2 dan Composer
- Node.js ≥ 18 dan npm
- MySQL (misalnya melalui XAMPP/Laragon)

### Langkah

```bash
# 1. Clone repository
git clone https://github.com/Fanisaasn/c-shield.git
cd c-shield

# 2. Install dependency
composer install
npm install

# 3. Salin file environment dan generate key
cp .env.example .env
php artisan key:generate
```

Buat database bernama `cshield_db` di MySQL, lalu sesuaikan konfigurasi di `.env` bila perlu:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=cshield_db
DB_USERNAME=root
DB_PASSWORD=
```

```bash
# 4. Migrasi dan isi data awal
php artisan migrate --seed

# 5. Hubungkan folder storage (untuk gambar/video yang diunggah)
php artisan storage:link

# 6. Jalankan aplikasi (dua terminal)
npm run dev
php artisan serve
```

Buka aplikasi di **http://127.0.0.1:8000**.

### Akun Administrator Default

| Email | Password |
|---|---|
| `admin@cshield.test` | `password` |

> ⚠️ Segera ganti password default sebelum aplikasi digunakan di lingkungan produksi.

## Struktur Direktori Utama

```
app/
├── Http/Controllers/        # Controller halaman publik
│   └── Admin/               # Controller panel administrator
└── Models/                  # Model Eloquent (Article, Video, Flyer, Webinar, Assessment*, Survey*)
database/
├── migrations/              # Skema tabel
└── seeders/                 # Data awal (admin, konten, soal assessment)
resources/views/
├── user/                    # Tampilan halaman publik
└── admin/                   # Tampilan panel administrator
routes/web.php               # Definisi rute
```

## Tim Pengembang

Proyek Kerja Praktik — Diskominfo Kota Cimahi.

## Lisensi

Proyek ini dikembangkan untuk keperluan Kerja Praktik di Diskominfo Kota Cimahi. Framework Laravel dilisensikan di bawah [MIT license](https://opensource.org/licenses/MIT).
