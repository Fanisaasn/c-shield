<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Services\WhatsAppCloudService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminWebinarRegistrationTest extends TestCase
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
        $response = $this->get(route('admin.webinar-registrations.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_webinar_registrations_menu(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.webinar-registrations.index'));

        $response->assertOk();
        $response->assertSee('Data Pendaftaran Webinar');
        $response->assertSee('Kirim Link Webinar via WhatsApp');
    }

    public function test_pendaftar_are_grouped_by_webinar(): void
    {
        $admin = $this->admin();

        $webinarA = Webinar::first();
        $this->assertNotNull($webinarA);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.webinar-registrations.index', ['webinar_id' => $webinarA->id]));

        $response->assertOk();
        $response->assertSee($webinarA->title);
    }

    public function test_admin_can_toggle_whatsapp_status(): void
    {
        $admin = $this->admin();
        $registration = WebinarRegistration::first();
        $this->assertNotNull($registration);

        $initialStatus = $registration->whatsapp_status;

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.webinar-registrations.toggle-status', $registration));

        $response->assertRedirect();

        $registration->refresh();
        $this->assertNotEquals($initialStatus, $registration->whatsapp_status);
    }

    public function test_admin_can_broadcast_whatsapp_link_with_template(): void
    {
        $admin = $this->admin();
        $webinar = Webinar::first();

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.webinar-registrations.send-whatsapp', $webinar), [
                'meeting_link' => 'https://zoom.us/j/999888777?pwd=secretpassword',
                'message_template' => 'Halo {nama}, ini link webinar {judul_webinar}: {link_webinar}',
                'target' => 'all',
            ]);

        $response->assertRedirect(route('admin.webinar-registrations.index', ['webinar_id' => $webinar->id]));

        $webinar->refresh();
        $this->assertEquals('https://zoom.us/j/999888777?pwd=secretpassword', $webinar->meeting_link);
    }

    public function test_admin_can_export_webinar_registrations_csv(): void
    {
        $admin = $this->admin();
        $webinar = Webinar::first();

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.webinar-registrations.export', $webinar));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_whatsapp_service_formats_indonesian_phone_numbers(): void
    {
        $service = new WhatsAppCloudService();

        $this->assertEquals('6281234567890', $service->formatPhoneNumber('0812-3456-7890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('+62 812 3456 7890'));
        $this->assertEquals('6281234567890', $service->formatPhoneNumber('6281234567890'));
        $this->assertNull($service->formatPhoneNumber(null));
        $this->assertNull($service->formatPhoneNumber(''));
    }
}
