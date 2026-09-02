@extends('user.layouts.app')

@section('title', 'Beranda')
@section('meta_description', 'C-SHIELD: Portal pusat keamanan siber dan wadah kesadaran digital terpadu Kota Cimahi. Akses artikel, video edukasi, flyer, webinar, dan self-assessment keamanan siber.')

@section('content')

    {{-- Hero --}}
    <section class="relative overflow-hidden bg-navy-900">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(42,183,202,0.18),_transparent_55%)]"></div>
        <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 lg:px-8 lg:py-28">
            <div class="max-w-3xl">
                <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                    Diskominfo Kota Cimahi
                </span>
                <h1 class="mt-5 font-heading text-4xl font-extrabold leading-tight text-white sm:text-5xl">
                    Cimahi Cyber Security Hub &amp; Awareness Field
                </h1>
                <p class="mt-5 max-w-2xl text-base leading-relaxed text-slate-300 sm:text-lg">
                    Portal pusat keamanan siber dan wadah kesadaran digital terpadu bagi masyarakat dan
                    aparatur Kota Cimahi &mdash; artikel, video edukasi, flyer, webinar, hingga self-assessment
                    tingkat kesadaran keamanan siber, dalam satu tempat.
                </p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="#self-assessment" class="rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                        Mulai Self Assessment
                    </a>
                    <a href="{{ route('articles.index') }}" class="rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white transition hover:bg-white/10">
                        Jelajahi Materi Edukasi
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Feature grid --}}
    <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            @foreach ([
                ['route' => 'articles.index', 'title' => 'Artikel', 'desc' => 'Bacaan edukatif seputar praktik keamanan siber.'],
                ['route' => 'videos.index', 'title' => 'Video Edukasi', 'desc' => 'Materi pembelajaran dalam format video.'],
                ['route' => 'flyers.index', 'title' => 'Flyer', 'desc' => 'Materi sosialisasi visual yang ringkas.'],
                ['route' => 'webinars.index', 'title' => 'Webinar', 'desc' => 'Sesi edukasi daring maupun luring terjadwal.'],
            ] as $feature)
                <a href="{{ route($feature['route']) }}" class="group rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600/10 text-blue-600 transition group-hover:bg-teal-500/10 group-hover:text-teal-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M9 12h6M9 16h6M9 8h6M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3 class="mt-4 font-heading text-base font-bold text-navy-900">{{ $feature['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $feature['desc'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Latest articles --}}
    @if ($latestArticles->isNotEmpty())
        <section class="bg-white py-16">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex items-end justify-between">
                    <h2 class="font-heading text-2xl font-bold text-navy-900">Artikel Terbaru</h2>
                    <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-blue-600 hover:text-teal-500">Lihat semua &rarr;</a>
                </div>
                <div class="mt-8 grid gap-6 md:grid-cols-3">
                    @foreach ($latestArticles as $article)
                        <a href="{{ route('articles.show', $article) }}" class="group rounded-xl border border-slate-200 p-6 transition hover:shadow-md">
                            <p class="text-xs font-medium uppercase tracking-wide text-teal-600">
                                {{ $article->published_at?->translatedFormat('d M Y') }}
                            </p>
                            <h3 class="mt-2 font-heading text-lg font-bold text-navy-900 group-hover:text-blue-600">
                                {{ $article->title }}
                            </h3>
                            <p class="mt-2 line-clamp-3 text-sm text-slate-500">{{ $article->excerpt }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Upcoming webinars --}}
    @if ($upcomingWebinars->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex items-end justify-between">
                <h2 class="font-heading text-2xl font-bold text-navy-900">Webinar Mendatang</h2>
                <a href="{{ route('webinars.index') }}" class="text-sm font-semibold text-blue-600 hover:text-teal-500">Lihat semua &rarr;</a>
            </div>
            <div class="mt-8 grid gap-6 md:grid-cols-2">
                @foreach ($upcomingWebinars as $webinar)
                    <div class="rounded-xl border border-slate-200 bg-white p-6">
                        <p class="text-xs font-medium uppercase tracking-wide text-teal-600">
                            {{ $webinar->webinar_date->translatedFormat('d M Y, H:i') }} WIB &middot; {{ $webinar->platform }}
                        </p>
                        <h3 class="mt-2 font-heading text-lg font-bold text-navy-900">{{ $webinar->title }}</h3>
                        <p class="mt-2 line-clamp-2 text-sm text-slate-500">{{ $webinar->description }}</p>
                        <a href="{{ $webinar->registration_url }}" target="_blank" rel="noopener"
                           class="mt-4 inline-flex items-center gap-1 rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950 hover:bg-teal-400">
                            Register Now
                        </a>
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Self assessment teaser --}}
    <section id="self-assessment" class="scroll-mt-20 bg-navy-900 py-16">
        <div class="mx-auto max-w-4xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment
            </span>
            <h2 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Ukur Tingkat Kesadaran Keamanan Siber Anda
            </h2>
            <p class="mx-auto mt-4 max-w-2xl text-sm leading-relaxed text-slate-300 sm:text-base">
                Isi Pre-Assessment sebelum mempelajari materi, pelajari materi edukasi pilihan Anda, lalu isi
                Post-Assessment untuk melihat perkembangan skor kesadaran keamanan siber Anda. Fitur ini akan
                segera hadir di C-SHIELD.
            </p>
            <span class="mt-6 inline-flex items-center gap-2 rounded-md border border-white/20 px-5 py-3 text-sm font-semibold text-white/60">
                Segera Hadir
            </span>
        </div>
    </section>

@endsection
