<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\AdminLoginCaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the admin login form.
     */
    public function showLoginForm(Request $request, AdminLoginCaptcha $captcha)
    {
        $captcha->ensure($request);

        return view('admin.auth.login');
    }

    public function captchaImage(Request $request, AdminLoginCaptcha $captcha): Response
    {
        return $captcha->image($request);
    }

    /**
     * Handle an admin login attempt.
     */
    public function login(Request $request, AdminLoginCaptcha $captcha): RedirectResponse
    {
        $captchaIsValid = $captcha->verify($request, $request->input('captcha'));
        $captcha->create($request);

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! $captchaIsValid) {
            return back()
                ->withErrors(['captcha' => 'Kode CAPTCHA tidak valid atau sudah kedaluwarsa. Silakan coba lagi.'])
                ->onlyInput('email');
        }

        if (! Auth::guard('admin')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau kata sandi yang Anda masukkan salah.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    /**
     * Log the admin out.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
