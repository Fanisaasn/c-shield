<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator &mdash; C-SHIELD</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%232ab7ca'/%3E%3Cpath d='M12 5 7 7v3.5c0 3.2 2.1 5.6 5 6.5 2.9-.9 5-3.3 5-6.5V7l-5-2Z' stroke='%230b2545' stroke-width='1.4' stroke-linejoin='round' fill='%230b2545' fill-opacity='0.15'/%3E%3Cpath d='m9.7 11.3 1.4 1.4 3-3' stroke='%230b2545' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-navy-900 px-4 font-sans">

    <div class="w-full max-w-sm">
        <div class="mb-8 flex flex-col items-center text-center">
            <span class="flex h-12 w-12 items-center justify-center rounded-xl bg-teal-500 text-navy-950">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <h1 class="mt-4 font-heading text-xl font-bold text-white">C-SHIELD Admin</h1>
            <p class="mt-1 text-sm text-slate-400">Masuk untuk mengelola portal keamanan siber Kota Cimahi.</p>
        </div>

        <div class="rounded-xl bg-white p-8 shadow-xl">
            @if ($errors->any())
                <div class="mb-5 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-navy-900">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="mt-1.5 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-navy-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-navy-900">Kata Sandi</label>
                    <div class="relative mt-1.5">
                        <input id="password" type="password" name="password" required autocomplete="current-password"
                               class="block w-full rounded-md border border-slate-300 py-2 pl-3 pr-12 text-sm text-navy-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <button id="toggle-password" type="button" aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false"
                                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-md text-slate-500 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <svg data-eye-open xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="hidden h-5 w-5" aria-hidden="true">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="1.8"/>
                            </svg>
                            <svg data-eye-closed xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5" aria-hidden="true">
                                <path d="m3 3 18 18M10.6 5.1A12 12 0 0 1 12 5c6.5 0 10 7 10 7a19 19 0 0 1-3.1 4M6.2 6.2A22 22 0 0 0 2 12s3.5 7 10 7a12 12 0 0 0 5.8-1.8M9.9 9.9a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="flex items-center gap-3">
                        <img id="captcha-image" src="{{ route('admin.captcha.image') }}" alt="Gambar kode CAPTCHA" width="184" height="58"
                             class="h-[58px] w-[184px] rounded-md border border-slate-300 bg-slate-50">
                        <button id="refresh-captcha" type="button" aria-label="Muat ulang CAPTCHA" title="Muat ulang CAPTCHA"
                                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md border border-slate-300 text-navy-900 transition hover:border-blue-600 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M20 7v5h-5M4 17v-5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M5.5 9a7 7 0 0 1 11.7-2L20 12M4 12l2.8 5a7 7 0 0 0 11.7-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </button>
                    </div>
                    <label for="captcha" class="block text-sm font-medium text-navy-900">Masukkan kode CAPTCHA</label>
                    <input id="captcha" type="text" name="captcha" required maxlength="6" autocomplete="off" autocapitalize="characters" spellcheck="false"
                           class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm uppercase text-navy-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                    Ingat saya
                </label>

                <button type="submit"
                        class="w-full rounded-md bg-navy-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-600">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm text-slate-400">
            <a href="{{ route('home') }}" class="hover:text-teal-400">&larr; Kembali ke portal publik</a>
        </p>
    </div>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            const input = document.getElementById('password');
            const visible = input.type === 'password';
            input.type = visible ? 'text' : 'password';
            this.setAttribute('aria-pressed', String(visible));
            this.setAttribute('aria-label', visible ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            this.querySelector('[data-eye-open]').classList.toggle('hidden', !visible);
            this.querySelector('[data-eye-closed]').classList.toggle('hidden', visible);
        });

        document.getElementById('refresh-captcha').addEventListener('click', function () {
            document.getElementById('captcha').value = '';
            document.getElementById('captcha-image').src = @json(route('admin.captcha.image')) + '?refresh=' + Date.now();
        });
    </script>
</body>
</html>
