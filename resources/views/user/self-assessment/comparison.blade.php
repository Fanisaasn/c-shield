@extends('user.layouts.app')

@section('title', 'Perbandingan Pre vs Post')
@section('meta_description', 'Perbandingan skor Pre-Assessment dan Post-Assessment kesadaran keamanan siber C-SHIELD.')

@php
    $preScore = (int) round($preAttempt->score);
    $postScore = (int) round($postAttempt->score);
    $delta = $postScore - $preScore;
@endphp

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 8 dari 8
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Perbandingan Pre vs Post &mdash; {{ $category->name }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                {{ $respondent ? 'Halo ' . $respondent->name . '. ' : '' }}Berikut perkembangan skor kesadaran
                keamanan siber Anda sebelum dan sesudah mempelajari video materi.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-2xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <div class="relative mx-auto h-64 max-w-sm">
                <canvas id="comparisonChart" role="img" aria-label="Perbandingan skor Pre-Assessment {{ $preScore }} dan Post-Assessment {{ $postScore }}"></canvas>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4 text-center">
                <div class="rounded-lg border border-slate-200 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Pre-Assessment</p>
                    <p class="mt-1 font-heading text-2xl font-extrabold text-navy-900">{{ $preScore }}</p>
                    <p class="mt-1 text-xs text-slate-500">{{ $preAttempt->level }}</p>
                </div>
                <div class="rounded-lg border border-teal-200 bg-teal-50 p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-teal-600">Post-Assessment</p>
                    <p class="mt-1 font-heading text-2xl font-extrabold text-navy-900">{{ $postScore }}</p>
                    <p class="mt-1 text-xs text-teal-600">{{ $postAttempt->level }}</p>
                </div>
            </div>

            <div class="mt-6 text-center">
                @if ($delta > 0)
                    <span class="inline-flex items-center rounded-full border border-teal-200 bg-teal-50 px-4 py-1.5 text-sm font-semibold text-teal-600">
                        Naik {{ $delta }} poin dari Pre-Assessment
                    </span>
                @elseif ($delta < 0)
                    <span class="inline-flex items-center rounded-full border border-red-200 bg-red-50 px-4 py-1.5 text-sm font-semibold text-red-600">
                        Turun {{ abs($delta) }} poin dari Pre-Assessment
                    </span>
                @else
                    <span class="inline-flex items-center rounded-full border border-slate-200 bg-slate-50 px-4 py-1.5 text-sm font-semibold text-slate-600">
                        Skor tidak berubah dari Pre-Assessment
                    </span>
                @endif
            </div>
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('self-assessment.themes') }}"
               class="rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Coba Tema Lain
            </a>
            <a href="{{ route('home') }}"
               class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-navy-900 transition hover:bg-slate-50">
                Kembali ke Beranda
            </a>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new Chart(document.getElementById('comparisonChart'), {
                type: 'bar',
                data: {
                    labels: ['Pre-Assessment', 'Post-Assessment'],
                    datasets: [{
                        data: [{{ $preScore }}, {{ $postScore }}],
                        backgroundColor: ['#1e5aa8', '#2ab7ca'],
                        borderRadius: 6,
                        maxBarThickness: 80,
                    }],
                },
                options: {
                    scales: { y: { beginAtZero: true, max: 100 } },
                    plugins: { legend: { display: false } },
                },
            });
        });
    </script>
@endpush
