<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class RestoreContentMedia extends Command
{
    protected $signature = 'content:restore-media {--check : Only check current database media references; do not write files}';

    protected $description = 'Restore bundled public media without changing database content or overwriting existing files';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $this->line('Public media root: '.$disk->path(''));

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

        $restored = 0;
        foreach (File::allFiles($source) as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            if ($disk->exists($path)) {
                continue;
            }
            $stream = fopen($file->getPathname(), 'rb');
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
}
