@extends('admin.layouts.app')

@section('title', 'Edit Dashboard')

@section('content')
<div class="space-y-6">

    {{-- Alert Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-sm" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0 text-emerald-600">
                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                <path d="m9 12 2 2 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            <div class="flex-1 text-sm font-medium">{{ session('success') }}</div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-rose-800 shadow-sm" role="alert">
            <div class="flex items-center gap-2 font-semibold text-rose-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-rose-600">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M12 8v4m0 4h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Terjadi beberapa kesalahan:
            </div>
            <ul class="mt-2 list-inside list-disc text-xs text-rose-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Page Header --}}
    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <h1 class="font-heading text-xl font-bold text-navy-900">Edit Dashboard</h1>
            </div>
            <p class="mt-1 text-sm text-slate-500">
                Kelola gambar logo aplikasi dan gambar slide/banner hero yang tampil pada dashboard utama.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-navy-900">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14 21 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                Lihat Dashboard Publik
            </a>
        </div>
    </div>

    {{-- Tab Navigation --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6" aria-label="Tabs">
            <button type="button" id="tab-btn-logo" onclick="switchTab('logo')"
                    class="tab-trigger inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <rect width="18" height="18" x="3" y="3" rx="2" stroke="currentColor" stroke-width="1.8"/>
                    <circle cx="9" cy="9" r="2" stroke="currentColor" stroke-width="1.8"/>
                    <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Pengaturan Logo
            </button>
            <button type="button" id="tab-btn-slides" onclick="switchTab('slides')"
                    class="tab-trigger inline-flex items-center gap-2 border-b-2 py-3 px-1 text-sm font-semibold transition">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                    <path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                Slide / Banner Hero
                <span class="ml-1.5 rounded-full bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-600">
                    {{ $slides->count() }}
                </span>
            </button>
        </nav>
    </div>

    {{-- TAB 1: PENGATURAN LOGO & LIVE PREVIEW --}}
    <div id="tab-content-logo" class="tab-content space-y-6">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">

            {{-- Kolom Kiri: Form Upload & Reset Logo --}}
            <div class="lg:col-span-6 space-y-6">
                <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="font-heading text-base font-bold text-navy-900 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-teal-600">
                                    <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                                </svg>
                                Logo Aplikasi
                            </h2>
                            <p class="mt-1 text-xs text-slate-500">
                                Unggah logo kustom baru untuk mengganti logo default pada navbar dan footer dashboard.
                            </p>
                        </div>
                    </div>

                    {{-- Status Logo Saat Ini --}}
                    <div class="mt-4 flex flex-col gap-4 rounded-lg border border-slate-200 bg-slate-50/70 p-4 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex h-16 w-24 shrink-0 items-center justify-center rounded-lg border border-slate-200 bg-navy-900 p-2 shadow-inner">
                                <img id="logo-current-preview"
                                     src="{{ $logoUrl ?: 'data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\'><path d=\'M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z\' stroke=\'%232ab7ca\' stroke-width=\'1.8\' fill=\'%232ab7ca\' fill-opacity=\'0.2\'/><path d=\'m9 12 2 2 4-4\' stroke=\'%232ab7ca\' stroke-width=\'1.8\' stroke-linecap=\'round\' stroke-linejoin=\'round\'/></svg>' }}"
                                     alt="Logo Saat Ini"
                                     class="h-10 w-auto max-w-full object-contain">
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-navy-900">
                                    {{ $logoUrl ? 'Logo Kustom Aktif' : 'Logo Default Sistem (Perisai C-Shield)' }}
                                </p>
                                <p class="mt-0.5 text-[11px] text-slate-500">
                                    {{ $logoUrl ? 'Tersimpan di storage/appearance/logo' : 'Menggunakan logo bawaan sistem' }}
                                </p>
                            </div>
                        </div>

                        @if ($logoUrl)
                            <button type="button" onclick="confirmResetLogo()"
                                    class="inline-flex items-center gap-1.5 self-start rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100 sm:self-center">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M3 3v5h5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Reset ke Default
                            </button>
                        @endif
                    </div>

                    {{-- Form Upload Logo Baru --}}
                    <form action="{{ route('admin.edit-dashboard.logo') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
                        @csrf
                        <div>
                            <label for="logo-input" class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Pilih File Logo Baru
                            </label>
                            <input type="file" id="logo-input" name="logo" required accept="image/png,image/jpeg,image/webp,image/svg+xml"
                                   class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-navy-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-navy-800 cursor-pointer">
                            <p class="mt-1.5 text-[11px] text-slate-500">
                                Format yang didukung: PNG, JPG, JPEG, WebP, SVG. Disarankan berlatar belakang transparan (Maks. 3MB).
                            </p>
                        </div>

                        <div class="flex items-center justify-end pt-2">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-lg bg-teal-500 px-5 py-2.5 text-xs font-semibold text-navy-950 shadow-sm transition hover:bg-teal-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M17 21v-8H7v8M7 3v5h8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Simpan Logo Baru
                            </button>
                        </div>
                    </form>
                </div>

                {{-- Hidden Form for Reset Logo --}}
                <form id="form-reset-logo" action="{{ route('admin.edit-dashboard.reset-logo') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>

            {{-- Kolom Kanan: Interactive Live Preview (Pratinjau Logo) --}}
            <div class="lg:col-span-6 space-y-4">
                <div class="sticky top-6">
                    <div class="flex items-center justify-between pb-2">
                        <div class="flex items-center gap-2">
                            <span class="flex h-2.5 w-2.5 rounded-full bg-teal-500 animate-pulse"></span>
                            <h2 class="font-heading text-sm font-bold uppercase tracking-wider text-slate-700">
                                Live Preview Dashboard
                            </h2>
                        </div>
                        <span class="rounded-full bg-teal-50 border border-teal-200 px-2.5 py-0.5 text-[10px] font-semibold text-teal-700">
                            Pratinjau Real-Time
                        </span>
                    </div>

                    {{-- Mockup Window Dashboard --}}
                    <div class="overflow-hidden rounded-xl border border-slate-300 bg-white shadow-lg transition-all duration-300">
                        {{-- Window Browser Bar --}}
                        <div class="flex items-center justify-between border-b border-slate-200 bg-slate-100 px-3 py-2 text-xs text-slate-500">
                            <div class="flex items-center gap-1.5">
                                <span class="h-2.5 w-2.5 rounded-full bg-rose-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                            </div>
                            <div class="flex items-center gap-1 rounded bg-white px-3 py-0.5 text-[10px] text-slate-600 shadow-xs border border-slate-200 font-mono">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3 w-3 text-emerald-600">
                                    <rect width="18" height="11" x="3" y="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2"/>
                                </svg>
                                cshield.cimahikota.go.id
                            </div>
                            <div class="text-[10px] text-slate-400 font-medium">100% Zoom</div>
                        </div>

                        {{-- Preview Content --}}
                        <div class="p-0 bg-slate-50">
                            {{-- Mockup Navbar --}}
                            <div class="flex items-center justify-between px-4 py-2.5 bg-navy-900 text-white shadow-sm">
                                <div class="flex items-center gap-2">
                                    <div id="preview-logo-container" class="flex items-center">
                                        @if ($logoUrl)
                                            <img id="preview-logo-img" src="{{ $logoUrl }}" alt="Preview Logo" class="h-6 w-auto object-contain">
                                        @else
                                            <span class="flex h-6 w-6 items-center justify-center rounded bg-teal-500 text-navy-950">
                                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                                    <path d="M12 2 4 5v6c0 5 3.4 8.9 8 10 4.6-1.1 8-5 8-10V5l-8-3Z" stroke="currentColor" stroke-width="2" fill="currentColor" fill-opacity="0.15"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                    <span class="font-heading text-xs font-bold tracking-tight">C-SHIELD</span>
                                </div>

                                <div class="hidden sm:flex items-center gap-2 text-[10px] font-medium text-white/70">
                                    <span class="rounded px-2 py-0.5 bg-white/10 text-white font-semibold">Beranda</span>
                                    <span>Artikel</span>
                                    <span>Video</span>
                                    <span>Flyer</span>
                                    <span>Webinar</span>
                                    <span class="rounded bg-teal-500 px-2 py-0.5 text-navy-950 font-bold">Assessment</span>
                                </div>
                            </div>

                            {{-- Mockup Hero Section --}}
                            @php
                                $firstSlide = $slides->where('is_active', true)->first() ?: $slides->first();
                                $previewSlideUrl = $firstSlide ? $firstSlide->image_url : 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=1200&q=80';
                            @endphp
                            <div class="relative overflow-hidden bg-navy-950 p-6 text-white">
                                <div class="absolute inset-0 bg-cover bg-center opacity-30"
                                     style="background-image: url('{{ $previewSlideUrl }}');"></div>
                                <div class="absolute inset-0 bg-gradient-to-r from-navy-950/90 via-navy-950/70 to-transparent"></div>

                                <div class="relative z-10 max-w-sm space-y-2">
                                    <span class="inline-flex items-center rounded-full border border-teal-400/30 bg-teal-500/10 px-2 py-0.5 text-[9px] font-semibold text-teal-300">
                                        Diskominfo Kota Cimahi
                                    </span>
                                    <h3 class="font-heading text-sm font-extrabold leading-tight text-white">
                                        Cimahi Cyber Security Hub &amp; Awareness Field
                                    </h3>
                                    <p class="text-[10px] text-slate-300 line-clamp-2 leading-relaxed">
                                        Pusat informasi dan edukasi keamanan siber untuk masyarakat dan aparatur Kota Cimahi.
                                    </p>
                                    <div class="flex items-center gap-2 pt-1">
                                        <span class="rounded bg-teal-500 px-2.5 py-1 text-[10px] font-bold text-navy-950 shadow-xs">
                                            Mulai Self Assessment
                                        </span>
                                    </div>
                                </div>
                            </div>

                            {{-- Mockup Footer --}}
                            <div class="border-t border-slate-200/50 bg-navy-950 px-4 py-2.5 text-[10px] text-slate-400">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-white">C-SHIELD</span>
                                        <span>&copy; {{ date('Y') }} Diskominfo Kota Cimahi</span>
                                    </div>
                                    <div class="flex gap-2">
                                        <span>Privasi</span>
                                        <span>Kontak</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Petunjuk Preview --}}
                    <div class="mt-3 flex items-start gap-2 rounded-lg bg-teal-50/50 p-2.5 text-[11px] text-slate-600 border border-teal-100">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0 text-teal-600">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                            <path d="M12 16v-4m0-4h.01" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                        </svg>
                        <span>
                            Saat Anda memilih file logo baru di form, pratinjau langsung memperbarui tampilan logo di bilah navigasi sebelum disimpan.
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- TAB 2: KELOLA SLIDE / BANNER HERO --}}
    <div id="tab-content-slides" class="tab-content hidden space-y-6">

        {{-- Slide Sub-header --}}
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-5 sm:flex-row sm:items-center sm:justify-between shadow-sm">
            <div>
                <h2 class="font-heading text-base font-bold text-navy-900 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 text-teal-600">
                        <rect width="18" height="18" x="3" y="3" rx="2" stroke="currentColor" stroke-width="1.8"/>
                        <circle cx="9" cy="9" r="2" stroke="currentColor" stroke-width="1.8"/>
                        <path d="m21 15-3.086-3.086a2 2 0 0 0-2.828 0L6 21" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    </svg>
                    Daftar Slide Banner Hero
                </h2>
                <p class="mt-0.5 text-xs text-slate-500">
                    Atur gambar slide latar pada hero section halaman depan. Tambah, ganti, hapus, aktifkan, nonaktifkan, dan sesuaikan urutan slide.
                </p>
            </div>

            <button type="button" onclick="openAddSlideModal()"
                    class="inline-flex items-center gap-2 rounded-lg bg-teal-500 px-4 py-2 text-xs font-semibold text-navy-950 shadow-sm transition hover:bg-teal-400">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                    <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Tambah Slide Baru
            </button>
        </div>

        {{-- List of Slides --}}
        @if ($slides->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center shadow-sm">
                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-7 w-7">
                        <rect x="2" y="3" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1.6"/>
                        <path d="M8 21h8M12 17v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                    </svg>
                </div>
                <h3 class="mt-3 font-heading text-sm font-bold text-navy-900">Belum ada slide banner</h3>
                <p class="mt-1 text-xs text-slate-500 max-w-sm mx-auto">
                    Hero section saat ini menampilkan gambar fallback bawaan. Tambahkan gambar banner kustom Anda sekarang.
                </p>
                <button type="button" onclick="openAddSlideModal()"
                        class="mt-4 inline-flex items-center gap-2 rounded-lg bg-teal-500 px-4 py-2 text-xs font-semibold text-navy-950 shadow-sm transition hover:bg-teal-400">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                    Tambah Slide Pertama
                </button>
            </div>
        @else
            <div class="space-y-4" id="slides-container">
                @foreach ($slides as $index => $slide)
                    <div class="flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 md:flex-row md:items-center md:justify-between"
                         data-slide-id="{{ $slide->id }}" data-sort-order="{{ $slide->sort_order }}">

                        {{-- Thumbnail and Basic Info --}}
                        <div class="flex items-start gap-4">
                            {{-- Order Badge & Up/Down Arrows --}}
                            <div class="flex flex-col items-center justify-center gap-1 shrink-0 rounded-lg bg-slate-100 p-1.5 text-slate-600">
                                <form action="{{ route('admin.edit-dashboard.slides.reorder') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="slide_id" value="{{ $slide->id }}">
                                    <input type="hidden" name="direction" value="up">
                                    <button type="submit"
                                            {{ $index === 0 ? 'disabled' : '' }}
                                            class="rounded p-1 text-slate-500 transition hover:bg-slate-200 hover:text-navy-900 disabled:opacity-25 disabled:cursor-not-allowed"
                                            title="Pindahkan ke atas">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                            <path d="m18 15-6-6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>

                                <span class="font-mono text-xs font-bold text-navy-900">#{{ $slide->sort_order }}</span>

                                <form action="{{ route('admin.edit-dashboard.slides.reorder') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="slide_id" value="{{ $slide->id }}">
                                    <input type="hidden" name="direction" value="down">
                                    <button type="submit"
                                            {{ $index === $slides->count() - 1 ? 'disabled' : '' }}
                                            class="rounded p-1 text-slate-500 transition hover:bg-slate-200 hover:text-navy-900 disabled:opacity-25 disabled:cursor-not-allowed"
                                            title="Pindahkan ke bawah">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                            <path d="m6 9 6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>

                            {{-- Image Thumbnail with preview on click --}}
                            <div class="relative h-20 w-32 shrink-0 overflow-hidden rounded-lg border border-slate-200 bg-navy-950">
                                <img src="{{ $slide->image_url }}" alt="{{ $slide->title ?? 'Slide #'.$slide->id }}"
                                     class="h-full w-full object-cover transition duration-300 hover:scale-105">
                                <a href="{{ $slide->image_url }}" target="_blank" rel="noopener noreferrer"
                                   class="absolute bottom-1 right-1 rounded bg-black/60 p-1 text-white hover:bg-black/90 transition"
                                   title="Lihat ukuran penuh">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3 w-3">
                                        <path d="M15 3h6v6M10 14 21 3M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </a>
                            </div>

                            {{-- Title & Meta --}}
                            <div class="space-y-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <h3 class="font-heading text-sm font-bold text-navy-900">
                                        {{ $slide->title ?: 'Slide Tanpa Judul #' . $slide->id }}
                                    </h3>
                                    @if ($slide->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[10px] font-semibold text-emerald-700">
                                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 rounded-full border border-slate-200 bg-slate-100 px-2 py-0.5 text-[10px] font-semibold text-slate-600">
                                            <span class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                            Nonaktif
                                        </span>
                                    @endif
                                </div>

                                @if ($slide->description)
                                    <p class="text-xs text-slate-500 line-clamp-2 max-w-xl">
                                        {{ $slide->description }}
                                    </p>
                                @endif

                                @if ($slide->link_url)
                                    <div class="flex items-center gap-1 text-[11px] text-teal-700 font-mono">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3 w-3 shrink-0">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        <a href="{{ $slide->link_url }}" target="_blank" rel="noopener noreferrer" class="hover:underline truncate max-w-xs">
                                            {{ $slide->link_url }}
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                            {{-- Toggle Active / Inactive --}}
                            <form action="{{ route('admin.edit-dashboard.slides.toggle', $slide) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        title="{{ $slide->is_active ? 'Klik untuk menonaktifkan' : 'Klik untuk mengaktifkan' }}"
                                        class="inline-flex items-center gap-1.5 rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $slide->is_active ? 'border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100' : 'border-emerald-300 bg-emerald-50 text-emerald-800 hover:bg-emerald-100' }}">
                                    @if ($slide->is_active)
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M10 15V9M14 15V9" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        Nonaktifkan
                                    @else
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                            <polygon points="5 3 19 12 5 21 5 3" stroke="currentColor" stroke-width="1.8" fill="currentColor" fill-opacity="0.2"/>
                                        </svg>
                                        Aktifkan
                                    @endif
                                </button>
                            </form>

                            {{-- Edit Button --}}
                            <button type="button"
                                    onclick="openEditSlideModal({{ json_encode([
                                        'id' => $slide->id,
                                        'title' => $slide->title,
                                        'description' => $slide->description,
                                        'link_url' => $slide->link_url,
                                        'sort_order' => $slide->sort_order,
                                        'is_active' => $slide->is_active,
                                        'image_url' => $slide->image_url,
                                        'update_url' => route('admin.edit-dashboard.slides.update', $slide),
                                    ]) }})"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-navy-900">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Edit
                            </button>

                            {{-- Delete Button --}}
                            <button type="button"
                                    onclick="confirmDeleteSlide('{{ route('admin.edit-dashboard.slides.destroy', $slide) }}', '{{ addslashes($slide->title ?: 'Slide #'.$slide->id) }}')"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-rose-300 bg-rose-50 px-3 py-1.5 text-xs font-semibold text-rose-700 transition hover:bg-rose-100">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                Hapus
                            </button>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif

    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODALS SECTION --}}
{{-- ========================================================================= --}}

{{-- 1. Modal Tambah Slide --}}
<div id="modal-add-slide" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-add-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-navy-950/60 backdrop-blur-xs transition-opacity" onclick="closeAddSlideModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modal-add-title" class="font-heading text-base font-bold text-navy-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </span>
                    Tambah Slide Banner Baru
                </h3>
                <button type="button" onclick="closeAddSlideModal()" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <form action="{{ route('admin.edit-dashboard.slides.store') }}" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">
                        Pilih Gambar Banner <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" id="add-slide-image" name="image" required accept="image/png,image/jpeg,image/webp,image/svg+xml"
                               onchange="previewSlideImage(this, 'add-image-preview-wrapper', 'add-image-preview')"
                               class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-navy-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-navy-800 cursor-pointer">
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500">Rasio 16:9 disarankan (misal: 1920x1080px). Maksimal 8MB.</p>

                    <div id="add-image-preview-wrapper" class="mt-2 hidden rounded-lg border border-slate-200 bg-navy-950 p-1 overflow-hidden">
                        <img id="add-image-preview" src="#" alt="Pratinjau Slide" class="h-32 w-full object-cover rounded">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Slide (Opsional)</label>
                    <input type="text" name="title" placeholder="Contoh: Cimahi Cyber Security Hub"
                           class="w-full text-xs rounded-lg border border-slate-300">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat (Opsional)</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat mengenai banner..."
                              class="w-full text-xs rounded-lg border border-slate-300"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">URL Tautan / Link (Opsional)</label>
                    <input type="url" name="link_url" placeholder="https://..."
                           class="w-full text-xs rounded-lg border border-slate-300">
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="add-slide-active" name="is_active" value="1" checked
                           class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                    <label for="add-slide-active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Aktifkan slide ini langsung di dashboard
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" onclick="closeAddSlideModal()"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-500 px-4 py-2 text-xs font-semibold text-navy-950 hover:bg-teal-400">
                        Simpan Slide
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 2. Modal Edit Slide --}}
<div id="modal-edit-slide" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-edit-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-navy-950/60 backdrop-blur-xs transition-opacity" onclick="closeEditSlideModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-lg rounded-2xl bg-white p-6 shadow-2xl transition-all">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 id="modal-edit-title" class="font-heading text-base font-bold text-navy-900 flex items-center gap-2">
                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-teal-500/10 text-teal-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    Edit Slide Banner
                </h3>
                <button type="button" onclick="closeEditSlideModal()" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
            </div>

            <form id="form-edit-slide" action="" method="POST" enctype="multipart/form-data" class="mt-4 space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Gambar Slide</label>
                    <div id="edit-image-current-wrapper" class="mb-2 rounded-lg border border-slate-200 bg-navy-950 p-1 overflow-hidden">
                        <img id="edit-image-current" src="#" alt="Gambar Saat Ini" class="h-32 w-full object-cover rounded">
                    </div>

                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">
                        Ganti Gambar (Biarkan kosong jika tidak ingin mengubah):
                    </label>
                    <input type="file" id="edit-slide-image" name="image" accept="image/png,image/jpeg,image/webp,image/svg+xml"
                           onchange="previewSlideImage(this, 'edit-image-current-wrapper', 'edit-image-current')"
                           class="block w-full text-xs text-slate-600 file:mr-3 file:rounded-md file:border-0 file:bg-navy-900 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-white hover:file:bg-navy-800 cursor-pointer">
                    <p class="mt-1 text-[11px] text-slate-500">Maksimal 8MB.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Judul Slide</label>
                    <input type="text" id="edit-slide-title" name="title"
                           class="w-full text-xs rounded-lg border border-slate-300">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea id="edit-slide-desc" name="description" rows="2"
                              class="w-full text-xs rounded-lg border border-slate-300"></textarea>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">URL Tautan</label>
                        <input type="url" id="edit-slide-link" name="link_url" placeholder="https://..."
                               class="w-full text-xs rounded-lg border border-slate-300">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Urutan (Sort Order)</label>
                        <input type="number" id="edit-slide-order" name="sort_order" min="1"
                               class="w-full text-xs rounded-lg border border-slate-300">
                    </div>
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="edit-slide-active" name="is_active" value="1"
                           class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 cursor-pointer">
                    <label for="edit-slide-active" class="text-xs font-semibold text-slate-700 cursor-pointer">
                        Status Slide Aktif
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" onclick="closeEditSlideModal()"
                            class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="inline-flex items-center gap-2 rounded-lg bg-teal-500 px-4 py-2 text-xs font-semibold text-navy-950 hover:bg-teal-400">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 3. Modal Konfirmasi Hapus Slide --}}
<div id="modal-delete-slide" class="fixed inset-0 z-50 hidden overflow-y-auto" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-navy-950/60 backdrop-blur-xs transition-opacity" onclick="closeDeleteSlideModal()"></div>

    <div class="flex min-h-screen items-center justify-center p-4">
        <div class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-rose-100 text-rose-600 mb-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                    <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h3 class="font-heading text-base font-bold text-navy-900">Hapus Slide Ini?</h3>
            <p class="mt-1 text-xs text-slate-500">
                Slide "<span id="delete-slide-title" class="font-semibold text-slate-700"></span>" beserta gambarnya akan dihapus permanen dari sistem.
            </p>

            <form id="form-delete-slide" action="" method="POST" class="mt-5 flex items-center justify-center gap-2">
                @csrf
                @method('DELETE')
                <button type="button" onclick="closeDeleteSlideModal()"
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50">
                    Batal
                </button>
                <button type="submit"
                        class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2 text-xs font-semibold text-white hover:bg-rose-500">
                    Ya, Hapus Slide
                </button>
            </form>
        </div>
    </div>
</div>

{{-- JAVASCRIPT LOGIC & PREVIEW SYNC --}}
<script>
    // Tab Switching Logic with Query Param / Hash Support
    function switchTab(tab) {
        const logoBtn = document.getElementById('tab-btn-logo');
        const slidesBtn = document.getElementById('tab-btn-slides');
        const logoContent = document.getElementById('tab-content-logo');
        const slidesContent = document.getElementById('tab-content-slides');

        if (tab === 'slides') {
            slidesBtn.className = 'tab-trigger inline-flex items-center gap-2 border-b-2 border-teal-500 py-3 px-1 text-sm font-semibold text-teal-600';
            logoBtn.className = 'tab-trigger inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 hover:text-slate-700 hover:border-slate-300';
            slidesContent.classList.remove('hidden');
            logoContent.classList.add('hidden');
            window.history.replaceState(null, null, '?tab=slides');
        } else {
            logoBtn.className = 'tab-trigger inline-flex items-center gap-2 border-b-2 border-teal-500 py-3 px-1 text-sm font-semibold text-teal-600';
            slidesBtn.className = 'tab-trigger inline-flex items-center gap-2 border-b-2 border-transparent py-3 px-1 text-sm font-semibold text-slate-500 hover:text-slate-700 hover:border-slate-300';
            logoContent.classList.remove('hidden');
            slidesContent.classList.add('hidden');
            window.history.replaceState(null, null, '?tab=logo');
        }
    }

    // Logo Input Preview
    const logoInput = document.getElementById('logo-input');
    const logoCurrentPreview = document.getElementById('logo-current-preview');
    const previewLogoContainer = document.getElementById('preview-logo-container');

    logoInput?.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (logoCurrentPreview) {
                    logoCurrentPreview.src = e.target.result;
                }
                if (previewLogoContainer) {
                    previewLogoContainer.innerHTML = `<img src="${e.target.result}" alt="Preview Logo" class="h-6 w-auto object-contain">`;
                }
            };
            reader.readAsDataURL(this.files[0]);
        }
    });

    // Reset Logo Confirmation
    function confirmResetLogo() {
        if (confirm('Apakah Anda yakin ingin mengembalikan logo ke default sistem?')) {
            document.getElementById('form-reset-logo').submit();
        }
    }

    // Slide Image File Preview
    function previewSlideImage(input, wrapperId, imgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.getElementById(wrapperId);
                const img = document.getElementById(imgId);
                if (wrapper && img) {
                    img.src = e.target.result;
                    wrapper.classList.remove('hidden');
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Modals Handling
    function openAddSlideModal() {
        document.getElementById('modal-add-slide').classList.remove('hidden');
    }

    function closeAddSlideModal() {
        document.getElementById('modal-add-slide').classList.add('hidden');
    }

    function openEditSlideModal(data) {
        const modal = document.getElementById('modal-edit-slide');
        const form = document.getElementById('form-edit-slide');
        form.action = data.update_url;

        document.getElementById('edit-slide-title').value = data.title || '';
        document.getElementById('edit-slide-desc').value = data.description || '';
        document.getElementById('edit-slide-link').value = data.link_url || '';
        document.getElementById('edit-slide-order').value = data.sort_order || 1;
        document.getElementById('edit-slide-active').checked = !!data.is_active;

        const currentImg = document.getElementById('edit-image-current');
        if (currentImg && data.image_url) {
            currentImg.src = data.image_url;
        }

        modal.classList.remove('hidden');
    }

    function closeEditSlideModal() {
        document.getElementById('modal-edit-slide').classList.add('hidden');
    }

    function confirmDeleteSlide(url, title) {
        const modal = document.getElementById('modal-delete-slide');
        const form = document.getElementById('form-delete-slide');
        form.action = url;
        document.getElementById('delete-slide-title').innerText = title;
        modal.classList.remove('hidden');
    }

    function closeDeleteSlideModal() {
        document.getElementById('modal-delete-slide').classList.add('hidden');
    }

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddSlideModal();
            closeEditSlideModal();
            closeDeleteSlideModal();
        }
    });

    // Initialize Active Tab on Page Load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'logo';
        switchTab(activeTab);
    });
</script>
@endsection
