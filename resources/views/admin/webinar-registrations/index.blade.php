@extends('admin.layouts.app')

@section('title', 'Data Pendaftaran Webinar')

@section('content')
    <div class="space-y-6">
        {{-- Header Halaman --}}
        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-heading text-xl font-bold text-navy-900">Data Pendaftaran Webinar</h2>
                <p class="mt-1 text-sm text-slate-500">
                    Kelola data pendaftar webinar, pantau referensi pendaftar, dan kirim link akses webinar via WhatsApp.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if ($isCloudApiConfigured)
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-700">
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        WhatsApp Cloud API Aktif
                    </span>
                @else
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-800" title="Konfigurasikan WHATSAPP_TOKEN & WHATSAPP_PHONE_NUMBER_ID di .env untuk pengiriman otomatis Meta">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        WhatsApp Cloud API (Belum Dikonfigurasi di .env)
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
                                        <span class="inline-flex items-center text-emerald-600 font-medium" title="Sudah dikirim WA">
                                            ✓ {{ $wb->sent_count }}
                                        </span>
                                        <span class="inline-flex items-center text-amber-600 font-medium" title="Pending / Belum dikirim WA">
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
                                        {{-- WhatsApp Icon --}}
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 shrink-0">
                                            <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67M9.53 7.35C9.35 7.35 9.06 7.41 8.82 7.68C8.58 7.94 7.89 8.58 7.89 9.89C7.89 11.2 8.84 12.46 8.98 12.65C9.11 12.83 10.82 15.5 13.49 16.64C15.69 17.59 16.14 17.4 16.62 17.36C17.09 17.31 18.15 16.73 18.37 16.11C18.59 15.49 18.59 14.96 18.53 14.85C18.47 14.74 18.3 14.68 18.04 14.55C17.78 14.42 16.5 13.79 16.26 13.7C16.03 13.62 15.86 13.57 15.69 13.84C15.02 14.68 14.87 14.85 14.73 15.02 14.58 15.05 14.32 14.92C14.06 14.79 12.96 14.43 11.66 13.27C10.65 12.37 9.97 11.26 9.84 11.04C9.7 10.82 9.82 10.7 9.95 10.57C10.07 10.45 10.22 10.26 10.35 10.11C10.48 9.96 10.53 9.85 10.62 9.68C10.71 9.5 10.66 9.35 10.6 9.22C10.53 9.09 10.01 7.81 9.79 7.28C9.58 6.77 9.37 6.84 9.21 6.83C9.06 6.83 8.88 6.83 8.71 6.83L9.53 7.35Z"/>
                                        </svg>
                                        <span>Kirim Link via WhatsApp</span>
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
                                    <p class="text-xs text-emerald-700">Sudah Terkirim WA</p>
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
                                           placeholder="Cari nama, WhatsApp, email, referensi..."
                                           class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-3 text-sm focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                </div>

                                <select name="status" onchange="this.form.submit()"
                                        class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 focus:border-teal-500 focus:outline-none focus:ring-1 focus:ring-teal-500">
                                    <option value="">Semua Status WA</option>
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
                                            <th class="px-4 py-3">No. WhatsApp</th>
                                            <th class="px-4 py-3">Siapa yang Mereferensikan</th>
                                            <th class="px-4 py-3">Instansi / Profesi</th>
                                            <th class="px-4 py-3 text-center">Status WhatsApp</th>
                                            <th class="px-4 py-3 text-right">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @forelse ($registrations as $index => $reg)
                                            <tr class="hover:bg-slate-50/70 transition" data-reg-id="{{ $reg->id }}" data-reg-name="{{ $reg->name }}" data-reg-phone="{{ $reg->phone }}">
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
                                                        <a href="https://wa.me/{{ $reg->formatted_phone }}" target="_blank"
                                                           class="inline-flex items-center gap-1 font-mono text-emerald-700 hover:text-emerald-800 hover:underline">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-3.5 w-3.5 text-emerald-600">
                                                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67Z"/>
                                                            </svg>
                                                            {{ $reg->phone }}
                                                        </a>
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
                                                    @if ($reg->whatsapp_status === 'sent')
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-semibold text-emerald-800" title="{{ $reg->whatsapp_sent_at ? 'Dikirim: ' . $reg->whatsapp_sent_at->format('d/m/Y H:i') : 'Terkirim' }}">
                                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-3 w-3">
                                                                <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 0 1 .143 1.052l-8 10.5a.75.75 0 0 1-1.127.075l-4.5-4.5a.75.75 0 0 1 1.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 0 1 1.05-.143Z" clip-rule="evenodd"/>
                                                            </svg>
                                                            Terkirim
                                                        </span>
                                                    @elseif ($reg->whatsapp_status === 'failed')
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800" title="{{ $reg->whatsapp_error }}">
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
                                                        {{-- Tombol Kirim WhatsApp Manual / Langsung --}}
                                                        @if ($reg->phone)
                                                            @php
                                                                $cleanLink = $selectedWebinar->meeting_link ?: $selectedWebinar->registration_url ?: 'https://zoom.us';
                                                                $waMsg = "Halo {$reg->name}, berikut link akses webinar *{$selectedWebinar->title}*:\n\n🔗 {$cleanLink}\n\nSampai jumpa!";
                                                                $waDirectUrl = "https://wa.me/{$reg->formatted_phone}?text=" . rawurlencode($waMsg);
                                                            @endphp
                                                            <a href="{{ $waDirectUrl }}" target="_blank"
                                                               class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 transition hover:bg-emerald-100"
                                                               title="Kirim via WhatsApp Web/App">
                                                                Kirim WA
                                                            </a>
                                                        @endif

                                                        {{-- Toggle Status --}}
                                                        <form method="POST" action="{{ route('admin.webinar-registrations.toggle-status', $reg) }}" class="inline">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="rounded-md border border-slate-200 px-2 py-1 text-xs text-slate-600 transition hover:bg-slate-100"
                                                                    title="Ganti status antara Terkirim dan Pending">
                                                                {{ $reg->whatsapp_status === 'sent' ? 'Set Pending' : 'Set Terkirim' }}
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

    {{-- ========== MODAL KIRIM LINK WEBINAR VIA WHATSAPP ========== --}}
    @if ($selectedWebinar)
        <div id="broadcastModal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-navy-950/60 p-4 backdrop-blur-sm">
            <div class="flex min-h-full items-center justify-center p-2 sm:p-4">
                <div class="relative w-full max-w-2xl rounded-2xl bg-white shadow-2xl overflow-hidden transition-all">
                    {{-- Header Modal --}}
                    <div class="flex items-center justify-between border-b border-emerald-600 bg-emerald-600 px-6 py-4 text-white">
                        <div class="flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-6 w-6">
                                <path d="M12.04 2C6.58 2 2.13 6.45 2.13 11.91C2.13 13.66 2.59 15.36 3.45 16.86L2.05 22L7.3 20.62C8.75 21.41 10.38 21.83 12.04 21.83C17.5 21.83 21.95 17.38 21.95 11.92C21.95 9.27 20.92 6.78 19.05 4.91C17.18 3.03 14.69 2 12.04 2M12.05 3.67C14.25 3.67 16.31 4.53 17.87 6.09C19.42 7.65 20.28 9.72 20.28 11.92C20.28 16.46 16.58 20.15 12.04 20.15C10.56 20.15 9.11 19.76 7.85 19L7.55 18.83L4.43 19.65L5.26 16.61L5.06 16.29C4.24 15 3.8 13.47 3.8 11.91C3.81 7.37 7.5 3.67 12.05 3.67Z"/>
                            </svg>
                            <h3 class="font-heading text-lg font-bold">Kirim Link Webinar via WhatsApp</h3>
                        </div>
                        <button type="button" onclick="closeBroadcastModal()" class="rounded-full bg-white/20 p-1 text-white hover:bg-white/30">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                                <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Form Kirim --}}
                    <form method="POST" action="{{ route('admin.webinar-registrations.send-whatsapp', $selectedWebinar) }}" id="broadcastForm" class="p-6 space-y-5">
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
                                       value="{{ old('meeting_link', $selectedWebinar->meeting_link ?: $selectedWebinar->registration_url) }}"
                                       oninput="updatePreview()"
                                       placeholder="https://zoom.us/j/123456789 atau https://meet.google.com/..."
                                       class="block w-full rounded-lg border border-slate-300 pl-10 pr-3 py-2 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 font-mono">
                            </div>
                            <p class="mt-1 text-xs text-slate-500">Link ini akan otomatis disimpan pada webinar dan disisipkan pada pesan.</p>
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

                        {{-- Template Pesan yang Bisa Diedit --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="message_template" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Template Pesan WhatsApp <span class="text-red-500">*</span>
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

                        {{-- PREVIEW SEBELUM MENGIRIM (Realistic WhatsApp Chat Bubble) --}}
                        <div>
                            <p class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                                Preview Tampilan Pesan WhatsApp
                            </p>
                            <div class="rounded-xl border border-slate-200 bg-[#efeae2] p-4">
                                <div class="max-w-md mx-auto sm:mx-0 rounded-lg bg-white p-3.5 shadow-sm border border-black/5 relative">
                                    <div class="text-xs font-semibold text-emerald-800 mb-1 flex items-center justify-between">
                                        <span>C-SHIELD Diskominfo Kota Cimahi</span>
                                        <span class="text-[10px] text-slate-400">WhatsApp Message</span>
                                    </div>
                                    <div id="waPreviewText" class="text-xs sm:text-sm text-slate-800 whitespace-pre-wrap leading-relaxed font-sans">
                                        {{-- Dynamic Preview Content --}}
                                    </div>
                                    <div class="mt-2 text-right">
                                        <span class="text-[10px] text-slate-400">10:00 ✓✓</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Status WhatsApp Cloud API Notice --}}
                        <div class="rounded-lg p-3 text-xs {{ $isCloudApiConfigured ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-amber-50 text-amber-800 border border-amber-200' }}">
                            @if ($isCloudApiConfigured)
                                <p class="font-semibold">✓ WhatsApp Business Cloud API Siap Digunakan</p>
                                <p class="mt-0.5 text-emerald-700">Pesan akan dikirimkan otomatis melalui Meta Graph API ke nomor WhatsApp masing-masing peserta.</p>
                            @else
                                <p class="font-semibold">ℹ Mode Broadcast & Pelacak Status</p>
                                <p class="mt-0.5">WhatsApp Cloud API belum dikonfigurasi di file <code class="font-mono font-bold">.env</code> (variabel: <code class="font-mono">WHATSAPP_TOKEN</code> &amp; <code class="font-mono">WHATSAPP_PHONE_NUMBER_ID</code>). Sistem akan memperbarui status pendaftar menjadi terkirim dan Anda dapat menggunakan tombol <strong>Kirim WA</strong> langsung di tabel untuk membuka WhatsApp Web/App.</p>
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
                                <span>Kirim Pesan Sekarang</span>
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
        sampleName: @json($registrations->first()?->name ?? 'Budi Santoso')
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

    function updatePreview() {
        const template = document.getElementById('message_template')?.value || '';
        const meetingLink = document.getElementById('meeting_link')?.value || 'https://zoom.us/j/...';

        let preview = template
            .replace(/{nama}/g, webinarData.sampleName)
            .replace(/{judul_webinar}/g, webinarData.title)
            .replace(/{tanggal}/g, webinarData.date)
            .replace(/{waktu}/g, webinarData.time)
            .replace(/{platform}/g, webinarData.platform)
            .replace(/{pembicara}/g, webinarData.speaker)
            .replace(/{link_webinar}/g, meetingLink);

        const previewContainer = document.getElementById('waPreviewText');
        if (previewContainer) {
            previewContainer.textContent = preview;
        }
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
            btn.innerHTML = `<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Mengirim Pesan...`;
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
