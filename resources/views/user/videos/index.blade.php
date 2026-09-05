@extends('user.layouts.app')

@section('title', 'Video Edukasi')
@section('meta_description', 'Kumpulan video edukasi keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Video Edukasi</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Materi pembelajaran keamanan siber dalam format video.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($videos->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                Belum ada video yang dipublikasikan.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($videos as $video)
                    <a href="{{ route('videos.show', $video) }}" class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <div class="flex aspect-video items-center justify-center bg-navy-900">
                            @if ($video->thumbnail)
                                <img src="{{ asset('storage/' . $video->thumbnail) }}" alt="{{ $video->title }}" class="h-full w-full object-cover">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-teal-400">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="M10 9v6l5-3-5-3Z" fill="currentColor"/>
                                </svg>
                            @endif
                        </div>
                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-teal-600">
                                {{ $video->published_at?->translatedFormat('d M Y') }}
                            </p>
                            <h2 class="mt-2 font-heading text-lg font-bold text-navy-900 group-hover:text-blue-600">
                                {{ $video->title }}
                            </h2>
                            <p class="mt-2 line-clamp-2 flex-1 text-sm text-slate-500">{{ $video->description }}</p>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $videos->links() }}
            </div>
        @endif
    </section>
@endsection
