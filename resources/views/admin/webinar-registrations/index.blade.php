@extends('admin.layouts.app')

@section('title', 'Data Pendaftaran Webinar')

@section('content')
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-xl font-bold text-navy-900">Data Pendaftaran Webinar</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola data pendaftar webinar, pantau referensi pendaftar, dan kirim link akses webinar via email.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($isMailConfigured)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700" title="Pengirim: {{ $mailFromAddress }}">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Email Aktif ({{ strtoupper($mailerName) }})
                    </span>
                @endif
            </div>
        </div>

        @if ($webinars->isEmpty())
            <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="mx-auto h-12 w-12 text-slate-300">
                    <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.6"/>
                    <path d="M8 2v4M16 2v4M3 10h18" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                </svg>
                <h3 class="mt-3 font-heading text-base font-bold text-navy-900">Belum ada webinar</h3>
                <p class="mt-1 text-sm text-slate-500">Silakan tambahkan data webinar terlebih dahulu di menu Webinar.</p>
                <a href="{{ route('admin.webinars.create') }}" class="mt-4 inline-flex items-center gap-2 rounded-lg bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                    + Tambah Webinar
                </a>
            </div>
        @else
            {{-- Layout 2 Kolom: Kiri Daftar Webinar, Kanan Detail Pendaftar --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-12">
                {{-- KOLOM KIRI: Daftar Webinar (Pengelompokan) --}}
                <div class="lg:col-span-4 xl:col-span-4 space-y-3">
                    <div class="flex items-center justify-between px-1">
                        <h3 class="font-heading text-sm font-bold uppercase tracking-wider text-slate-500">
                            Pilih Webinar ({{ $webinars->count() }})
                        </h3>
                    </div>

                    <div class="space-y-3 max-h-[750px] overflow-y-auto pr-1">
                        @foreach ($webinars as $wb)
                            @php
                                $isSelected = $selectedWebinar && $selectedWebinar->id === $wb->id;
                            @endphp
                            <a href="{{ route('admin.webinar-registrations.index', ['webinar_id' => $wb->id]) }}"
                               class="group block rounded-xl border p-4 transition {{ $isSelected ? 'border-teal-500 bg-teal-50/40 shadow-sm ring-2 ring-teal-500/20' : 'border-slate-200 bg-white hover:border-slate-300 hover:shadow-sm' }}">
                                <div class="flex items-start justify-between gap-2">
                                    <h4 class="font-heading text-sm font-bold leading-snug {{ $isSelected ? 'text-teal-900' : 'text-navy-900 group-hover:text-blue-600' }}">
                                        {{ $wb->title }}
                                    </h4>
                                    @if ($isSelected)
                                        <span class="shrink-0 rounded-full bg-teal-500 p-1 text-navy-950">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3.5 w-3.5">
                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                            </svg>
                                        </span>
                                    @endif
                                </div>

                                <div class="mt-2.5 flex flex-wrap items-center gap-1.5 text-xs text-slate-500">
                                    <span class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-0.5 font-medium text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3 w-3">
                                            <rect x="3" y="4" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="M16 2v4M8 2v4M3 10h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        {{ $wb->webinar_date ? $wb->webinar_date->translatedFormat('d M Y, H:i') : '-' }}
                                    </span>
                                    @if ($wb->platform)
                                        <span class="rounded bg-slate-100 px-2 py-0.5 text-slate-600">{{ $wb->platform }}</span>
                                    @endif
                                </div>

                                <div class="mt-3 flex items-center justify-between border-t border-slate-100 pt-2.5 text-xs">
                                    <span class="font-semibold text-navy-900">
                                        {{ $wb->registrations_count }} Peserta
                                    </span>
                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center text-emerald-600 font-medium" title="Sudah dikirim email">
                                            ✓ {{ $wb->sent_count }}
                                        </span>
                                        <span class="inline-flex items-center text-amber-600 font-medium" title="Pending / Belum dikirim email">
                                            ⏳ {{ $wb->pending_count }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>

                {{-- KOLOM KANAN: Detail & Daftar Pendaftar Webinar yang Dipilih --}}
                <div class="lg:col-span-8 xl:col-span-8 space-y-4 min-w-0">
                    @if ($selectedWebinar)
                        {{-- Card Info Webinar Terpilih & Action Bar --}}
                        <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm overflow-hidden">
                            <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
                                <div class="min-w-0 flex-1 space-y-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-semibold text-blue-700">
                                            {{ $selectedWebinar->platform ?? 'Webinar' }}
                                        </span>
                                        <span class="text-xs text-slate-500">
                                            {{ $selectedWebinar->webinar_date ? $selectedWebinar->webinar_date->translatedFormat('l, d F Y - H:i') . ' WIB' : '-' }}
                                        </span>
                                    </div>
                                    <h3 class="font-heading text-lg font-bold text-navy-900 break-words">
                                        {{ $selectedWebinar->title }}
                                    </h3>
                                    @if ($selectedWebinar->speaker)
                                        <p class="text-xs text-slate-500">
                                            🎤 Narasumber: <span class="font-medium text-slate-700">{{ $selectedWebinar->speaker }}</span>
                                        </p>
                                    @endif
                                    @if ($selectedWebinar->meeting_link)
                                        <p class="text-xs text-slate-500 truncate max-w-full">
                                            🔗 Link: <a href="{{ $selectedWebinar->meeting_link }}" target="_blank" class="text-blue-600 underline font-mono">{{ $selectedWebinar->meeting_link }}</a>
                                        </p>
                                    @endif
                                </div>

                                {{-- Action Buttons --}}
                                <div class="flex flex-wrap items-center gap-2 pt-1 xl:pt-0">
                                    <button type="button"
                                            onclick="openBroadcastModal()"
                                            class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-3.5 py-2 sm:px-4 sm:py-2 text-xs sm:text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 shrink-0">
                                            <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                            <path d="m3 7 9 6 9-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Kirim Link via Email</span>
                                    </button>

                                    <a href="{{ route('admin.webinar-registrations.export', $selectedWebinar) }}"
                                       class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs sm:text-sm font-semibold text-slate-700 transition hover:bg-slate-50"
                                       title="Export data peserta ke file CSV/Excel">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-slate-500 shrink-0">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4M7 10l5 5 5-5M12 15V3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                        <span>Export CSV</span>
                                    </a>
                                </div>
                            </div>

                            {{-- Statistik Ringkas Peserta --}}
                            <div class="mt-4 grid grid-cols-2 gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
                                <div class="rounded-lg bg-slate-50 p-3">
                                    <p class="text-xs text-slate-500">Total Pendaftar</p>
                                    <p class="text-lg font-bold text-navy-900">{{ $selectedWebinar->registrations_count }}</p>
                                </div>
                                <div class="rounded-lg bg-emerald-50 p-3">
                                    <p class="text-xs text-emerald-700">Sudah Terkirim Email</p>
                                    <p class="text-lg font-bold text-emerald-800">{{ $selectedWebinar->sent_count }}</p>
                                </div>
                                <div class="rounded-lg bg-amber-50 p-3">
                                    <p class="text-xs text-amber-700">Belum Dikirim (Pending)</p>
                                    <p class="text-lg font-bold text-amber-800">{{ $selectedWebinar->pending_count }}</p>
                                </div>
                                <div class="rounded-lg bg-red-50 p-3">
                                    <p class="text-xs text-red-700">Gagal Kirim</p>
                                    <p class="text-lg font-bold text-red-800">{{ $selectedWebinar->failed_count }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- Search & Filter Bar --}}
                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                            <form method="GET" action="{{ route('admin.webinar-registrations.index') }}" class="flex flex-1 flex-wrap items-center gap-2">
                                <input type="hidden" name="webinar_id" value="{{ $selectedWebinar->id }}">
                                
                                <div class="relative flex-1 min-w-[200px]">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="absolute left-3 top-2.5 h-4 w-4 text-slate-400">
                                        <circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="m21 21-4.35-4.35" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    <input type="text" name="search" value="{{ request('search') }}"
                                           placeholder="Cari nama, email, telepon, referensi..."
                                           class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </div>

                                <select name="status" onchange="this.form.submit()"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="">Semua Status Email</option>
                                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending (Belum)</option>
                                    <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Terkirim</option>
                                    <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Gagal</option>
                                </select>

                                <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-medium text-white transition hover:bg-slate-700">
                                    Filter
                                </button>

                                @if (request('search') || request('status'))
                                    <a href="{{ route('admin.webinar-registrations.index', ['webinar_id' => $selectedWebinar->id]) }}"
                                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">
                                        Reset
                                    </a>
                                @endif
                            </form>
                        </div>

                        {{-- Tabel Daftar Pendaftar --}}
                        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                            <div class="overflow-x-auto">
                                <table class="w-full text-left text-xs sm:text-sm">
                                    <thead class="border-b border-slate-200 bg-slate-50 font-heading text-xs font-bold uppercase tracking-wider text-slate-600">
                                        <tr>
                                            <th class="w-10 px-4 py-3 text-center">
                                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"
                                                       class="h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                            </th>
                                            <th class="px-4 py-3">Nama Pendaftar</th>
                                            <th class="px-4 py-3">No. Telepon</th>
                                            <th class="px-4 py-3">Siapa yang Mereferensikan</th>
                                            <th class="px-4 py-3">Instansi / Profesi</th>
                                            <th class="px-4 py-3 text-center">Status Email</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($registrations as $index => $reg)
                                            <tr class="hover:bg-slate-50/70 transition" data-reg-id="{{ $reg->id }}" data-reg-name="{{ $reg->name }}" data-reg-email="{{ $reg->email }}">
                                                <td class="px-4 py-3 text-center">
                                                    <input type="checkbox" name="selected_participant[]" value="{{ $reg->id }}"
                                                           onchange="updateSelectionCount()"
                                                           class="participant-checkbox h-4 w-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500">
                                                </td>
                                                <td class="px-4 py-3">
                                                    <p class="font-bold text-navy-900">{{ $reg->name }}</p>
                                                    <p class="text-xs text-slate-500">{{ $reg->email }}</p>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if ($reg->phone)
                                                        <span class="font-mono text-slate-700">{{ $reg->phone }}</span>
                                                    @else
                                                        <span class="text-xs text-slate-400">-</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if ($reg->referral_source)
                                                        <span class="inline-flex items-center gap-1 rounded-md bg-purple-50 px-2.5 py-1 text-xs font-semibold text-purple-700">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                                                <path d="M7 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM14.5 9a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM1.615 16.428a1.224 1.224 0 0 1-.569-1.175 6.002 6.002 0 0 1 11.908 0c.058.467-.172.92-.57 1.174A9.953 9.953 0 0 1 7 18a9.953 9.953 0 0 1-5.385-1.572ZM14.5 16h-.106c.07-.297.088-.611.048-.933a7.47 7.47 0 0 0-1.588-3.755 4.502 4.502 0 0 1 5.874 2.636.818.818 0 0 1-.36.98A7.465 7.465 0 0 1 14.5 16Z"/>
                                                            </svg>
                                                            {{ $reg->referral_source }}
                                                        </span>
                                                    @else
                                                        <span class="text-xs text-slate-400 italic">Tidak tersedia / Mandiri</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-xs text-slate-600">
                                                    <p class="font-medium text-slate-800">{{ $reg->institution ?: '-' }}</p>
                                                    <p class="text-slate-400">{{ $reg->occupation ?: '' }}</p>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if ($reg->email_status === 'sent')
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800" title="{{ $reg->email_sent_at ? 'Dikirim: ' . $reg->email_sent_at->format('d/m/Y H:i') : 'Terkirim' }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Terkirim
                                                        </span>
                                                    @elseif ($reg->email_status === 'failed')
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800" title="{{ $reg->email_error }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-5a.75.75 0 0 1 .75.75v4.5a.75.75 0 0 1-1.5 0v-4.5A.75.75 0 0 1 10 5Zm0 10a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Gagal
                                                        </span>
                                                    @else
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-semibold text-amber-800">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                                                <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm.75-13a.75.75 0 0 0-1.5 0v5c0 .414.336.75.75.75h4a.75.75 0 0 0 0-1.5h-3.25V5Z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right">
                                                    <div class="flex items-center justify-end gap-1.5">
                                                        {{-- Tombol Kirim Email ke Satu Peserta --}}
                                                        <form method="POST" action="{{ route('admin.webinar-registrations.send-single', $reg) }}" class="inline"
                                                              data-confirm-email="{{ $reg->email }}"
                                                              onsubmit="return confirm('Kirim email link webinar ke ' + this.dataset.confirmEmail + '?')">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                                                    title="Kirim email link webinar ke peserta ini">
                                                                Kirim Email
                                                            </button>
                                                        </form>

                                                        {{-- Toggle Status --}}
                                                        <form method="POST" action="{{ route('admin.webinar-registrations.toggle-status', $reg) }}" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-600 transition hover:bg-slate-100"
                                                                    title="Ganti status antara Terkirim dan Pending">
                                                                {{ $reg->email_status === 'sent' ? 'Set Pending' : 'Set Terkirim' }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="px-4 py-8 text-center text-sm text-slate-400">
                                                    Tidak ada data pendaftar yang sesuai.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="rounded-xl border border-dashed border-slate-300 bg-white p-12 text-center text-slate-400">
                            Silakan pilih salah satu webinar di sebelah kiri untuk melihat daftar pendaftar.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- ========== MODAL KIRIM LINK WEBINAR VIA EMAIL ========== --}}
    @if ($selectedWebinar)
        <div id="broadcastModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-navy-950/60 p-4 backdrop-blur-sm">
            <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden transition-all">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-emerald-600 bg-emerald-600 px-6 py-4 text-white">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-6 w-6">
                                <rect x="3" y="5" width="18" height="14" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                <path d="m3 7 9 6 9-6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                            <h3 class="font-heading text-lg font-bold">Kirim Link Webinar via Email</h3>
                        </div>
                        <button type="button" onclick="closeBroadcastModal()" class="rounded-full bg-white/20 p-1 text-white hover:bg-white/30">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Form Kirim --}}
                    <form method="POST" action="{{ route('admin.webinar-registrations.send-email', $selectedWebinar) }}" id="broadcastForm" class="p-6 space-y-5">
                        @csrf

                        {{-- Input Link Webinar --}}
                        <div>
                            <label for="meeting_link" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Link Akses Webinar (Zoom / GMeet / YouTube) <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4 text-slate-400">
                                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <input type="text" id="meeting_link" name="meeting_link" required
                                       value="{{ old('meeting_link', $selectedWebinar->meeting_link) }}"
                                       oninput="updatePreview()"
                                       placeholder="https://zoom.us/j/123456789 atau https://meet.google.com/..."
                                       class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Link ini akan otomatis disimpan pada webinar dan disisipkan pada email.</p>
                        </div>

                        {{-- Target Penerima --}}
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Target Penerima Pesan
                            </label>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="radio" name="target" value="all" checked onchange="handleTargetChange(this)"
                                           class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="font-bold text-navy-900">Semua Pendaftar</p>
                                        <p class="text-slate-500">{{ $selectedWebinar->registrations_count }} orang</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="radio" name="target" value="pending_only" onchange="handleTargetChange(this)"
                                           class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="font-bold text-navy-900">Hanya Pending</p>
                                        <p class="text-amber-600 font-semibold">{{ $selectedWebinar->pending_count }} orang</p>
                                    </div>
                                </label>

                                <label class="flex items-center gap-2 rounded-lg border border-slate-200 p-3 cursor-pointer hover:bg-slate-50 transition">
                                    <input type="radio" name="target" value="selected" id="targetSelectedRadio" onchange="handleTargetChange(this)"
                                           class="text-emerald-600 focus:ring-emerald-500">
                                    <div>
                                        <p class="font-bold text-navy-900">Pilihan di Tabel</p>
                                        <p id="selectedCountLabel" class="text-slate-500">0 orang terpilih</p>
                                    </div>
                                </label>
                            </div>
                            <div id="selectedIdsContainer"></div>
                        </div>

                        {{-- Subjek Email --}}
                        <div>
                            <label for="email_subject" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Subjek Email <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="email_subject" name="email_subject" required maxlength="255"
                                   value="{{ old('email_subject', $defaultSubject) }}"
                                   oninput="updatePreview()"
                                   class="mt-1 block w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            <p class="mt-1 text-xs text-slate-500">Tag seperti <code class="font-mono">{judul_webinar}</code> juga bisa dipakai di subjek.</p>
                        </div>

                        {{-- Template Pesan yang Bisa Diedit --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="message_template" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Isi Email <span class="text-red-500">*</span>
                                </label>
                                <span class="text-xs text-slate-400">Klik tag di bawah untuk menyisipkan</span>
                            </div>

                            {{-- Tag Chips --}}
                            <div class="flex flex-wrap gap-1.5 mb-2">
                                @foreach ([
                                    '{nama}' => 'Nama Peserta',
                                    '{judul_webinar}' => 'Judul Webinar',
                                    '{tanggal}' => 'Tanggal',
                                    '{waktu}' => 'Jam Pelaksanaan',
                                    '{platform}' => 'Platform',
                                    '{link_webinar}' => 'Link Webinar',
                                    '{pembicara}' => 'Narasumber',
                                ] as $tag => $label)
                                    <button type="button" onclick="insertTag('{{ $tag }}')"
                                            class="inline-flex items-center gap-1 rounded bg-slate-100 px-2 py-1 text-xs font-mono font-medium text-slate-700 transition hover:bg-teal-100 hover:text-teal-900">
                                        + {{ $tag }}
                                    </button>
                                @endforeach
                            </div>

                            <textarea id="message_template" name="message_template" rows="7" required
                                      oninput="updatePreview()"
                                      class="block w-full rounded-lg border border-slate-300 p-3 text-xs sm:text-sm font-sans focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 leading-relaxed">{{ old('message_template', $defaultTemplate) }}</textarea>
                        </div>

                        {{-- PREVIEW EMAIL SEBELUM MENGIRIM --}}
                        <div>
                            <p class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Preview Email
                            </p>
                            <div class="rounded-xl border border-slate-200 bg-slate-100 p-4">
                                <div class="mx-auto max-w-md overflow-hidden rounded-lg border border-black/5 bg-white shadow-sm">
                                    <div class="border-b border-slate-100 px-4 py-2.5 text-xs text-slate-500 space-y-0.5">
                                        <p><span class="font-semibold text-slate-700">Dari:</span> {{ config('mail.from.name') }} &lt;{{ $mailFromAddress }}&gt;</p>
                                        <p><span class="font-semibold text-slate-700">Kepada:</span> <span id="emailPreviewTo"></span></p>
                                        <p><span class="font-semibold text-slate-700">Subjek:</span> <span id="emailPreviewSubject" class="text-slate-800"></span></p>
                                    </div>
                                    <div class="bg-navy-900 px-4 py-2.5">
                                        <p class="text-sm font-bold text-white">C-SHIELD</p>
                                        <p class="text-[10px] text-teal-400">Diskominfo Kota Cimahi</p>
                                    </div>
                                    <div id="emailPreviewText" class="px-4 py-3 text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed font-sans break-words"></div>
                                    <div class="px-4 pb-4 text-center">
                                        <span class="inline-block rounded-md bg-teal-500 px-4 py-2 text-xs font-bold text-navy-950">Gabung Webinar</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Status Konfigurasi Email --}}
                        <div class="rounded-lg p-3 text-xs {{ $isMailConfigured ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                            @if ($isMailConfigured)
                                <p class="font-semibold">✓ Pengiriman Email Siap Digunakan</p>
                                <p class="mt-0.5 text-emerald-700">Email dikirim dari <strong>{{ $mailFromAddress }}</strong> ke alamat email masing-masing peserta.</p>
                            @else
                                <p class="font-semibold">ℹ Mode Log (Email Tidak Benar-benar Terkirim)</p>
                                <p class="mt-0.5"><code class="font-mono font-bold">MAIL_MAILER</code> di file <code class="font-mono font-bold">.env</code> masih <code class="font-mono">{{ $mailerName }}</code>, jadi email hanya ditulis ke <code class="font-mono">storage/logs</code>. Atur <code class="font-mono">MAIL_MAILER=smtp</code> beserta <code class="font-mono">MAIL_HOST</code>, <code class="font-mono">MAIL_PORT</code>, <code class="font-mono">MAIL_USERNAME</code>, <code class="font-mono">MAIL_PASSWORD</code> dan <code class="font-mono">MAIL_FROM_ADDRESS</code> untuk pengiriman sungguhan.</p>
                            @endif
                        </div>

                        {{-- Action Buttons --}}
                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-slate-100">
                            <button type="button" onclick="closeBroadcastModal()"
                                    class="rounded-lg border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-600 transition hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" id="btnSubmitBroadcast"
                                    class="inline-flex items-center gap-2 rounded-lg bg-emerald-600 px-6 py-2.5 text-sm font-bold text-white shadow-sm transition hover:bg-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                    <path d="M22 2L11 13" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                                <span>Kirim Email Sekarang</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Variables for preview replacement
    const webinarData = {
        title: @json($selectedWebinar?->title ?? ''),
        date: @json($selectedWebinar?->webinar_date ? $selectedWebinar->webinar_date->translatedFormat('d F Y') : '-'),
        time: @json($selectedWebinar?->webinar_date ? $selectedWebinar->webinar_date->translatedFormat('H:i') . ' WIB' : '-'),
        platform: @json($selectedWebinar?->platform ?? 'Online Meeting'),
        speaker: @json($selectedWebinar?->speaker ?? '-'),
        sampleName: @json($registrations->first()?->name ?? 'Budi Santoso'),
        sampleEmail: @json($registrations->first()?->email ?? 'peserta@example.com')
    };

    function openBroadcastModal() {
        const modal = document.getElementById('broadcastModal');
        if (modal) {
            syncSelectedCheckboxesToForm();
            modal.classList.remove('hidden');
            updatePreview();
        }
    }

    function closeBroadcastModal() {
        const modal = document.getElementById('broadcastModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    function insertTag(tag) {
        const textarea = document.getElementById('message_template');
        if (!textarea) return;

        const start = textarea.selectionStart;
        const end = textarea.selectionEnd;
        const text = textarea.value;

        textarea.value = text.substring(0, start) + tag + text.substring(end);
        textarea.selectionStart = textarea.selectionEnd = start + tag.length;
        textarea.focus();

        updatePreview();
    }

    function fillTags(text, meetingLink) {
        return text
            .replace(/{nama}/g, webinarData.sampleName)
            .replace(/{judul_webinar}/g, webinarData.title)
            .replace(/{tanggal}/g, webinarData.date)
            .replace(/{waktu}/g, webinarData.time)
            .replace(/{platform}/g, webinarData.platform)
            .replace(/{pembicara}/g, webinarData.speaker)
            .replace(/{link_webinar}/g, meetingLink);
    }

    function updatePreview() {
        const template = document.getElementById('message_template')?.value || '';
        const subject = document.getElementById('email_subject')?.value || '';
        const meetingLink = document.getElementById('meeting_link')?.value || 'https://zoom.us/j/...';

        const setText = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = value;
        };

        setText('emailPreviewText', fillTags(template, meetingLink));
        setText('emailPreviewSubject', fillTags(subject, meetingLink));
        setText('emailPreviewTo', webinarData.sampleName + ' <' + webinarData.sampleEmail + '>');
    }

    // Toggle Select All Checkboxes in Table
    function toggleSelectAll(masterCheckbox) {
        const checkboxes = document.querySelectorAll('.participant-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = masterCheckbox.checked;
        });
        updateSelectionCount();
    }

    function updateSelectionCount() {
        const checked = document.querySelectorAll('.participant-checkbox:checked');
        const count = checked.length;
        const label = document.getElementById('selectedCountLabel');
        if (label) {
            label.textContent = `${count} orang terpilih`;
        }

        // If items are selected, optionally highlight target radio
        if (count > 0) {
            const radio = document.getElementById('targetSelectedRadio');
            if (radio) {
                radio.disabled = false;
            }
        }
    }

    function syncSelectedCheckboxesToForm() {
        const container = document.getElementById('selectedIdsContainer');
        if (!container) return;

        container.innerHTML = '';
        const checked = document.querySelectorAll('.participant-checkbox:checked');
        checked.forEach(cb => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'selected_ids[]';
            input.value = cb.value;
            container.appendChild(input);
        });
        updateSelectionCount();
    }

    function handleTargetChange(radio) {
        if (radio.value === 'selected') {
            const checked = document.querySelectorAll('.participant-checkbox:checked');
            if (checked.length === 0) {
                alert('Belum ada peserta yang dipilih di tabel. Silakan centang minimal satu peserta di tabel terlebih dahulu.');
            }
        }
    }

    document.getElementById('broadcastForm')?.addEventListener('submit', function (e) {
        const targetRadio = document.querySelector('input[name="target"]:checked');
        if (targetRadio && targetRadio.value === 'selected') {
            syncSelectedCheckboxesToForm();
            const selectedInputs = document.querySelectorAll('#selectedIdsContainer input');
            if (selectedInputs.length === 0) {
                e.preventDefault();
                alert('Silakan pilih minimal satu peserta di tabel sebelum mengirim.');
                return false;
            }
        }

        const btn = document.getElementById('btnSubmitBroadcast');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim Email...`;
        }
    });

    // Close on escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeBroadcastModal();
        }
    });

    // Initial preview render if modal elements exist
    document.addEventListener('DOMContentLoaded', function () {
        updatePreview();
        updateSelectionCount();
    });
</script>
@endpush
