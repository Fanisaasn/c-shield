<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentUser;
use App\Models\LearningMaterial;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class LearningMaterialFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Always an isolated in-memory database, never the real one from .env.
        config(['database.default' => 'materi_test', 'database.connections.materi_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '', 'foreign_key_constraints' => true,
        ]]);
        $this->artisan('migrate');
        Storage::fake('public');
        Storage::fake('local');
    }

    /**
     * Session of a respondent whose Pre-Assessment is completed.
     */
    private function preCompletedSession(): array
    {
        $user = AssessmentUser::create([
            'name' => 'Responden', 'phone_last_digits' => '1234', 'gender' => 'Perempuan', 'age' => 21,
            'education' => 'S1', 'domicile' => AssessmentUser::domicileOptions()[0], 'occupation_status' => 'Mahasiswa',
        ]);
        $pre = AssessmentAttempt::create([
            'assessment_user_id' => $user->id, 'type' => 'pre', 'started_at' => now(), 'completed_at' => now(),
        ]);

        return ['assessment_user_id' => $user->id, 'assessment_pre_attempt_id' => $pre->id];
    }

    private function material(string $title, string $type, int $order, ?int $duration = null): LearningMaterial
    {
        return LearningMaterial::create([
            'title' => $title, 'file_type' => $type, 'sort_order' => $order,
            'file_path' => "learning-materials/{$title}.bin", 'duration_seconds' => $duration,
        ]);
    }

    public function test_materials_must_be_finished_in_order_before_post_assessment(): void
    {
        $pdf = $this->material('Pertama', 'pdf', 1);
        $video = $this->material('Kedua', 'video', 2, 60);
        $this->withSession($this->preCompletedSession());

        $this->get(route('self-assessment.materi'))->assertOk()->assertSee('Pertama')->assertSee('Terkunci');

        // The second material is locked: it can neither be opened nor finished.
        $this->get(route('self-assessment.materi', ['langkah' => 2]))->assertRedirect(route('self-assessment.materi'));
        $this->post(route('self-assessment.materi.selesai', $video))->assertSessionHas('error');
        $this->post(route('self-assessment.materi.lanjut'), ['confirm_studied' => 1])->assertSessionHas('error');

        $this->post(route('self-assessment.materi.selesai', $pdf))->assertRedirect(route('self-assessment.materi'));
        $this->assertSame([$pdf->id], session('assessment_materi_done'));

        // Opening the video starts its clock; finishing before it could have played through is refused.
        $this->get(route('self-assessment.materi'))->assertOk()->assertSee('material-video', false);
        $this->post(route('self-assessment.materi.selesai', $video))->assertSessionHas('error', 'Tonton video sampai selesai sebelum melanjutkan.');

        $this->travel(61)->seconds();
        $this->post(route('self-assessment.materi.selesai', $video))->assertRedirect(route('self-assessment.materi'));

        $this->post(route('self-assessment.materi.lanjut'))->assertSessionHas('error');
        $this->post(route('self-assessment.materi.lanjut'), ['confirm_studied' => 1])->assertRedirect(route('self-assessment.post.quiz'));
    }

    public function test_material_cannot_be_finished_without_being_opened(): void
    {
        $pdf = $this->material('Pertama', 'pdf', 1);
        $this->withSession($this->preCompletedSession());

        $this->post(route('self-assessment.materi.selesai', $pdf))->assertSessionHas('error', 'Pelajari materi ini terlebih dahulu.');
    }

    public function test_admin_can_upload_interactive_material_and_reorder(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $existing = $this->material('Lama', 'pdf', 1);

        $zipPath = tempnam(sys_get_temp_dir(), 'pkg');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::OVERWRITE);
        $zip->addFromString('paket/index.html', '<!doctype html><title>x</title>');
        $zip->addFromString('paket/script.js', 'console.log(1)');
        $zip->close();

        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.store'), [
            'kind' => 'interactive',
            'title' => 'Interaktif',
            'interactive_zip' => new UploadedFile($zipPath, 'paket.zip', 'application/zip', null, true),
        ])->assertRedirect(route('admin.learning-material.index'));

        $interactive = LearningMaterial::where('title', 'Interaktif')->firstOrFail();
        $this->actingAs($admin, 'admin')->get(route('admin.learning-material.index'))->assertOk()->assertSee('Video interaktif');
        $this->actingAs($admin, 'admin')->get(route('admin.learning-material.create'))->assertOk();
        $this->actingAs($admin, 'admin')->get(route('admin.learning-material.edit', $interactive))->assertOk()->assertSee('paket.zip');
        $this->assertSame('interactive', $interactive->file_type);
        $this->assertMatchesRegularExpression('#^learning-materials/interactive/[0-9a-f-]{36}/paket/index\.html$#', $interactive->file_path);
        Storage::disk('public')->assertExists($interactive->file_path);
        Storage::disk('public')->assertExists(dirname($interactive->file_path).'/script.js');

        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.move', $interactive), ['direction' => 'up']);
        $this->assertSame(['Interaktif', 'Lama'], LearningMaterial::ordered()->pluck('title')->all());

        $this->actingAs($admin, 'admin')->delete(route('admin.learning-material.destroy', $interactive));
        Storage::disk('public')->assertMissing($interactive->file_path);
        $this->assertTrue($existing->fresh()->exists);
    }

    public function test_admin_can_add_youtube_and_instagram_links(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);
        $pdf = $this->material('Lama', 'pdf', 1);
        Storage::disk('public')->put($pdf->file_path, 'pdf');

        // Switching an uploaded material to a link removes its old file.
        $this->actingAs($admin, 'admin')->put(route('admin.learning-material.update', $pdf), [
            'kind' => 'link', 'title' => 'YouTube', 'external_url' => 'https://youtu.be/dQw4w9WgXcQ', 'link_duration' => '3:32',
        ])->assertRedirect(route('admin.learning-material.index'));

        $youtube = $pdf->fresh();
        $this->assertSame('youtube', $youtube->file_type);
        $this->assertSame('dQw4w9WgXcQ', $youtube->youtubeId());
        $this->assertSame(212, $youtube->duration_seconds);
        $this->assertNull($youtube->file_path);
        Storage::disk('public')->assertMissing($pdf->file_path);

        // Instagram cannot report playback, so its duration is required.
        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.store'), [
            'kind' => 'link', 'title' => 'Instagram', 'external_url' => 'https://www.instagram.com/reel/C1a2B3c4/',
        ])->assertSessionHasErrors('link_duration');

        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.store'), [
            'kind' => 'link', 'title' => 'Bukan video', 'external_url' => 'https://example.com/video',
        ])->assertSessionHasErrors('external_url');

        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.store'), [
            'kind' => 'link', 'title' => 'Instagram', 'external_url' => 'https://www.instagram.com/reels/C1a2B3c4/', 'link_duration' => '45',
        ])->assertRedirect(route('admin.learning-material.index'));

        $instagram = LearningMaterial::where('title', 'Instagram')->firstOrFail();
        $this->assertSame('https://www.instagram.com/reel/C1a2B3c4/embed/', $instagram->instagramEmbedUrl());
        $this->assertSame(45, $instagram->duration_seconds);

        // Respondent: the Instagram video cannot be passed before its length has elapsed.
        $this->withSession($this->preCompletedSession() + ['assessment_materi_done' => [$youtube->id]]);
        $this->get(route('self-assessment.materi'))->assertOk()->assertSee('instagram.com/reel/C1a2B3c4/embed/', false);
        $this->post(route('self-assessment.materi.selesai', $instagram))->assertSessionHas('error');
        $this->travel(46)->seconds();
        $this->post(route('self-assessment.materi.selesai', $instagram))->assertRedirect(route('self-assessment.materi'));

        $this->get(route('self-assessment.materi', ['langkah' => 1]))->assertOk()->assertSee('data-video-id="dQw4w9WgXcQ"', false);
    }

    public function test_ppt_upload_is_rejected(): void
    {
        $admin = Admin::create(['name' => 'Admin', 'email' => 'admin@test.com', 'password' => bcrypt('password')]);

        $this->actingAs($admin, 'admin')->post(route('admin.learning-material.store'), [
            'kind' => 'file',
            'title' => 'Slide',
            'file' => UploadedFile::fake()->create('slide.pptx', 10),
        ])->assertSessionHasErrors('file');
    }
}
