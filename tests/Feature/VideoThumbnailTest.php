<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VideoThumbnailTest extends TestCase
{
    private const YOUTUBE_URL = 'https://www.youtube.com/watch?v=abcdefghijk';

    private const INSTAGRAM_URL = 'https://www.instagram.com/reel/ABC123/?utm_source=ig_web_copy_link';

    protected function setUp(): void
    {
        parent::setUp();

        // Always an isolated in-memory database, never the real one from .env.
        config(['database.default' => 'thumbnail_test', 'database.connections.thumbnail_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->artisan('migrate');
        Storage::fake('public');

        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $this->actingAs($admin, 'admin');
    }

    private function jpeg(): string
    {
        return UploadedFile::fake()->image('cover.jpg')->getContent();
    }

    private function fakeRemotes(): void
    {
        Http::fake([
            'img.youtube.com/*' => Http::response($this->jpeg()),
            'www.instagram.com/*' => Http::response('<img class="EmbeddedMediaImage" alt="post" src="https://scontent.cdninstagram.com/cover.jpg?a=1&amp;b=2">'),
            'scontent.cdninstagram.com/*' => Http::response($this->jpeg()),
        ]);
    }

    private function payload(string $url, array $overrides = []): array
    {
        return [
            'title' => 'Video '.uniqid(), 'slug' => '', 'type' => 'normal', 'video_url' => $url,
            'is_published' => '1', 'published_at' => now()->toDateTimeString(), ...$overrides,
        ];
    }

    public function test_youtube_link_gets_a_thumbnail_on_create(): void
    {
        $this->fakeRemotes();

        $this->post(route('admin.videos.store'), $this->payload(self::YOUTUBE_URL))->assertRedirect();

        $this->assertSame('videos/auto-abcdefghijk.jpg', Video::first()->thumbnail);
        Storage::disk('public')->assertExists('videos/auto-abcdefghijk.jpg');
    }

    public function test_instagram_link_gets_a_thumbnail_on_create(): void
    {
        $this->fakeRemotes();

        $this->post(route('admin.videos.store'), $this->payload(self::INSTAGRAM_URL))->assertRedirect();

        $this->assertSame('videos/auto-ABC123.jpg', Video::first()->thumbnail);
        Http::assertSent(fn ($request) => $request->url() === 'https://scontent.cdninstagram.com/cover.jpg?a=1&b=2');
    }

    public function test_image_outside_instagram_cdn_is_not_downloaded(): void
    {
        Http::fake(['www.instagram.com/*' => Http::response('<img class="EmbeddedMediaImage" src="https://evil.example/cover.jpg">')]);

        $this->post(route('admin.videos.store'), $this->payload(self::INSTAGRAM_URL))->assertRedirect();

        $this->assertNull(Video::first()->thumbnail);
        Http::assertNotSent(fn ($request) => str_contains($request->url(), 'evil.example'));
    }

    public function test_video_is_still_saved_when_the_thumbnail_is_unavailable(): void
    {
        Http::fake(['*' => Http::response('', 404)]);

        $this->post(route('admin.videos.store'), $this->payload(self::YOUTUBE_URL))->assertRedirect();

        $this->assertNull(Video::first()->thumbnail);
    }

    public function test_uploaded_thumbnail_is_kept_when_the_link_changes(): void
    {
        $this->fakeRemotes();
        $this->post(route('admin.videos.store'), $this->payload(self::YOUTUBE_URL, [
            'thumbnail' => UploadedFile::fake()->image('manual.jpg'),
        ]));
        $video = Video::first();
        $manualPath = $video->thumbnail;

        $this->put(route('admin.videos.update', $video), $this->payload(self::INSTAGRAM_URL, ['title' => $video->title]))->assertRedirect();

        $this->assertSame($manualPath, $video->fresh()->thumbnail);
        Http::assertNothingSent();
    }

    public function test_auto_thumbnail_follows_a_changed_link(): void
    {
        $this->fakeRemotes();
        $this->post(route('admin.videos.store'), $this->payload(self::YOUTUBE_URL));
        $video = Video::first();

        $this->put(route('admin.videos.update', $video), $this->payload(self::INSTAGRAM_URL, ['title' => $video->title]))->assertRedirect();

        $this->assertSame('videos/auto-ABC123.jpg', $video->fresh()->thumbnail);
        Storage::disk('public')->assertMissing('videos/auto-abcdefghijk.jpg');
    }

    public function test_command_fills_only_missing_thumbnails(): void
    {
        $this->fakeRemotes();
        $missing = Video::create(['title' => 'A', 'slug' => 'a', 'type' => 'normal', 'video_url' => self::YOUTUBE_URL, 'is_published' => true]);
        $manual = Video::create(['title' => 'B', 'slug' => 'b', 'type' => 'normal', 'video_url' => self::INSTAGRAM_URL, 'thumbnail' => 'videos/manual.jpg', 'is_published' => true]);

        $this->artisan('videos:fetch-thumbnails')->assertSuccessful();

        $this->assertSame('videos/auto-abcdefghijk.jpg', $missing->fresh()->thumbnail);
        $this->assertSame('videos/manual.jpg', $manual->fresh()->thumbnail);
    }
}
