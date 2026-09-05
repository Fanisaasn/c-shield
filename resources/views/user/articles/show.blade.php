@extends('user.layouts.app')

@section('title', $article->title)
@section('meta_description', $article->excerpt)

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <a href="{{ route('articles.index') }}" class="text-sm font-semibold text-teal-300 hover:text-teal-200">&larr; Kembali ke Artikel</a>
            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-teal-300">
                {{ $article->published_at?->translatedFormat('d M Y') }}
            </p>
            <h1 class="mt-2 font-heading text-3xl font-bold text-white">{{ $article->title }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($article->cover_image)
            <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}" class="mb-8 w-full rounded-xl object-cover">
        @endif

        <div class="max-w-none whitespace-pre-line text-base leading-relaxed text-slate-700">
            {{ $article->content }}
        </div>
    </article>
@endsection
