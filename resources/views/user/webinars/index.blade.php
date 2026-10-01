@extends('user.layouts.app')

@section('title', 'Webinar')
@section('meta_description', 'Jadwal webinar keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Webinar</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Jadwal webinar keamanan siber dari C-SHIELD Diskominfo Kota Cimahi. Temukan berbagai informasi dan tips seputar keamanan digital untuk meningkatkan kesadaran dan perlindungan diri di dunia maya.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @include('partials.content-search', ['searchRoute' => 'webinars.index', 'searchLabel' => 'webinar'])

        @if ($webinars->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                @if ($search !== '')
                    Tidak ada hasil untuk &ldquo;{{ $search }}&rdquo;. Coba kata kunci lain.
                @else
                    Belum ada webinar yang dijadwalkan.
                @endif
            </p>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($webinars as $webinar)
                    @php $isPast = $webinar->webinar_date->isPast(); @endphp
                    <article class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="relative flex aspect-[4/5] items-center justify-center bg-slate-100">
                            @if ($webinar->poster_image)
                                <img src="{{ asset('storage/' . $webinar->poster_image) }}" alt="Poster {{ $webinar->title }}" loading="lazy"
                                     class="h-full w-full object-contain {{ $isPast ? 'grayscale' : '' }}">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-slate-300">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="m3 16 5-5 4 4 5-6 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif

                            @if ($isPast)
                                <span class="absolute right-2 top-2 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white">Selesai</span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-600/10 px-3 py-1 text-xs font-semibold text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                        <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ $webinar->webinar_date->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                                @if ($webinar->platform)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $webinar->platform }}
                                    </span>
                                @endif
                            </div>

                            <h2 class="mt-3 line-clamp-2 font-heading text-sm font-bold leading-snug text-navy-900 sm:text-base">{{ $webinar->title }}</h2>

                            @if ($webinar->speaker)
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500">Narasumber: {{ $webinar->speaker }}</p>
                            @endif

                            @if ($webinar->description)
                                @include('partials.rich-text', ['html' => $webinar->description, 'class' => 'mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600'])
                                @if (Str::length(Str::plainText($webinar->description)) > 150)
                                    <button type="button" data-webinar-toggle class="mt-1 self-start text-xs font-semibold text-blue-600 hover:text-blue-700">
                                        Selengkapnya
                                    </button>
                                @endif
                            @endif

                            <div class="mt-auto pt-4">
                                @if ($isPast)
                                    <span class="inline-flex w-full items-center justify-center rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                                        Pendaftaran Ditutup
                                    </span>
                                @else
                                    <a href="{{ $webinar->registration_url }}" target="_blank" rel="noopener"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                                        Register Now
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                            <path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $webinars->links() }}
            </div>
        @endif
    </section>

    @push('scripts')
        <script>
            document.querySelectorAll('[data-webinar-toggle]').forEach((button) => {
                button.addEventListener('click', () => {
                    const desc = button.previousElementSibling;
                    const expanded = desc.classList.toggle('line-clamp-3') === false;
                    button.textContent = expanded ? 'Sembunyikan' : 'Selengkapnya';
                });
            });
        </script>
    @endpush
@endsection
