<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PublicContentSearchTest extends TestCase
{
    public function test_search_matches_only_titles_and_preserves_pagination(): void
    {
        config(['database.default' => 'search_test', 'database.connections.search_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ]]);
        $this->withoutVite();
        DB::statement('CREATE TABLE flyer_images (id INTEGER PRIMARY KEY, flyer_id INTEGER, image TEXT, sort_order INTEGER)');

        foreach (['videos' => '/video', 'flyers' => '/flyer', 'webinars' => '/webinar', 'articles' => '/artikel'] as $table => $url) {
            DB::statement("CREATE TABLE {$table} (id INTEGER PRIMARY KEY, title TEXT, description TEXT,
                slug TEXT, is_published INTEGER, published_at TEXT, webinar_date TEXT, speaker TEXT)");
            for ($id = 1; $id <= 12; $id++) {
                DB::table($table)->insert([
                    'id' => $id, 'title' => $id === 12 ? 'Content 12' : "Siber {$id}", 'slug' => "content-{$id}",
                    'description' => 'Contoh deskripsi siber',
                    'is_published' => $id !== 11, 'published_at' => '2026-01-01',
                    'webinar_date' => '2026-01-01',
                ]);
            }

            $this->get($url.'?q=siber')->assertOk()->assertViewHas($table, function ($items) {
                return $items->total() === 10 && ! $items->contains('id', 11)
                    && str_contains($items->nextPageUrl(), 'q=siber');
            })->assertSee('value="siber"', false)->assertDontSee('>Reset</a>', false);
            $this->get($url.'?q=Content%2012')->assertOk()->assertViewHas($table, fn ($items) => $items->total() === 1);
            $this->get($url.'?q=Contoh')->assertOk()->assertViewHas($table, fn ($items) => $items->total() === 0);
            $this->get($url.'?q=tidak-ada')->assertOk()->assertSee('Tidak ada hasil');
            $this->get($url)->assertOk()->assertViewHas($table, fn ($items) => $items->total() === 11);
        }

        DB::table('webinars')->where('id', 12)->update(['speaker' => 'Narasumber unik']);
        $this->get('/webinar?q=Narasumber')->assertOk()->assertViewHas('webinars', fn ($items) => $items->total() === 0);
        $this->getJson('/video?q[]=invalid')->assertUnprocessable();
    }
}
