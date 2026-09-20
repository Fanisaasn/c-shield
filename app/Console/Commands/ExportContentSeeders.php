<?php

namespace App\Console\Commands;

use App\Models\Article;
use App\Models\Flyer;
use App\Models\Video;
use App\Models\Webinar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ExportContentSeeders extends Command
{
    protected $signature = 'content:export-seeders';

    protected $description = 'Ekspor data flyer, video, artikel, dan webinar dari database saat ini (beserta file gambar/videonya) ke database/seeders, supaya bisa dibagikan lewat git dan dimasukkan ulang lewat php artisan db:seed';

    protected string $dataPath;

    protected string $assetsPath;

    public function handle(): int
    {
        $this->dataPath = database_path('seeders/data');
        $this->assetsPath = database_path('seeders/assets');

        File::ensureDirectoryExists($this->dataPath);
        File::ensureDirectoryExists($this->assetsPath);

        $this->exportFlyers();
        $this->exportVideos();
        $this->exportArticles();
        $this->exportWebinars();

        $this->newLine();
        $this->info('Selesai. Sekarang jalankan: git add database/seeders && git commit && git push');

        return self::SUCCESS;
    }

    protected function exportFlyers(): void
    {
        $flyers = Flyer::with('images')->get()->map(function (Flyer $flyer) {
            return [
                'title' => $flyer->title,
                'description' => $flyer->description,
                'source_url' => $flyer->source_url,
                'is_published' => $flyer->is_published,
                'published_at' => optional($flyer->published_at)->toDateTimeString(),
                'images' => $flyer->images->map(fn ($image) => [
                    'image' => $this->copyToAssets($image->image),
                    'sort_order' => $image->sort_order,
                ])->filter(fn ($image) => $image['image'] !== null)->values()->all(),
            ];
        })->all();

        $this->writeJson('flyers.json', $flyers);
        $this->info('Flyer: '.count($flyers).' data diekspor.');
    }

    protected function exportVideos(): void
    {
        $videos = Video::all()->map(function (Video $video) {
            return [
                'title' => $video->title,
                'slug' => $video->slug,
                'description' => $video->description,
                'video_url' => $video->video_url,
                'video_path' => $this->copyToAssets($video->video_path),
                'thumbnail' => $this->copyToAssets($video->thumbnail),
                'is_published' => $video->is_published,
                'published_at' => optional($video->published_at)->toDateTimeString(),
            ];
        })->all();

        $this->writeJson('videos.json', $videos);
        $this->info('Video: '.count($videos).' data diekspor.');
    }

    protected function exportArticles(): void
    {
        $articles = Article::all()->map(function (Article $article) {
            return [
                'title' => $article->title,
                'slug' => $article->slug,
                'excerpt' => $article->excerpt,
                'content' => $article->content,
                'source_url' => $article->source_url,
                'cover_image' => $this->copyToAssets($article->cover_image),
                'is_published' => $article->is_published,
                'published_at' => optional($article->published_at)->toDateTimeString(),
            ];
        })->all();

        $this->writeJson('articles.json', $articles);
        $this->info('Artikel: '.count($articles).' data diekspor.');
    }

    protected function exportWebinars(): void
    {
        $webinars = Webinar::all()->map(function (Webinar $webinar) {
            return [
                'title' => $webinar->title,
                'description' => $webinar->description,
                'speaker' => $webinar->speaker,
                'webinar_date' => optional($webinar->webinar_date)->toDateTimeString(),
                'platform' => $webinar->platform,
                'registration_url' => $webinar->registration_url,
                'poster_image' => $this->copyToAssets($webinar->poster_image),
                'is_published' => $webinar->is_published,
            ];
        })->all();

        $this->writeJson('webinars.json', $webinars);
        $this->info('Webinar: '.count($webinars).' data diekspor.');
    }

    /**
     * Copy an uploaded file from the public disk into the git-tracked
     * database/seeders/assets folder, mirroring its relative path, so the
     * actual file (not just its DB path) is committed and can be restored
     * on another machine by the seeder. PNG/JPEG images are downscaled and
     * recompressed (same format and extension) to keep the git repo small;
     * the original file in storage/app/public is never modified.
     */
    protected function copyToAssets(?string $relativePath): ?string
    {
        if (! $relativePath || Str::startsWith($relativePath, ['http://', 'https://'])) {
            return $relativePath;
        }

        if (! Storage::disk('public')->exists($relativePath)) {
            return null;
        }

        $source = Storage::disk('public')->path($relativePath);
        $destination = $this->assetsPath.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
        File::ensureDirectoryExists(dirname($destination));

        if (! $this->compressImage($source, $destination)) {
            File::copy($source, $destination);
        }

        return $relativePath;
    }

    /**
     * Downscale (max 1000px wide, never upscaled) and recompress a PNG or
     * JPEG into $destination. Returns false for any other file type or if
     * GD cannot read the file, so the caller can fall back to a plain copy.
     */
    protected function compressImage(string $source, string $destination): bool
    {
        $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

        $image = match ($extension) {
            'png' => @imagecreatefrompng($source),
            'jpg', 'jpeg' => @imagecreatefromjpeg($source),
            default => false,
        };

        if (! $image) {
            return false;
        }

        $maxWidth = 1000;
        $width = imagesx($image);
        $height = imagesy($image);

        if ($width > $maxWidth) {
            $newHeight = (int) round($height * ($maxWidth / $width));
            $resized = imagecreatetruecolor($maxWidth, $newHeight);
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
            imagedestroy($image);
            $image = $resized;
        }

        if ($extension === 'png') {
            imagetruecolortopalette($image, false, 255);
            $saved = imagepng($image, $destination, 9);
        } else {
            $saved = imagejpeg($image, $destination, 75);
        }

        imagedestroy($image);

        return $saved;
    }

    protected function writeJson(string $filename, array $data): void
    {
        File::put(
            $this->dataPath.DIRECTORY_SEPARATOR.$filename,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
