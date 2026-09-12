@extends('user.layouts.app')

@section('title', 'Video Edukasi')
@section('meta_description', 'Kumpulan video edukasi keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Video Edukasi</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Kumpulan video edukasi keamanan siber dari C-SHIELD Diskominfo Kota Cimahi. Temukan berbagai informasi dan tips seputar keamanan digital untuk meningkatkan kesadaran dan perlindungan diri di dunia maya.      
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
                    <article class="group flex flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        @include('partials.video-player', ['video' => $video, 'iconClass' => 'h-10 w-10'])
                        <div class="flex flex-1 flex-col p-5">
                            <p class="text-xs font-medium uppercase tracking-wide text-teal-600">
                                {{ $video->published_at?->translatedFormat('d M Y') }}
                            </p>
                            <a href="{{ route('videos.show', $video) }}" class="mt-2 font-heading text-lg font-bold text-navy-900 group-hover:text-blue-600">
                                {{ $video->title }}
                            </a>
                            <p class="mt-2 line-clamp-2 flex-1 text-sm text-slate-500">{{ $video->description }}</p>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $videos->links() }}
            </div>
        @endif
    </section>
@endsection
