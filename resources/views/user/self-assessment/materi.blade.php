@extends('user.layouts.app')

@section('title', 'Materi Belajar')
@section('meta_description', 'Pelajari materi Video, Artikel, dan Flyer C-SHIELD sebelum mengerjakan Post-Assessment.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 5 dari 7
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Pelajari Materi Terlebih Dahulu
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Sebelum mengerjakan Post-Assessment, luangkan waktu untuk mempelajari materi edukasi keamanan
                siber berikut. Anda dapat memilih salah satu atau lebih sesuai kebutuhan.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['route' => 'videos.index', 'title' => 'Video Edukasi', 'desc' => 'Materi pembelajaran dalam format video.'],
                ['route' => 'articles.index', 'title' => 'Artikel', 'desc' => 'Bacaan edukatif seputar praktik keamanan siber.'],
                ['route' => 'flyers.index', 'title' => 'Flyer', 'desc' => 'Materi sosialisasi visual yang ringkas.'],
            ] as $material)
                <a href="{{ route($material['route']) }}" target="_blank" rel="noopener"
                   class="group rounded-xl border border-slate-200 bg-white p-6 transition hover:-translate-y-0.5 hover:border-blue-600/30 hover:shadow-md">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600/10 text-blue-600 transition group-hover:bg-teal-500/10 group-hover:text-teal-500">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M9 12h6M9 16h6M9 8h6M5 4h14a1 1 0 0 1 1 1v14a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1Z" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </span>
                    <h3 class="mt-4 font-heading text-base font-bold text-navy-900 group-hover:text-blue-600">{{ $material['title'] }}</h3>
                    <p class="mt-1 text-sm text-slate-500">{{ $material['desc'] }}</p>
                </a>
            @endforeach
        </div>

        <form method="POST" action="{{ route('self-assessment.materi.lanjut') }}" class="mt-8">
            @csrf
            <button type="submit"
                    class="w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Saya Sudah Siap, Mulai Post-Assessment
            </button>
        </form>
    </section>

@endsection
