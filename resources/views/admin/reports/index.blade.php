@extends('admin.layouts.app')
@section('title', 'Monitoring & Laporan')
@section('content')

{{-- Tab navigation --}}
<div class="flex gap-1 rounded-lg bg-slate-100 p-1">
    <button type="button" data-tab="assessment" class="tab-btn flex-1 rounded-md px-4 py-2.5 text-sm font-semibold transition-all" onclick="switchTab('assessment')">
        <span class="flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2" stroke="currentColor" stroke-width="1.6"/>
                <rect x="9" y="3" width="6" height="4" rx="1" stroke="currentColor" stroke-width="1.6"/>
                <path d="M9 12h6M9 16h4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            Data Assessment
        </span>
    </button>
    <button type="button" data-tab="webinar" class="tab-btn flex-1 rounded-md px-4 py-2.5 text-sm font-semibold transition-all" onclick="switchTab('webinar')">
        <span class="flex items-center justify-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.6"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
            </svg>
            Peserta Webinar
        </span>
    </button>
</div>

{{-- ========== TAB: Assessment ========== --}}
<div id="tab-assessment" class="tab-content mt-5">
    {{-- Filter & export --}}
    <form class="grid items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 md:grid-cols-6" method="GET">
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Tipe</label>
            <select name="type" class="w-full rounded-md border-slate-300 text-sm">
                <option value="">Semua tipe</option>
                <option value="pre" @selected(request('type')==='pre')>Pre-test</option>
                <option value="post" @selected(request('type')==='post')>Post-test</option>
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Tingkat</label>
            <select name="level" class="w-full rounded-md border-slate-300 text-sm">
                <option value="">Semua tingkat</option>
                @foreach($levels as $level)
                    <option @selected(request('level')===$level)>{{ $level }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Dari Tanggal</label>
            <input type="date" name="from" value="{{ request('from') }}" class="w-full rounded-md border-slate-300 text-sm">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Sampai Tanggal</label>
            <input type="date" name="to" value="{{ request('to') }}" class="w-full rounded-md border-slate-300 text-sm">
        </div>
        <button type="submit" class="rounded-md bg-teal-500 px-4 py-2.5 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
            Filter
        </button>
        <a href="{{ route('admin.reports.export-assessment', request()->query()) }}"
           class="inline-flex items-center justify-center gap-2 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Export CSV
        </a>
    </form>

    {{-- Assessment table --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Peserta</th>
                        <th class="px-4 py-3">Jenis Kelamin</th>
                        <th class="px-4 py-3">Usia</th>
                        <th class="px-4 py-3">Pendidikan</th>
                        <th class="px-4 py-3">Domisili</th>
                        <th class="px-4 py-3">Tipe</th>
                        <th class="px-4 py-3">Skor</th>
                        <th class="px-4 py-3">Tingkat</th>
                        <th class="px-4 py-3">Tanggal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($attempts as $attempt)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $attempts->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <p class="font-medium">{{ $attempt->user?->name ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $attempt->user?->gender ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $attempt->user?->age ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $attempt->user?->education ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $attempt->user?->domicile ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $attempt->type === 'pre' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ strtoupper($attempt->type) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold">{{ $attempt->score }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $levelColors = [
                                        'Sangat Rendah' => 'bg-red-100 text-red-700',
                                        'Rendah' => 'bg-orange-100 text-orange-700',
                                        'Cukup' => 'bg-yellow-100 text-yellow-700',
                                        'Baik' => 'bg-blue-100 text-blue-700',
                                        'Sangat Baik' => 'bg-teal-100 text-teal-700',
                                    ];
                                @endphp
                                <span class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold {{ $levelColors[$attempt->level] ?? 'bg-slate-100 text-slate-600' }}">
                                    {{ $attempt->level }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $attempt->completed_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-4 py-8 text-center text-slate-400">Tidak ada hasil assessment yang sesuai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $attempts->links() }}</div>
</div>

{{-- ========== TAB: Webinar Registrations ========== --}}
<div id="tab-webinar" class="tab-content mt-5 hidden">
    {{-- Export --}}
    <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4">
        <div>
            <h3 class="text-sm font-semibold text-navy-900">Data Peserta Webinar</h3>
            <p class="text-xs text-slate-400">Daftar peserta yang mendaftar melalui form registrasi di website.</p>
        </div>
        <a href="{{ route('admin.reports.export-webinar') }}"
           class="inline-flex items-center gap-2 rounded-md border border-emerald-300 bg-emerald-50 px-4 py-2.5 text-sm font-semibold text-emerald-700 transition hover:bg-emerald-100">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <polyline points="7 10 12 15 17 10" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                <line x1="12" y1="15" x2="12" y2="3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
            </svg>
            Export CSV
        </a>
    </div>

    {{-- Webinar registrations table --}}
    <div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">No</th>
                        <th class="px-4 py-3">Webinar</th>
                        <th class="px-4 py-3">Nama</th>
                        <th class="px-4 py-3">Email</th>
                        <th class="px-4 py-3">Telepon</th>
                        <th class="px-4 py-3">Instansi</th>
                        <th class="px-4 py-3">Pekerjaan</th>
                        <th class="px-4 py-3">Motivasi</th>
                        <th class="px-4 py-3">Tanggal Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($webinarRegistrations as $reg)
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="px-4 py-3 text-slate-400">{{ $webinarRegistrations->firstItem() + $loop->index }}</td>
                            <td class="px-4 py-3">
                                <p class="max-w-[200px] truncate font-medium" title="{{ $reg->webinar?->title }}">{{ $reg->webinar?->title ?? '-' }}</p>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $reg->name }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $reg->email }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $reg->phone ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $reg->institution ?? '-' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $reg->occupation ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <p class="max-w-[200px] truncate text-slate-500" title="{{ $reg->motivation }}">{{ $reg->motivation ?? '-' }}</p>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $reg->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-4 py-8 text-center text-slate-400">Belum ada peserta webinar yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $webinarRegistrations->links() }}</div>
</div>

@endsection

@push('scripts')
<script>
    function switchTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(el => {
            el.classList.remove('bg-white', 'text-navy-900', 'shadow-sm');
            el.classList.add('text-slate-500', 'hover:text-slate-700');
        });

        const panel = document.getElementById('tab-' + tab);
        const btn = document.querySelector('[data-tab="' + tab + '"]');
        if (panel) panel.classList.remove('hidden');
        if (btn) {
            btn.classList.add('bg-white', 'text-navy-900', 'shadow-sm');
            btn.classList.remove('text-slate-500', 'hover:text-slate-700');
        }
    }

    // Initialize: show assessment tab by default
    document.addEventListener('DOMContentLoaded', function () {
        switchTab('assessment');
    });
</script>
@endpush
