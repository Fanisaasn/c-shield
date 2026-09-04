<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') &mdash; C-SHIELD Admin</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Crect width='24' height='24' rx='6' fill='%232ab7ca'/%3E%3Cpath d='M12 5 7 7v3.5c0 3.2 2.1 5.6 5 6.5 2.9-.9 5-3.3 5-6.5V7l-5-2Z' stroke='%230b2545' stroke-width='1.4' stroke-linejoin='round' fill='%230b2545' fill-opacity='0.15'/%3E%3Cpath d='m9.7 11.3 1.4 1.4 3-3' stroke='%230b2545' stroke-width='1.4' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen bg-slate-50 font-sans text-navy-900 antialiased">

    <div id="admin-sidebar-overlay" onclick="document.getElementById('admin-sidebar').classList.add('-translate-x-full'); this.classList.add('hidden')" class="fixed inset-0 z-30 hidden bg-navy-950/50 md:hidden"></div>

    <aside id="admin-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-navy-900 transition-transform duration-200 md:static md:z-auto md:translate-x-0">
        <div class="flex items-center gap-2 px-6 py-5">
            <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-teal-500 text-navy-950">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                    <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round" fill="currentColor" fill-opacity="0.15"/>
                    <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
            <span class="font-heading text-base font-bold text-white">C-SHIELD Admin</span>
        </div>

        <nav class="mt-4 flex-1 space-y-1 px-3">
            <a href="{{ route('admin.dashboard') }}"
               class="flex items-center gap-2 rounded-md px-3 py-2 text-sm font-medium {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0">
                    <path d="M4 13h6V4H4v9Zm0 7h6v-5H4v5Zm10 0h6V11h-6v9Zm0-16v5h6V4h-6Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                </svg>
                Dashboard
            </a>
        </nav>

        <div class="border-t border-white/10 px-3 py-4">
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-md px-3 py-2 text-left text-sm font-medium text-white/70 transition hover:bg-white/5 hover:text-white">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    <div class="flex min-w-0 flex-1 flex-col">
        <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-4 sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button"
                        onclick="document.getElementById('admin-sidebar').classList.remove('-translate-x-full'); document.getElementById('admin-sidebar-overlay').classList.remove('hidden')"
                        class="inline-flex items-center justify-center rounded-md p-2 text-slate-500 hover:bg-slate-100 md:hidden" aria-label="Buka menu">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                        <path d="M4 6h16M4 12h16M4 18h16" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                </button>
                <h1 class="font-heading text-lg font-bold text-navy-900">@yield('title', 'Dashboard')</h1>
            </div>
            <span class="text-sm text-slate-500">{{ auth('admin')->user()->name }}</span>
        </header>

        <main class="flex-1 p-4 sm:p-6">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
