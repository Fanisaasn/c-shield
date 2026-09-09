<?php

namespace Database\Seeders;

use App\Models\Flyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

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

        $this->seedSlideDemoFlyer();
    }

    /**
     * Seed one example flyer with several slide images, to demonstrate the
     * Instagram-style multi-image carousel on the public flyer detail page.
     * The slide images are generated on the fly with GD so the demo works
     * without needing real design assets checked into the repository.
     */
    protected function seedSlideDemoFlyer(): void
    {
        $flyer = Flyer::query()->updateOrCreate(
            ['title' => 'Contoh Flyer Multi-Slide: Kenali 3 Ancaman Siber Umum'],
            [
                'description' => "Contoh flyer dengan beberapa gambar yang bisa digeser (slide) seperti Instagram. Geser untuk melihat 3 ancaman siber yang paling umum ditemui.",
                'is_published' => true,
                'published_at' => now(),
            ]
        );

        if ($flyer->images()->exists()) {
            return;
        }

        $slides = [
            [
                'file' => 'demo-slide-1.png',
                'bg' => '#0B2545',
                'badge' => 'CONTOH FLYER',
                'title' => ['Kenali 3', 'Ancaman Siber', 'yang Umum'],
                'body' => ['Geser gambar ini ke', 'kiri untuk melihat', 'penjelasannya ->'],
            ],
            [
                'file' => 'demo-slide-2.png',
                'bg' => '#1E5AA8',
                'badge' => 'ANCAMAN 1',
                'title' => ['Phishing'],
                'body' => ['Email atau pesan palsu', 'yang menyamar sebagai', 'pihak resmi untuk', 'mencuri data Anda.'],
            ],
            [
                'file' => 'demo-slide-3.png',
                'bg' => '#0B2545',
                'badge' => 'ANCAMAN 2',
                'title' => ['Malware'],
                'body' => ['Perangkat lunak berbahaya', 'yang menyusup, merusak,', 'atau mencuri data', 'dari perangkat Anda.'],
            ],
            [
                'file' => 'demo-slide-4.png',
                'bg' => '#2AB7CA',
                'badge' => 'ANCAMAN 3',
                'title' => ['Social', 'Engineering'],
                'body' => ['Manipulasi psikologis agar', 'korban tanpa sadar', 'memberi informasi rahasia', 'atau akses ke akunnya.'],
                'dark' => true,
            ],
        ];

        foreach ($slides as $index => $slide) {
            $path = $this->generateSlideImage(
                $slide['file'],
                $slide['bg'],
                $slide['badge'],
                $slide['title'],
                $slide['body'],
                $index + 1,
                count($slides),
                ! empty($slide['dark'])
            );

            $flyer->images()->create([
                'image' => $path,
                'sort_order' => $index,
            ]);
        }
    }

    /**
     * Generate one square placeholder slide image (background color, badge,
     * heading lines, body lines, and a dot progress indicator) using GD.
     */
    protected function generateSlideImage(
        string $filename,
        string $bgHex,
        string $badge,
        array $titleLines,
        array $bodyLines,
        int $slideNumber,
        int $slideCount,
        bool $dark = false
    ): string {
        $size = 800;
        $image = imagecreatetruecolor($size, $size);

        [$r, $g, $b] = array_map('hexdec', str_split(ltrim($bgHex, '#'), 2));
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));

        $textColor = $dark ? imagecolorallocate($image, 11, 37, 69) : imagecolorallocate($image, 255, 255, 255);
        $mutedColor = $dark ? imagecolorallocate($image, 11, 37, 69) : imagecolorallocate($image, 214, 226, 240);
        $badgeBg = $dark ? imagecolorallocate($image, 11, 37, 69) : imagecolorallocate($image, 42, 183, 202);
        $badgeText = imagecolorallocate($image, 255, 255, 255);

        $font = $this->resolveFont();

        // Badge pill.
        $badgeWidth = 80 + (strlen($badge) * 13);
        imagefilledrectangle($image, 60, 60, min($badgeWidth, $size - 60), 108, $badgeBg);
        $this->drawLine($image, $font, $badge, 78, 88, 15, $badgeText);

        // Title (kept well above the vertical-center band, where the
        // carousel's prev/next buttons sit, so generated text never
        // collides with them).
        $y = 210;
        foreach ($titleLines as $line) {
            $this->drawLine($image, $font, $line, 60, $y, 34, $textColor);
            $y += 56;
        }

        // Body (kept well below the vertical-center band).
        $y = 520;
        foreach ($bodyLines as $line) {
            $this->drawLine($image, $font, $line, 60, $y, 18, $mutedColor);
            $y += 32;
        }

        // Slide dot indicator (bottom center).
        $dotY = $size - 70;
        $dotSpacing = 24;
        $startX = ($size / 2) - (($slideCount - 1) * $dotSpacing / 2);
        for ($i = 0; $i < $slideCount; $i++) {
            $dotColor = ($i === $slideNumber - 1) ? $textColor : $mutedColor;
            imagefilledellipse($image, (int) ($startX + $i * $dotSpacing), $dotY, 12, 12, $dotColor);
        }

        // C-SHIELD watermark (bottom left).
        $this->drawLine($image, $font, 'C-SHIELD', 60, $size - 40, 14, $mutedColor);

        ob_start();
        imagepng($image);
        $binary = ob_get_clean();
        imagedestroy($image);

        $path = 'flyers/' . $filename;
        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    /**
     * Draw one line of text using a TrueType font when available, falling
     * back to GD's built-in bitmap font so the seeder still works on
     * environments without system fonts installed.
     */
    protected function drawLine($image, ?string $font, string $text, int $x, int $y, int $size, int $color): void
    {
        if ($font) {
            imagettftext($image, $size, 0, $x, $y, $color, $font, $text);

            return;
        }

        imagestring($image, 5, $x, $y - 14, $text, $color);
    }

    /**
     * Resolve a usable TrueType font on the host system, or null to fall
     * back to GD's built-in bitmap font.
     */
    protected function resolveFont(): ?string
    {
        static $resolved;

        if ($resolved !== null) {
            return $resolved ?: null;
        }

        $candidates = [
            'C:\\Windows\\Fonts\\arialbd.ttf',
            'C:\\Windows\\Fonts\\arial.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        ];

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return $resolved = $candidate;
            }
        }

        $resolved = false;

        return null;
    }
}
