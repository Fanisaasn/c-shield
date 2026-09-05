@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
    <div class="rounded-xl border border-slate-200 bg-white p-8">
        <h2 class="font-heading text-xl font-bold text-navy-900">
            Selamat datang, {{ auth('admin')->user()->name }}
        </h2>
        <p class="mt-2 max-w-xl text-sm text-slate-500">
            Ringkasan konten dan hasil self-assessment C-SHIELD saat ini.
        </p>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($stats as $stat)
            <div class="rounded-xl border border-slate-200 bg-white p-5">
                <p class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $stat['total'] }}</p>
                <p class="mt-1 text-xs text-slate-500">{{ $stat['published'] }} dipublikasikan</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Peserta Self Assessment</p>
            <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $assessmentStats['participants'] }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-400">Total Pengerjaan Assessment</p>
            <p class="mt-2 font-heading text-3xl font-bold text-navy-900">{{ $assessmentStats['attempts'] }}</p>
        </div>
    </div>

    {{-- Statistik hasil assessment seluruh peserta --}}
    <div class="mt-8">
        <h2 class="font-heading text-lg font-bold text-navy-900">Statistik Hasil Assessment</h2>
        <p class="mt-1 text-sm text-slate-500">Rekap hasil self-assessment seluruh peserta pada tahun {{ $currentYear }}.</p>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Persentase Hasil Assessment</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($levelDistribution->sum('count') === 0)
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="levelDistributionChart" role="img" aria-label="Diagram persentase peserta per tingkat kesadaran keamanan siber"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Nilai Per-Kategori</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($scoreByCategory->isEmpty())
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="categoryChart" role="img" aria-label="Diagram rata-rata skor berdasarkan kategori/tema assessment"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Assessment Berdasarkan Jenis Kelamin</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($scoreByGender->isEmpty())
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="genderChart" role="img" aria-label="Diagram rata-rata skor berdasarkan jenis kelamin"></canvas>
                </div>
            @endif
        </div>
    </div>

    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Assessment Berdasarkan Pendidikan</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($scoreByEducation->isEmpty())
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="educationChart" role="img" aria-label="Diagram rata-rata skor berdasarkan pendidikan"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Assessment Berdasarkan Umur</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($scoreByAge->isEmpty())
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="ageChart" role="img" aria-label="Diagram rata-rata skor berdasarkan kelompok umur"></canvas>
                </div>
            @endif
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-5">
            <p class="text-sm font-semibold text-navy-900">Assessment Berdasarkan Domisili</p>
            <p class="text-xs text-slate-400">Tahun {{ $currentYear }}</p>

            @if ($scoreByDomicile->isEmpty())
                <p class="mt-6 py-6 text-center text-sm text-slate-400">Belum ada data.</p>
            @else
                <div class="mt-4 h-56">
                    <canvas id="domicileChart" role="img" aria-label="Diagram rata-rata skor berdasarkan domisili"></canvas>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const levelColors = {
                'Sangat Rendah': '#dc2626',
                'Rendah': '#f97316',
                'Cukup': '#eab308',
                'Baik': '#1e5aa8',
                'Sangat Baik': '#2ab7ca',
            };

            const levelDistribution = @json($levelDistribution).filter(function (row) { return row.count > 0; });
            const levelEl = document.getElementById('levelDistributionChart');
            if (levelEl) {
                const total = levelDistribution.reduce(function (sum, row) { return sum + row.count; }, 0);

                new Chart(levelEl, {
                    type: 'doughnut',
                    data: {
                        labels: levelDistribution.map(function (row) { return row.label; }),
                        datasets: [{
                            data: levelDistribution.map(function (row) { return row.count; }),
                            backgroundColor: levelDistribution.map(function (row) { return levelColors[row.label] || '#94a3b8'; }),
                            borderWidth: 0,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom', labels: { color: '#0b2545', usePointStyle: true, boxWidth: 8, font: { size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const percent = total ? Math.round((ctx.parsed / total) * 100) : 0;
                                        return ctx.label + ': ' + percent + '% (' + ctx.parsed + ' peserta)';
                                    },
                                },
                            },
                        },
                    },
                });
            }

            function renderAverageChart(elementId, rows, label) {
                const el = document.getElementById(elementId);
                if (!el) return;

                new Chart(el, {
                    type: 'bar',
                    data: {
                        labels: rows.map(function (row) { return row.label; }),
                        datasets: [{
                            label: label,
                            data: rows.map(function (row) { return row.average; }),
                            backgroundColor: '#1e5aa8',
                            borderRadius: 4,
                            maxBarThickness: 40,
                        }],
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: { beginAtZero: true, max: 100, ticks: { color: '#94a3b8' }, grid: { color: '#e2e8f0' } },
                            y: { ticks: { color: '#0b2545', font: { size: 11 } }, grid: { display: false } },
                        },
                        plugins: { legend: { display: false } },
                    },
                });
            }

            renderAverageChart('categoryChart', @json($scoreByCategory), 'Rata-rata Skor');
            renderAverageChart('genderChart', @json($scoreByGender), 'Rata-rata Skor');
            renderAverageChart('educationChart', @json($scoreByEducation), 'Rata-rata Skor');
            renderAverageChart('ageChart', @json($scoreByAge), 'Rata-rata Skor');
            renderAverageChart('domicileChart', @json($scoreByDomicile), 'Rata-rata Skor');
        });
    </script>
@endpush
