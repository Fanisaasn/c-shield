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
            <h1 class="public-page-title mt-2 font-heading font-bold text-white">{{ $article->title }}</h1>
        </div>
    </section>

    <article class="mx-auto max-w-3xl px-4 py-12 sm:px-6 lg:px-8">
        @if (session('survey_success'))
            <div class="mb-6 rounded-lg border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-800">
                {{ session('survey_success') }}
            </div>
        @endif

        @if ($article->cover_image)
            <img src="{{ asset('storage/' . $article->cover_image) }}" alt="{{ $article->title }}" class="mb-8 w-full rounded-xl object-cover">
        @endif

        @include('partials.rich-text', ['html' => $article->content, 'class' => 'public-article-content text-base leading-relaxed text-slate-700'])

        @if ($article->attachment)
            <a href="{{ asset('storage/' . $article->attachment) }}" target="_blank" rel="noopener" download="{{ $article->attachment_name }}"
               class="mt-8 flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700 hover:border-teal-300 hover:bg-teal-50">
                <svg class="h-6 w-6 shrink-0 text-teal-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3M6 20h12a2 2 0 002-2V8l-6-6H6a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span class="min-w-0 flex-1 truncate font-medium">{{ $article->attachment_name ?: basename($article->attachment) }}</span>
                <span class="font-semibold text-teal-700">Unduh</span>
            </a>
        @endif

        <div class="mt-8 flex flex-wrap gap-3">
            @if ($article->source_url)
                @include('partials.source-link', ['sourceUrl' => $article->source_url])
            @endif

            @include('partials.survey-button')
        </div>
    </article>

@endsection
