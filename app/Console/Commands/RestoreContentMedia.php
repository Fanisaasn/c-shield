<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestoreContentMedia extends Command
{
    protected $signature = 'content:restore-media
        {--check : Only check current database media references; do not write files}
        {--prepare : Require a Railway volume and verify the public storage link before restoring}';

    protected $description = 'Restore bundled public media without changing database content or overwriting existing files';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $this->line('Public media root: '.$disk->path(''));

        if ($this->option('prepare') && ! $this->prepareStorage()) {
            return self::FAILURE;
        }

        if ($this->option('check')) {
            $missing = 0;
            $checked = 0;
            foreach (['flyer_images' => ['image'], 'webinars' => ['poster_image'], 'videos' => ['thumbnail', 'video_path', 'interactive_path'], 'articles' => ['cover_image']] as $table => $columns) {
                foreach (DB::table($table)->select(['id', ...$columns])->orderBy('id')->cursor() as $row) {
                    foreach ($columns as $column) {
                        $path = $row->$column;
                        if (! $path || Str::startsWith($path, ['http://', 'https://'])) {
                            continue;
                        }
                        $checked++;
                        if (! $disk->exists($path)) {
                            $this->error("Missing {$table} #{$row->id} {$column}: {$path}");
                            $missing++;
                        }
                    }
                }
            }
            $this->info("Checked {$checked} local database media references; missing: {$missing}.");

            return $missing ? self::FAILURE : self::SUCCESS;
        }

        $source = database_path('seeders/assets');
        if (! File::isDirectory($source)) {
            $this->error('Missing database/seeders/assets bundle. Include exported assets in the deployment.');

            return self::FAILURE;
        }

        $files = [];
        foreach (File::allFiles($source) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            $files[$path] = $file->getPathname();
        }

        // VideoSeeder stores this legacy bundled file under a different public path.
        if (isset($files['demo-video.webm']) && ! isset($files['videos/demo-uploaded-file.webm'])) {
            $files['videos/demo-uploaded-file.webm'] = $files['demo-video.webm'];
        }

        $restored = 0;
        foreach ($files as $path => $sourcePath) {
            if ($disk->exists($path)) {
                continue;
            }
            $stream = fopen($sourcePath, 'rb');
            if ($stream === false) {
                $this->error("Cannot read bundled media: {$path}");

                return self::FAILURE;
            }
            try {
                if (! $disk->put($path, $stream)) {
                    $this->error("Cannot restore media: {$path}");

                    return self::FAILURE;
                }
            } finally {
                fclose($stream);
            }
            $restored++;
        }

        $this->info("Restored {$restored} files; existing files preserved. Database unchanged.");

        return self::SUCCESS;
    }

    private function prepareStorage(): bool
    {
        if ($this->option('check')) {
            $this->error('--check cannot be combined with --prepare; --check never changes files.');

            return false;
        }

        $mount = $this->railwayVolumePath();
        $root = realpath(Storage::disk('public')->path(''));
        $volume = $mount ? realpath($mount) : false;

        if (! $volume || ! $root || ! $this->isMountedVolume($volume)
            || ($root !== $volume && ! str_starts_with($root, rtrim($volume, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR))) {
            $this->error('Public media is not on a mounted Railway volume. Attach a volume at '.storage_path('app/public').'.');

            return false;
        }

        if (! is_writable($root)) {
            $this->error('The application cannot write to the public-media volume. Check its ownership and permissions.');

            return false;
        }

        $link = public_path('storage');
        if (realpath($link) !== $root) {
            if (file_exists($link) || is_link($link)) {
                $this->error('public/storage exists but does not point to the public disk. Inspect and back up this path before repairing it.');

                return false;
            }

            if ($this->call('storage:link', ['--no-interaction' => true]) !== self::SUCCESS) {
                return false;
            }
            clearstatcache(true, $link);
        }

        if (realpath($link) !== $root) {
            $this->error('public/storage does not resolve to the public disk after storage:link. Check filesystem link configuration.');

            return false;
        }

        $this->info('Verified mounted public-media volume and public/storage target.');

        return true;
    }

    protected function railwayVolumePath(): ?string
    {
        return env('RAILWAY_VOLUME_MOUNT_PATH');
    }

    protected function isMountedVolume(string $volume): bool
    {
        // Railway mounts volumes at runtime. An environment variable alone is not proof.
        if (! File::isReadable('/proc/self/mountinfo')) {
            return false;
        }

        foreach (File::lines('/proc/self/mountinfo') as $line) {
            $fields = explode(' ', $line);
            $mountPoint = preg_replace_callback('/\\\\([0-7]{3})/', fn ($match) => chr(octdec($match[1])), $fields[4] ?? '');
            if ($mountPoint === $volume) {
                return true;
            }
        }

        return false;
    }
}
