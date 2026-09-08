@extends('user.layouts.app')

@section('title', $video->title)
@section('meta_description', $video->description)

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('videos.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Kembali ke Video Edukasi</a>
            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-teal-300">
                {{ $video->published_at?->translatedFormat('d M Y') }}
            </p>
            <h1 class="mt-2 font-heading text-3xl font-bold text-white">{{ $video->title }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if (session('survey_success'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                {{ session('survey_success') }}
            </div>
        @endif

        @include('partials.video-player', ['video' => $video])

        <p class="mt-6 whitespace-pre-line text-base leading-relaxed text-slate-700">{{ $video->description }}</p>

        <button type="button" onclick="document.getElementById('survey-modal').style.display='flex'" class="mt-2 inline-flex items-center gap-2 rounded-md border border-teal-500 px-4 py-2 text-sm font-semibold text-teal-700 transition hover:bg-teal-50">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5c-1.2 0-2.3-.25-3.3-.7L3 20l1.2-4.4A8.5 8.5 0 1 1 21 11.5Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/>
            </svg>
            Beri Masukan
        </button>
    </article>

    @include('partials.survey-modal')
@endsection
