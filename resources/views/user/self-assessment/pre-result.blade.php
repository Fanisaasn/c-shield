@extends('user.layouts.app')

@section('title', 'Hasil Pre-Assessment')
@section('meta_description', 'Hasil Pre-Assessment kesadaran keamanan siber C-SHIELD.')

@php
    $levelStyles = [
        'Sangat Rendah' => ['hex' => '#dc2626', 'badge' => 'bg-red-50 text-red-600 border-red-200'],
        'Rendah' => ['hex' => '#f97316', 'badge' => 'bg-orange-50 text-orange-600 border-orange-200'],
        'Cukup' => ['hex' => '#eab308', 'badge' => 'bg-amber-50 text-amber-600 border-amber-200'],
        'Baik' => ['hex' => '#1e5aa8', 'badge' => 'bg-blue-50 text-blue-600 border-blue-200'],
        'Sangat Baik' => ['hex' => '#2ab7ca', 'badge' => 'bg-teal-50 text-teal-600 border-teal-200'],
    ];
    $currentStyle = $levelStyles[$attempt->level] ?? $levelStyles['Cukup'];
    $overallScore = (int) round($attempt->score);
@endphp

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 4 dari 4
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                Hasil Pre-Assessment &mdash; {{ $category->name }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                {{ $respondent ? 'Halo ' . $respondent->name . '. ' : '' }}Ini adalah titik awal pemahaman Anda
                pada tema {{ $category->name }} sebelum mempelajari materi edukasi C-SHIELD.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-heading text-base font-bold text-navy-900">Skor {{ $category->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">Hasil berdasarkan jawaban Pre-Assessment Anda.</p>

            <div class="relative mx-auto mt-6 h-56 w-56">
                <canvas id="overallScoreChart" role="img" aria-label="Diagram skor {{ $overallScore }} dari 100, tingkat {{ $attempt->level }}"></canvas>
                <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                    <span class="font-heading text-4xl font-extrabold text-navy-900">{{ $overallScore }}</span>
                    <span class="text-xs font-medium text-slate-400">dari 100</span>
                </div>
            </div>

            <div class="mt-5 flex justify-center">
                <span class="inline-flex items-center rounded-full border px-4 py-1.5 text-sm font-semibold {{ $currentStyle['badge'] }}">
                    Tingkat Kesadaran: {{ $attempt->level }}
                </span>
            </div>
        </div>

        <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
            <h2 class="font-heading text-base font-bold text-navy-900">Rincian Jawaban</h2>
            <div class="mt-4 space-y-3">
                @foreach ($answerReview as $i => $review)
                    <div class="rounded-lg border border-slate-200 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <p class="text-sm font-medium text-navy-900">{{ $i + 1 }}. {{ $review['question'] }}</p>
                            @if ($review['is_correct'])
                                <span class="shrink-0 rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-semibold text-teal-600">Benar</span>
                            @else
                                <span class="shrink-0 rounded-full bg-red-50 px-2.5 py-0.5 text-xs font-semibold text-red-600">Keliru</span>
                            @endif
                        </div>
                        <p class="mt-2 text-sm text-slate-500">Jawaban Anda: {{ $review['chosen'] }}</p>
                        @if (! $review['is_correct'])
                            <p class="mt-1 text-sm text-teal-600">Jawaban tepat: {{ $review['correct_option'] }}</p>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        <div class="mt-6 rounded-md border border-teal-200 bg-teal-50 px-5 py-4 text-sm text-teal-700">
            Langkah selanjutnya: pelajari materi edukasi pilihan Anda, lalu kerjakan Post-Assessment untuk
            melihat perkembangan skor kesadaran keamanan siber Anda. Post-Assessment akan hadir pada tahap
            berikutnya di C-SHIELD.
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('articles.index') }}"
               class="rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                Pelajari Materi Edukasi
            </a>
            <a href="{{ route('self-assessment.themes') }}"
               class="rounded-md border border-slate-300 px-5 py-3 text-sm font-semibold text-navy-900 transition hover:bg-slate-50">
                Pilih Tema Lain
            </a>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const overallScore = {{ $overallScore }};
            const levelColor = @json($currentStyle['hex']);

            new Chart(document.getElementById('overallScoreChart'), {
                type: 'doughnut',
                data: {
                    labels: ['Skor', 'Sisa'],
                    datasets: [{
                        data: [overallScore, 100 - overallScore],
                        backgroundColor: [levelColor, '#e2e8f0'],
                        borderWidth: 0,
                    }],
                },
                options: {
                    cutout: '75%',
                    plugins: { legend: { display: false }, tooltip: { enabled: false } },
                },
            });
        });
    </script>
@endpush
