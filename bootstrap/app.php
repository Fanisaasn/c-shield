<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Symfony\Component\HttpFoundation\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->trustProxies(
            at: '*',
            headers:
                Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO
        );

        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, \Illuminate\Http\Request $request) {
            if ($e->getStatusCode() === 419) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'CSRF token mismatch. Sesi telah kedaluwarsa.'], 419);
                }

                $target = $request->is('admin*') ? route('admin.login') : url()->previous('/');

                return redirect()->to($target)
                    ->withInput($request->except('password', 'password_confirmation', 'captcha', '_token'))
                    ->withErrors(['email' => 'Sesi Anda telah kedaluwarsa atau halaman terlalu lama tidak aktif. Token telah diperbarui, silakan coba masuk kembali.']);
            }
        });
    })->create();