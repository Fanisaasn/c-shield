<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExportContentSeedersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Isolate both the source records and export destination from real content.
        config(['database.default' => 'export_test', 'database.connections.export_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        DB::statement('CREATE TABLE webinars (id INTEGER PRIMARY KEY, title TEXT, description TEXT,
            speaker TEXT, webinar_date TEXT, platform TEXT, registration_url TEXT,
            poster_image TEXT, is_published INTEGER)');
        $this->app->useDatabasePath(Storage::fake('export-fixture')->path(''));
        Storage::fake('public');
    }

    public function test_webinar_only_export_copies_poster_and_keeps_other_exports_unchanged(): void
    {
        DB::table('webinars')->insert(['id' => 1, 'title' => 'With poster', 'poster_image' => 'webinars/poster.svg']);
        Storage::disk('public')->put('webinars/poster.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>');
        File::ensureDirectoryExists(database_path('seeders/data'));
        File::put(database_path('seeders/data/flyers.json'), 'unchanged');

        $this->artisan('content:export-seeders --only=webinars')->assertSuccessful();

        $export = json_decode(File::get(database_path('seeders/data/webinars.json')), true);
        $this->assertSame('webinars/poster.svg', $export[0]['poster_image']);
        $this->assertSame(Storage::disk('public')->get('webinars/poster.svg'), File::get(database_path('seeders/assets/webinars/poster.svg')));
        $this->assertSame('unchanged', File::get(database_path('seeders/data/flyers.json')));
        $this->assertSame('webinars/poster.svg', DB::table('webinars')->value('poster_image'));
    }

    public function test_missing_poster_fails_without_replacing_previous_json(): void
    {
        DB::table('webinars')->insert(['id' => 1, 'title' => 'Missing poster', 'poster_image' => 'webinars/missing.png']);
        File::ensureDirectoryExists(database_path('seeders/data'));
        File::put(database_path('seeders/data/webinars.json'), 'previous export');

        $this->artisan('content:export-seeders --only=webinars')->assertFailed();

        $this->assertSame('previous export', File::get(database_path('seeders/data/webinars.json')));
        $this->assertSame('webinars/missing.png', DB::table('webinars')->value('poster_image'));
    }

    public function test_genuinely_absent_poster_remains_null(): void
    {
        DB::table('webinars')->insert(['id' => 1, 'title' => 'No poster', 'poster_image' => null]);

        $this->artisan('content:export-seeders --only=webinars')->assertSuccessful();

        $export = json_decode(File::get(database_path('seeders/data/webinars.json')), true);
        $this->assertNull($export[0]['poster_image']);
    }
}
