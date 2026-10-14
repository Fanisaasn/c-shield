<?php

namespace Tests\Feature;

use App\Console\Commands\RestoreContentMedia;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RestoreContentMediaTest extends TestCase
{
    public function test_fresh_storage_is_restored_without_overwriting_uploads_or_using_database(): void
    {
        DB::shouldReceive('connection')->never();
        $disk = Storage::fake('public');
        $files = File::allFiles(database_path('seeders/assets'));
        $this->assertNotEmpty($files);
        $existing = str_replace('\\', '/', $files[0]->getRelativePathname());
        $disk->put($existing, 'existing production upload');
        $disk->put('webinars/new-upload.png', 'new upload not in bundle');

        $this->artisan('content:restore-media')->assertSuccessful();

        foreach ($files as $file) {
            $path = str_replace('\\', '/', $file->getRelativePathname());
            $disk->assertExists($path);
            if ($path !== $existing) {
                $this->assertSame(hash_file('sha256', $file->getPathname()), hash_file('sha256', $disk->path($path)));
            }
        }
        $this->artisan('content:restore-media')->expectsOutput('Restored 0 files; existing files preserved. Database unchanged.')->assertSuccessful();
        $this->assertSame('existing production upload', $disk->get($existing));
        $this->assertSame('new upload not in bundle', $disk->get('webinars/new-upload.png'));
        $this->assertSame(File::get(database_path('seeders/assets/demo-video.webm')), $disk->get('videos/demo-uploaded-file.webm'));
    }

    public function test_check_reports_missing_database_media_without_restoring_files(): void
    {
        $disk = Storage::fake('public');
        $disk->put('flyers/present.png', 'present');
        $rows = [
            'flyer_images' => [(object) ['id' => 1, 'image' => 'flyers/present.png']],
            'webinars' => [(object) ['id' => 2, 'poster_image' => 'webinars/missing.png']],
            'videos' => [(object) ['id' => 3, 'thumbnail' => null, 'video_path' => null, 'interactive_path' => 'interactive-videos/missing/index.html']],
            'articles' => [(object) ['id' => 4, 'cover_image' => 'https://example.com/cover.png']],
        ];
        foreach ($rows as $table => $records) {
            $query = \Mockery::mock();
            DB::shouldReceive('table')->once()->with($table)->andReturn($query);
            $query->shouldReceive('select')->once()->andReturnSelf();
            $query->shouldReceive('orderBy')->once()->with('id')->andReturnSelf();
            $query->shouldReceive('cursor')->once()->andReturn(collect($records));
        }
        $this->artisan('content:restore-media --check')
            ->expectsOutput('Checked 3 local database media references; missing: 2.')
            ->assertFailed();
        $this->assertSame(['flyers/present.png'], $disk->allFiles());
    }

    public function test_prepare_rejects_missing_unmounted_or_unrelated_volume_without_writing(): void
    {
        $disk = Storage::fake('public');
        $command = $this->registerPreparationCommand();

        $this->artisan('content:restore-media --prepare')->assertFailed();
        $command->volume = $disk->path('');
        $command->mounted = false;
        $this->artisan('content:restore-media --prepare')->assertFailed();
        $command->mounted = true;
        $command->volume = database_path();
        $this->artisan('content:restore-media --prepare')->assertFailed();

        $this->assertSame([], $disk->allFiles());
    }

    public function test_prepare_preserves_an_existing_wrong_storage_directory(): void
    {
        $disk = Storage::fake('public');
        $command = $this->registerPreparationCommand();
        $command->volume = $disk->path('');
        $this->app->usePublicPath($disk->path('web-root'));
        $disk->put('web-root/storage/existing.png', 'do not delete');

        $this->artisan('content:restore-media --prepare')->assertFailed();

        $this->assertSame(['web-root/storage/existing.png'], $disk->allFiles());
        $this->assertSame('do not delete', $disk->get('web-root/storage/existing.png'));
    }

    public function test_prepare_restores_to_verified_volume_and_preserves_new_uploads_on_next_start(): void
    {
        DB::shouldReceive('connection')->never();
        // Use a test public/storage directory as the disk; avoid OS-specific symlink privileges.
        $disk = Storage::fake('storage');
        Storage::set('public', $disk);
        $this->app->usePublicPath(dirname(rtrim($disk->path(''), '/\\')));
        $command = $this->registerPreparationCommand();
        $command->volume = $disk->path('');

        $this->artisan('content:restore-media --prepare')->assertSuccessful();
        $disk->assertExists('videos/demo-uploaded-file.webm');
        $disk->put('articles/new-production-cover.png', 'new production cover');
        $this->artisan('content:restore-media --prepare')->assertSuccessful();
        $this->assertSame('new production cover', $disk->get('articles/new-production-cover.png'));
    }

    public function test_check_and_prepare_cannot_be_combined(): void
    {
        $disk = Storage::fake('public');
        $this->artisan('content:restore-media --check --prepare')->assertFailed();
        $this->assertSame([], $disk->allFiles());
    }

    public function test_mount_detection_requires_exact_kernel_mount_point_and_decodes_spaces(): void
    {
        File::shouldReceive('isReadable')->with('/proc/self/mountinfo')->andReturn(true);
        File::shouldReceive('lines')->with('/proc/self/mountinfo')->andReturn(collect([
            '100 99 8:1 / /app/storage/app/public rw,relatime - ext4 /dev/volume rw',
            '101 99 8:2 / /data/public\\040files rw,relatime - ext4 /dev/other rw',
        ]));
        $command = new class extends RestoreContentMedia
        {
            public function mounted(string $path): bool
            {
                return $this->isMountedVolume($path);
            }
        };

        $this->assertTrue($command->mounted('/app/storage/app/public'));
        $this->assertTrue($command->mounted('/data/public files'));
        $this->assertFalse($command->mounted('/app/storage/app/public-old'));
        $this->assertFalse($command->mounted('/app/storage'));
    }

    private function registerPreparationCommand(): RestoreContentMedia
    {
        $command = new class extends RestoreContentMedia
        {
            public ?string $volume = null;

            public bool $mounted = true;

            protected function railwayVolumePath(): ?string
            {
                return $this->volume;
            }

            protected function isMountedVolume(string $volume): bool
            {
                return $this->mounted;
            }
        };
        $this->app->make(Kernel::class)->registerCommand($command);

        return $command;
    }
}
