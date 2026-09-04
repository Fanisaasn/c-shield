<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'C-SHIELD') &mdash; Cimahi Cyber Security Hub & Awareness Field</title>
    <meta name="description" content="@yield('meta_description', 'Portal pusat keamanan siber dan wadah kesadaran digital terpadu Kota Cimahi, dikelola oleh Diskominfo Kota Cimahi.')">

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%232ab7ca'/%3E%3Cpath d='M12 5 7 7v3.5c0 3.2 2.1 5.6 5 6.5 2.9-.9 5-3.3 5-6.5V7l-5-2Z' stroke='%230b2545' stroke-width='1.4' stroke-linejoin='round' fill='%230b2545' fill-opacity='0.15'/%3E%3Cpath d='m9.7 11.3 1.4 1.4 3-3' stroke='%230b2545' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-navy-900 antialiased">

    <header class="sticky top-0 z-50 border-b border-navy-800/10 bg-navy-900">
        <nav class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" class="flex items-center gap-2 font-heading text-lg font-bold text-white">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500 text-navy-950">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                        <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <span>C-SHIELD</span>
            </a>

            <button type="button" onclick="document.getElementById('main-nav').classList.toggle('hidden')" class="inline-flex items-center justify-center rounded-md p-2 text-white/80 hover:bg-white/10 md:hidden" aria-label="Buka menu">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
            </button>

            <div id="main-nav" class="hidden w-full flex-col gap-1 pt-4 md:flex md:w-auto md:flex-row md:items-center md:gap-1 md:pt-0">
                @php
                    $navLinks = [
                        'home' => ['label' => 'Beranda', 'active' => request()->routeIs('home')],
                        'articles.index' => ['label' => 'Artikel', 'active' => request()->routeIs('articles.*')],
                        'videos.index' => ['label' => 'Video', 'active' => request()->routeIs('videos.*')],
                        'flyers.index' => ['label' => 'Flyer', 'active' => request()->routeIs('flyers.*')],
                        'webinars.index' => ['label' => 'Webinar', 'active' => request()->routeIs('webinars.*')],
                    ];
                @endphp
                @foreach ($navLinks as $routeName => $link)
                    <a href="{{ route($routeName) }}"
                       class="rounded-md px-3 py-2 text-sm font-medium transition {{ $link['active'] ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
                <a href="{{ route('self-assessment.themes') }}"
                   class="mt-2 rounded-md bg-teal-500 px-4 py-2 text-center text-sm font-semibold text-navy-950 transition hover:bg-teal-400 md:ml-2 md:mt-0">
                    Self Assessment
                </a>
            </div>
        </nav>
    </header>

    <main class="flex-1">
        @yield('content')
    </main>

    <footer class="border-t border-navy-900/10 bg-navy-950 text-slate-300">
        <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="grid gap-8 md:grid-cols-3">
                <div>
                    <div class="flex items-center gap-2 font-heading text-base font-bold text-white">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500 text-navy-950">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                                <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                        C-SHIELD
                    </div>
                    <p class="mt-3 text-sm leading-relaxed text-slate-400">
                        Cimahi Cyber Security Hub &amp; Awareness Field &mdash; portal pusat keamanan siber dan wadah
                        kesadaran digital terpadu Kota Cimahi.
                    </p>
                </div>

                <div>
                    <h3 class="font-heading text-sm font-semibold text-white">Navigasi</h3>
                    <ul class="mt-3 space-y-2 text-sm">
                        <li><a href="{{ route('articles.index') }}" class="hover:text-teal-400">Artikel</a></li>
                        <li><a href="{{ route('videos.index') }}" class="hover:text-teal-400">Video Edukasi</a></li>
                        <li><a href="{{ route('flyers.index') }}" class="hover:text-teal-400">Flyer</a></li>
                        <li><a href="{{ route('webinars.index') }}" class="hover:text-teal-400">Webinar</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-heading text-sm font-semibold text-white">Diskominfo Kota Cimahi</h3>
                    <ul class="mt-3 space-y-2 text-sm text-slate-400">
                        <li>Komplek Perkantoran Pemerintah Kota Cimahi,<br>Jl. Raden Demang Hardjakusumah No. 1, Cihanjuang</li>
                        <li>(022) 6642733</li>
                        <li>diskominfo@cimahikota.go.id</li>
                    </ul>
                </div>
            </div>

            <div class="mt-8 border-t border-white/10 pt-6 text-center text-xs text-slate-500">
                &copy; {{ now()->year }} C-SHIELD &mdash; Dinas Komunikasi dan Informatika Kota Cimahi.
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
