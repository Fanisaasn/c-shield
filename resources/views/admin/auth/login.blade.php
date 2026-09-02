<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Administrator &mdash; C-SHIELD</title>

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
                    <input id="password" type="password" name="password" required
                           class="mt-1.5 block w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-navy-900 focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
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

</body>
</html>
