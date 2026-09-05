@extends('user.layouts.app')

@section('title', 'Artikel')
@section('meta_description', 'Kumpulan artikel edukasi keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Artikel</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Bacaan edukatif seputar praktik keamanan siber untuk masyarakat dan aparatur Kota Cimahi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($articles->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                Belum ada artikel yang dipublikasikan.
            </p>
        @else
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($articles as $article)
                    <a href="{{ route('articles.show', $article) }}" class="group flex flex-col rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                        <p class="text-xs font-medium uppercase tracking-wide text-teal-600">
                            {{ $article->published_at?->translatedFormat('d M Y') }}
                        </p>
                        <h2 class="mt-2 font-heading text-lg font-bold text-navy-900 group-hover:text-blue-600">
                            {{ $article->title }}
                        </h2>
                        <p class="mt-2 line-clamp-3 flex-1 text-sm text-slate-500">{{ $article->excerpt }}</p>
                        <span class="mt-4 text-sm font-semibold text-blue-600 group-hover:text-teal-500">Baca selengkapnya &rarr;</span>
                    </a>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $articles->links() }}
            </div>
        @endif
    </section>
@endsection
