<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 &mdash; Sesi Kedaluwarsa | C-SHIELD</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%232ab7ca'/%3E%3Cpath d='M12 5 7 7v3.5c0 3.2 2.1 5.6 5 6.5 2.9-.9 5-3.3 5-6.5V7l-5-2Z' stroke='%230b2545' stroke-width='1.4' stroke-linejoin='round' fill='%230b2545' fill-opacity='0.15'/%3E%3Cpath d='m9.7 11.3 1.4 1.4 3-3' stroke='%230b2545' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-navy-900 px-4 font-sans text-slate-100">
    <div class="w-full max-w-md text-center">
        <!-- Shield Icon with Warning Ring -->
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500/20 to-teal-500/20 p-4 ring-1 ring-white/10 shadow-xl">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-teal-400">
                <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                <path d="M12 8v5M12 16h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-300 ring-1 ring-amber-400/20">
            HTTP 419 &bull; Page Expired
        </span>

        <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
            Sesi Anda Telah Berakhir
        </h1>

        <p class="mt-3 text-sm leading-relaxed text-slate-300">
            Halaman ini telah kedaluwarsa karena tidak ada aktivitas selama beberapa waktu atau token keamanan formulir perlu diperbarui demi menjaga keamanan akun Anda.
        </p>

        <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
            <button onclick="window.location.reload();"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-teal-500 px-5 py-2.5 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 shadow-md">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M20 7v5h-5M4 17v-5h5" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M5.5 9a7 7 0 0 1 11.7-2L20 12M4 12l2.8 5a7 7 0 0 0 11.7-2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Muat Ulang Halaman
            </button>

            <a href="{{ route('admin.login') }}"
               class="inline-flex items-center justify-center gap-2 rounded-lg border border-white/20 bg-white/5 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-white/10">
                Masuk Kembali
            </a>
        </div>

        <p class="mt-6 text-xs text-slate-400">
            <a href="{{ url('/') }}" class="hover:text-teal-400 transition">&larr; Kembali ke Beranda Publik</a>
        </p>
    </div>
</body>
</html>
