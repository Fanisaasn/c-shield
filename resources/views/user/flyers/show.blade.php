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
        <div class="flex items-center justify-center overflow-hidden rounded-xl bg-slate-100">
            @if ($flyer->image)
                <img src="{{ asset('storage/' . $flyer->image) }}" alt="{{ $flyer->title }}" class="w-full object-cover">
            @else
                <div class="flex aspect-[3/4] w-full items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-14 w-14 text-slate-300">
                        <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/>
                        <path d="m3 16 5-5 4 4 5-6 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
            @endif
        </div>

        @if ($flyer->description)
            <p class="mt-6 whitespace-pre-line text-base leading-relaxed text-slate-700">{{ $flyer->description }}</p>
        @endif
    </article>
@endsection
