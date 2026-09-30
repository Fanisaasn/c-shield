<?php

namespace Tests\Feature;

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
}
