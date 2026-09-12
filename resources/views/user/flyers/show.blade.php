@extends('user.layouts.app')

@section('title', $flyer->title)
@section('meta_description', $flyer->description)

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('flyers.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Kembali ke Flyer</a>
            <h1 class="mt-4 font-heading text-3xl font-bold text-white">{{ $flyer->title }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
        @if (session('survey_success'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                {{ session('survey_success') }}
            </div>
        @endif

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            @if ($flyer->images->isNotEmpty())
                <div class="relative bg-slate-100">
                    <div id="flyer-track" class="flex snap-x snap-mandatory overflow-x-auto scroll-smooth" style="scrollbar-width:none;-ms-overflow-style:none;">
                        @foreach ($flyer->images as $image)
                            <div class="aspect-square w-full flex-shrink-0 snap-center">
                                <img src="{{ asset('storage/' . $image->image) }}" alt="{{ $flyer->title }} - gambar {{ $loop->iteration }}" class="h-full w-full object-cover">
                            </div>
                        @endforeach
                    </div>

                    @if ($flyer->images->count() > 1)
                        <button type="button" id="flyer-prev" aria-label="Sebelumnya" class="absolute left-2 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition hover:bg-black/70">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m15 18-6-6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>
                        <button type="button" id="flyer-next" aria-label="Berikutnya" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-full bg-black/50 p-2 text-white transition hover:bg-black/70">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </button>

                        <div class="absolute inset-x-0 top-3 flex items-center justify-center gap-1.5">
                            @foreach ($flyer->images as $image)
                                <span data-dot="{{ $loop->index }}" class="h-1.5 w-1.5 rounded-full bg-white/60 transition {{ $loop->first ? 'w-4 bg-white' : '' }}"></span>
                            @endforeach
                        </div>
                    @endif
                </div>
            @else
                <div class="flex aspect-square w-full items-center justify-center bg-slate-100">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-14 w-14 text-slate-300">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/>
                        <path d="m3 16 5-5 4 4 5-6 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            @endif

            <div class="p-5">
                @if ($flyer->description)
                    <p class="whitespace-pre-line text-sm leading-relaxed text-slate-700">
                        <span class="font-heading font-bold text-navy-900">{{ $flyer->title }}</span>
                        &nbsp;{{ $flyer->description }}
                    </p>
                @endif

                <div class="mt-4 flex flex-wrap gap-3">
                    @if ($flyer->source_url)
                        @include('partials.source-link', ['sourceUrl' => $flyer->source_url])
                    @endif

                    <button type="button" onclick="document.getElementById('survey-modal').style.display='flex'" class="inline-flex items-center gap-2 rounded-md border border-teal-500 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.2 0-2.3-.25-3.3-.7L3 20l1.2-4.4A8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
                        </svg>
                        Beri Masukan
                    </button>
                </div>
            </div>
        </div>
    </article>

    @include('partials.survey-modal')

    @push('scripts')
        <style>#flyer-track::-webkit-scrollbar { display: none; }</style>
        <script>
            (function () {
                const track = document.getElementById('flyer-track');
                if (!track) return;

                const slides = track.children;
                const dots = document.querySelectorAll('[data-dot]');
                const prevBtn = document.getElementById('flyer-prev');
                const nextBtn = document.getElementById('flyer-next');

                function goTo(index) {
                    index = Math.max(0, Math.min(slides.length - 1, index));
                    track.scrollTo({ left: slides[index].offsetLeft, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
                }

                function currentIndex() {
                    return Math.round(track.scrollLeft / track.clientWidth);
                }

                function updateDots() {
                    const active = currentIndex();
                    dots.forEach((dot, i) => {
                        dot.classList.toggle('w-4', i === active);
                        dot.classList.toggle('bg-white', i === active);
                        dot.classList.toggle('bg-white/60', i !== active);
                    });
                }

                prevBtn?.addEventListener('click', () => goTo(currentIndex() - 1));
                nextBtn?.addEventListener('click', () => goTo(currentIndex() + 1));
                dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));

                let scrollTimeout;
                track.addEventListener('scroll', () => {
                    clearTimeout(scrollTimeout);
                    scrollTimeout = setTimeout(updateDots, 80);
                });
            })();
        </script>
    @endpush
@endsection
