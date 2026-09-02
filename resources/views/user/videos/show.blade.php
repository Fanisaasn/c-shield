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
        <div class="flex aspect-video items-center justify-center rounded-xl bg-navy-900">
            @if ($video->thumbnail)
                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="h-full w-full rounded-xl object-cover">
            @else
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-14 w-14 text-teal-400">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M10 9v6l5-3-5-3Z" fill="currentColor"/>
                </svg>
            @endif
        </div>

        <a href="{{ $video->video_url }}" target="_blank" rel="noopener"
           class="mt-6 inline-flex items-center gap-2 rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 hover:bg-teal-400">
            Tonton Video
        </a>

        <p class="mt-6 whitespace-pre-line text-base leading-relaxed text-slate-700">{{ $video->description }}</p>
    </article>
@endsection
