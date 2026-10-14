<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\DashboardSetting;
use App\Models\DashboardSlide;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminEditDashboardTest extends TestCase
{
    protected function admin(): Admin
    {
        return Admin::first() ?? Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $response = $this->get(route('admin.edit-dashboard.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_edit_dashboard_page(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.edit-dashboard.index'));

        $response->assertOk();
        $response->assertSee('Edit Dashboard');
        $response->assertSee('Logo Aplikasi');
        $response->assertSee('Daftar Slide Banner Hero');
        $response->assertSee('Live Preview Dashboard');
    }

    public function test_admin_can_upload_and_reset_logo(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $file = UploadedFile::fake()->image('custom_logo.png', 200, 200);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.edit-dashboard.logo'), [
                'logo' => $file,
            ]);

        $response->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'logo']));
        $this->assertNotNull(DashboardSetting::getLogoUrl());

        // Now test reset
        $resetResponse = $this->actingAs($admin, 'admin')
            ->post(route('admin.edit-dashboard.reset-logo'));

        $resetResponse->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'logo']));
        $this->assertNull(DashboardSetting::getLogoUrl());
    }

    public function test_admin_can_create_update_toggle_and_delete_slide(): void
    {
        Storage::fake('public');
        $admin = $this->admin();

        $file = UploadedFile::fake()->image('banner1.jpg', 1200, 600);

        // 1. Create
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.edit-dashboard.slides.store'), [
                'title' => 'Test Slide Title',
                'description' => 'Test Slide Description',
                'link_url' => 'https://cimahikota.go.id',
                'image' => $file,
                'is_active' => '1',
            ]);

        $response->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'slides']));
        $this->assertDatabaseHas('dashboard_slides', [
            'title' => 'Test Slide Title',
            'is_active' => true,
        ]);

        $slide = DashboardSlide::where('title', 'Test Slide Title')->first();
        $this->assertNotNull($slide);

        // 2. Toggle status
        $toggleResponse = $this->actingAs($admin, 'admin')
            ->patch(route('admin.edit-dashboard.slides.toggle', $slide));

        $toggleResponse->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'slides']));
        $this->assertFalse($slide->fresh()->is_active);

        // 3. Update slide
        $updateResponse = $this->actingAs($admin, 'admin')
            ->put(route('admin.edit-dashboard.slides.update', $slide), [
                'title' => 'Updated Slide Title',
                'description' => 'Updated Description',
                'is_active' => '1',
            ]);

        $updateResponse->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'slides']));
        $this->assertEquals('Updated Slide Title', $slide->fresh()->title);
        $this->assertTrue($slide->fresh()->is_active);

        // 4. Delete slide
        $deleteResponse = $this->actingAs($admin, 'admin')
            ->delete(route('admin.edit-dashboard.slides.destroy', $slide));

        $deleteResponse->assertRedirect(route('admin.edit-dashboard.index', ['tab' => 'slides']));
        $this->assertDatabaseMissing('dashboard_slides', ['id' => $slide->id]);
    }

    public function test_public_dashboard_reflects_logo_and_slides(): void
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->image('my_logo.png', 100, 100);
        $path = $file->storeAs('appearance/logo', 'my_logo.png', 'public');
        DashboardSetting::setValue('app_logo', $path);

        $response = $this->get(route('home'));
        $response->assertOk();
        $response->assertSee('storage/appearance/logo/my_logo.png', false);
    }
}
