<?php

namespace Tests\Feature;

use App\Support\AdminLoginCaptcha;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Tests\TestCase;

class AdminLoginCaptchaTest extends TestCase
{
    public function test_login_page_displays_a_server_generated_png_without_exposing_the_answer(): void
    {
        $this->seedCaptcha('A7K9P', now()->addMinutes(5)->timestamp);

        $page = $this->get(route('admin.login'));
        $page->assertOk();
        $page->assertSee('Masukkan kode CAPTCHA');
        $page->assertSee(route('admin.captcha.image'));
        $page->assertDontSee('A7K9P');

        $image = $this->get(route('admin.captcha.image', ['refresh' => 1]));
        $image->assertOk();
        $image->assertHeader('Content-Type', 'image/png');
        $this->assertStringStartsWith("\x89PNG\r\n\x1a\n", $image->getContent());
        $this->assertNotSame(hash_hmac('sha256', 'A7K9P', (string) config('app.key')), session('admin_login_captcha.answer_hash'));
        $this->assertStringNotContainsString('A7K9P', serialize(session('admin_login_captcha')));
    }

    public function test_correct_captcha_allows_existing_admin_authentication(): void
    {
        $this->seedCaptcha('A7K9P', now()->addMinutes(5)->timestamp);
        $this->mockAdminGuard(true, 'correct-password', true);

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
            'captcha' => 'A7K9P',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasNoErrors();
        $this->assertNotSame(
            hash_hmac('sha256', 'A7K9P', (string) config('app.key')),
            session('admin_login_captcha.answer_hash')
        );
    }

    public function test_invalid_captcha_rejects_correct_credentials_and_rotates_challenge(): void
    {
        $this->seedCaptcha('A7K9P', now()->addMinutes(5)->timestamp);
        $this->mockAdminGuard(false);

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
            'captcha' => 'WRONG',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertNotSame(
            hash_hmac('sha256', 'A7K9P', (string) config('app.key')),
            session('admin_login_captcha.answer_hash')
        );
    }

    public function test_expired_captcha_is_rejected_and_replaced(): void
    {
        $this->seedCaptcha('A7K9P', now()->subSecond()->timestamp);
        $this->mockAdminGuard(false);

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
            'captcha' => 'A7K9P',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGreaterThan(now()->timestamp, session('admin_login_captcha.expires_at'));
        $this->assertNotSame(
            hash_hmac('sha256', 'A7K9P', (string) config('app.key')),
            session('admin_login_captcha.answer_hash')
        );
    }

    public function test_refresh_returns_a_new_png_and_invalidates_the_previous_answer(): void
    {
        $this->seedCaptcha('A7K9P', now()->addMinutes(5)->timestamp);
        $oldHash = session('admin_login_captcha.answer_hash');

        $response = $this->get(route('admin.captcha.image', ['refresh' => 1]));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertNotSame($oldHash, session('admin_login_captcha.answer_hash'));
        $this->assertGreaterThan(now()->timestamp, session('admin_login_captcha.expires_at'));
    }

    public function test_direct_post_without_captcha_cannot_bypass_login(): void
    {
        $this->mockAdminGuard(false);

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'correct-password',
        ]);

        $response->assertSessionHasErrors('captcha');
    }

    public function test_invalid_credentials_still_fail_after_a_correct_captcha(): void
    {
        $this->seedCaptcha('A7K9P', now()->addMinutes(5)->timestamp);
        $this->mockAdminGuard(false, 'incorrect-password');

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'incorrect-password',
            'captcha' => 'A7K9P',
        ]);

        $response->assertSessionHasErrors('email');
        $response->assertSessionDoesntHaveErrors('captcha');
    }

    public function test_login_endpoint_is_rate_limited(): void
    {
        $this->mockAdminGuard(false);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post(route('admin.login.attempt'), [
                'email' => 'admin@example.com',
                'password' => 'password',
            ]);
        }

        $response = $this->post(route('admin.login.attempt'), [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertTooManyRequests();
    }

    private function seedCaptcha(string $answer, int $expiresAt): void
    {
        $this->withSession([
            'admin_login_captcha' => [
                'answer_hash' => hash_hmac('sha256', $answer, (string) config('app.key')),
                'expires_at' => $expiresAt,
                'image' => base64_encode('test image'),
            ],
        ]);
    }

    private function mockAdminGuard(bool $attemptResult, ?string $expectedPassword = null, bool $remember = false): void
    {
        $guard = Mockery::mock(StatefulGuard::class);
        $guard->shouldReceive('check')->zeroOrMoreTimes()->andReturnFalse();

        if ($expectedPassword === null) {
            $guard->shouldReceive('attempt')->never();
        } else {
            $guard->shouldReceive('attempt')
                ->with(['email' => 'admin@example.com', 'password' => $expectedPassword], $remember)
                ->andReturn($attemptResult);
        }

        Auth::shouldReceive('userResolver')
            ->zeroOrMoreTimes()
            ->andReturn(static fn (?string $guard = null) => null);
        Auth::shouldReceive('guard')
            ->with('admin')
            ->zeroOrMoreTimes()
            ->andReturn($guard);
    }
}