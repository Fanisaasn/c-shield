@extends('user.layouts.app')

@section('title', $video->title)
@section('meta_description', $video->description)

@section('content')
    <article class="mx-auto max-w-5xl px-4 py-6 sm:px-6 sm:py-8 lg:px-8 lg:py-10">
        @if (session('survey_success'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                {{ session('survey_success') }}
            </div>
        @endif

        @include('partials.video-player', ['video' => $video, 'showInteractivePlayer' => true])

        <header class="border-b border-slate-200 pb-6 pt-6">
            <h1 class="font-heading text-2xl font-bold text-navy-950 sm:text-3xl">{{ $video->title }}</h1>
            @if ($video->slug === 'jaga-data-jaga-diri')
                <p class="mt-2 font-medium text-slate-600">Interactive Security Awareness</p>
            @endif
            <p class="mt-3 text-sm text-slate-500">Security Awareness &bull; {{ $video->slug === 'jaga-data-jaga-diri' ? 'Interactive Video • Bahasa Indonesia • ' : '' }}{{ $video->published_at?->translatedFormat('d M Y') }}</p>
        </header>

        <section class="border-b border-slate-200 py-7">
            <h2 class="font-heading text-xl font-bold text-navy-950">Tentang video</h2>
            <p class="mt-3 whitespace-pre-line leading-7 text-slate-700">{{ $video->description }}</p>
        </section>

        @if ($video->slug === 'jaga-data-jaga-diri')
            <section class="border-b border-slate-200 py-7">
                <h2 class="font-heading text-xl font-bold text-navy-950">Yang akan dipelajari</h2>
                <ul class="mt-4 grid gap-3 text-sm text-slate-700 sm:grid-cols-2">
                    @foreach (['Mengenali email phishing dan tautan mencurigakan', 'Menangani perangkat USB yang tidak dikenal', 'Mencegah akses fisik tanpa verifikasi', 'Membuat password unik dan melindungi akun'] as $objective)
                        <li class="flex gap-3"><span class="text-teal-600">&#10003;</span><span>{{ $objective }}</span></li>
                    @endforeach
                </ul>
            </section>
        @endif

        <button type="button" onclick="document.getElementById('survey-modal').style.display='flex'" class="mt-2 inline-flex items-center gap-2 rounded-md border border-teal-500 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.2 0-2.3-.25-3.3-.7L3 20l1.2-4.4A8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
            Beri Masukan
        </button>
    </article>

    @include('partials.survey-modal')
@endsection
