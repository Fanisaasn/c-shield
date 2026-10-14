<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PharData;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Extracts and removes interactive (HTML/CSS/JS) ZIP packages on the
 * public disk. Used by both Video Edukasi and Materi Belajar.
 */
class InteractivePackage
{
    public const MAX_SIZE_KB = 39936; // 39 MB compressed (below PHP's 40 MB POST limit)
    private const MAX_UNCOMPRESSED_BYTES = 209715200; // 200 MB
    private const MAX_FILES = 2000;

    private const EXTENSIONS = [
        'html', 'htm', 'css', 'js', 'json', 'txt', 'md', 'xml',
        'svg', 'png', 'jpg', 'jpeg', 'gif', 'webp', 'avif', 'ico',
        'mp3', 'wav', 'ogg', 'm4a', 'aac', 'mp4', 'webm',
        'woff', 'woff2', 'ttf', 'otf', 'vtt',
    ];

    /**
     * Extract the uploaded ZIP into "{$directory}/{uuid}" on the public disk
     * and return the path of its shallowest index.html. Validation errors
     * are reported on $field.
     */
    public static function store(UploadedFile $zip, string $directory, string $field): string
    {
        $packageId = (string) Str::uuid();
        $temporaryPath = $zip->storeAs('interactive-video-temp', $packageId.'.zip', 'local');

        if (! $temporaryPath) {
            throw ValidationException::withMessages([$field => 'ZIP gagal disimpan untuk diproses.']);
        }

        $absolutePath = Storage::disk('local')->path($temporaryPath);
        $destination = $directory.'/'.$packageId;

        try {
            $archive = new PharData($absolutePath);
            $prefix = 'phar://'.str_replace('\\', '/', $absolutePath).'/';
            $entries = [];
            $indexFiles = [];
            $totalBytes = 0;

            foreach (new RecursiveIteratorIterator($archive) as $uri => $file) {
                if ($file->isDir()) {
                    continue;
                }

                $relativePath = str_replace('\\', '/', substr(str_replace('\\', '/', (string) $uri), strlen($prefix)));
                $segments = explode('/', $relativePath);
                $extension = strtolower(pathinfo($relativePath, PATHINFO_EXTENSION));

                if ($relativePath === '' || str_starts_with($relativePath, '/') || str_contains($relativePath, "\0") || in_array('..', $segments, true) || str_contains($relativePath, ':')) {
                    throw ValidationException::withMessages([$field => 'ZIP berisi path file yang tidak aman.']);
                }

                if (str_starts_with($relativePath, '__MACOSX/') || basename($relativePath) === '.DS_Store') {
                    continue;
                }

                if (! in_array($extension, self::EXTENSIONS, true)) {
                    throw ValidationException::withMessages([$field => "Jenis file .{$extension} tidak diizinkan di dalam ZIP."]);
                }

                $totalBytes += $file->getSize();
                $entries[] = [$relativePath, (string) $uri];

                if (strtolower(basename($relativePath)) === 'index.html') {
                    $indexFiles[] = $relativePath;
                }

                if (count($entries) > self::MAX_FILES || $totalBytes > self::MAX_UNCOMPRESSED_BYTES) {
                    throw ValidationException::withMessages([$field => 'Isi ZIP terlalu besar atau memiliki terlalu banyak file.']);
                }
            }

            if ($indexFiles === []) {
                throw ValidationException::withMessages([$field => 'ZIP harus memiliki file index.html.']);
            }

            usort($indexFiles, fn (string $a, string $b) => substr_count($a, '/') <=> substr_count($b, '/'));

            foreach ($entries as [$relativePath, $uri]) {
                $contents = file_get_contents($uri);
                if ($contents === false || ! Storage::disk('public')->put($destination.'/'.$relativePath, $contents)) {
                    throw ValidationException::withMessages([$field => 'Salah satu file dalam ZIP gagal diekstrak.']);
                }
            }

            return $destination.'/'.$indexFiles[0];
        } catch (ValidationException $exception) {
            Storage::disk('public')->deleteDirectory($destination);
            throw $exception;
        } catch (Throwable $exception) {
            Storage::disk('public')->deleteDirectory($destination);
            throw ValidationException::withMessages([$field => 'ZIP tidak valid atau rusak.']);
        } finally {
            Storage::disk('local')->delete($temporaryPath);
        }
    }

    /**
     * The "{$directory}/{uuid}" folder a stored package lives in, or null if
     * $path is not a package stored under $directory.
     */
    public static function packageRoot(?string $path, string $directory): ?string
    {
        if (! $path || ! Str::startsWith($path, $directory.'/')) {
            return null;
        }

        $uuid = explode('/', Str::after($path, $directory.'/'))[0];

        return Str::isUuid($uuid) ? $directory.'/'.$uuid : null;
    }

    /**
     * Remove a package previously returned by store() for the same $directory.
     */
    public static function delete(?string $path, string $directory): void
    {
        if ($root = self::packageRoot($path, $directory)) {
            Storage::disk('public')->deleteDirectory($root);
        }
    }
}
