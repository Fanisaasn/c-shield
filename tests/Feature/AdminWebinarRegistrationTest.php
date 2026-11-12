<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Webinar;
use App\Models\WebinarRegistration;
use App\Mail\WebinarLinkMail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminWebinarRegistrationTest extends TestCase
{
    use DatabaseTransactions;

    protected function makeRegistration(?Webinar $webinar = null): WebinarRegistration
    {
        $webinar ??= Webinar::first();
        $this->assertNotNull($webinar, 'Butuh minimal satu webinar di database.');

        return WebinarRegistration::create([
            'webinar_id' => $webinar->id,
            'name' => 'Peserta Uji',
            'email' => 'peserta-uji-' . uniqid() . '@example.com',
            'phone' => '081234567890',
            'email_status' => 'pending',
        ]);
    }

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
        $response->assertSee('Kirim Link Webinar via Email');
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

    public function test_admin_can_toggle_email_status(): void
    {
        $admin = $this->admin();
        $registration = $this->makeRegistration();

        $initialStatus = $registration->email_status;

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.webinar-registrations.toggle-status', $registration));

        $response->assertRedirect();

        $registration->refresh();
        $this->assertNotEquals($initialStatus, $registration->email_status);
    }

    public function test_admin_can_broadcast_webinar_link_via_email(): void
    {
        Mail::fake();

        $admin = $this->admin();
        $webinar = Webinar::first();
        $this->makeRegistration($webinar);

        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.webinar-registrations.send-email', $webinar), [
                'meeting_link' => 'https://zoom.us/j/999888777?pwd=secretpassword',
                'email_subject' => 'Link {judul_webinar}',
                'message_template' => 'Halo {nama}, ini link webinar {judul_webinar}: {link_webinar}',
                'target' => 'all',
            ]);

        $response->assertRedirect(route('admin.webinar-registrations.index', ['webinar_id' => $webinar->id]));

        $webinar->refresh();
        $this->assertEquals('https://zoom.us/j/999888777?pwd=secretpassword', $webinar->meeting_link);

        $registration = $webinar->registrations()->first();
        Mail::assertSent(WebinarLinkMail::class, function (WebinarLinkMail $mail) use ($registration, $webinar) {
            return $mail->hasTo($registration->email)
                && $mail->subjectLine === 'Link ' . $webinar->title
                && str_contains($mail->body, $registration->name)
                && str_contains($mail->body, 'https://zoom.us/j/999888777');
        });
        Mail::assertSentCount($webinar->registrations()->count());
        $this->assertEquals('sent', $registration->refresh()->email_status);
    }

    public function test_admin_can_send_webinar_link_email_to_single_participant(): void
    {
        Mail::fake();

        $registration = $this->makeRegistration();

        $response = $this->actingAs($this->admin(), 'admin')
            ->post(route('admin.webinar-registrations.send-single', $registration));

        $response->assertRedirect();
        Mail::assertSent(WebinarLinkMail::class, fn (WebinarLinkMail $mail) => $mail->hasTo($registration->email));
        $this->assertEquals('sent', $registration->refresh()->email_status);
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
}
