@extends('user.layouts.app')

@section('title', 'Webinar')
@section('meta_description', 'Jadwal webinar keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Webinar</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Jadwal webinar keamanan siber dari C-SHIELD Diskominfo Kota Cimahi. Temukan berbagai informasi dan tips seputar keamanan digital untuk meningkatkan kesadaran dan perlindungan diri di dunia maya.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
        @include('partials.content-search', ['searchRoute' => 'webinars.index', 'searchLabel' => 'webinar'])

        {{-- Flash messages --}}
        @if (session('success'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0">
                    <path d="M22 11.08V12a10 10 0 11-5.93-9.14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <path d="M22 4L12 14.01l-3-3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <p>{{ session('success') }}</p>
            </div>
        @endif
        @if (session('warning'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-sm text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0">
                    <path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="12" y1="9" x2="12" y2="13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="12" y1="17" x2="12.01" y2="17" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <p>{{ session('warning') }}</p>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 flex items-center gap-3 rounded-xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-5 w-5 shrink-0">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="1.6"/>
                    <line x1="15" y1="9" x2="9" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                    <line x1="9" y1="9" x2="15" y2="15" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                </svg>
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($webinars->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                @if ($search !== '')
                    Tidak ada hasil untuk &ldquo;{{ $search }}&rdquo;. Coba kata kunci lain.
                @else
                    Belum ada webinar yang dijadwalkan.
                @endif
            </p>
        @else
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($webinars as $webinar)
                    @php $isPast = $webinar->webinar_date->isPast(); @endphp
                    <article class="flex min-w-0 flex-col overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                        <div class="relative flex aspect-[4/5] items-center justify-center bg-slate-100">
                            @if ($webinar->poster_image)
                                <img src="{{ asset('storage/' . $webinar->poster_image) }}" alt="Poster {{ $webinar->title }}" loading="lazy"
                                     class="h-full w-full object-contain {{ $isPast ? 'grayscale' : '' }}">
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-10 w-10 text-slate-300">
                                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="1.6"/>
                                    <path d="m3 16 5-5 4 4 5-6 4 5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/>
                                </svg>
                            @endif

                            @if ($isPast)
                                <span class="absolute right-2 top-2 rounded-full bg-black/60 px-2.5 py-1 text-xs font-semibold text-white">Selesai</span>
                            @endif
                        </div>

                        <div class="flex flex-1 flex-col p-4">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-600/10 px-3 py-1 text-xs font-semibold text-blue-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                        <rect x="3" y="5" width="18" height="16" rx="2" stroke="currentColor" stroke-width="1.8"/>
                                        <path d="M3 10h18M8 3v4M16 3v4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                    </svg>
                                    {{ $webinar->webinar_date->translatedFormat('d M Y, H:i') }} WIB
                                </span>
                                @if ($webinar->platform)
                                    <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                        {{ $webinar->platform }}
                                    </span>
                                @endif
                            </div>

                            <h2 class="mt-3 line-clamp-2 font-heading text-sm font-bold leading-snug text-navy-900 sm:text-base">{{ $webinar->title }}</h2>

                            @if ($webinar->speaker)
                                <p class="mt-1 line-clamp-2 text-xs text-slate-500">Narasumber: {{ $webinar->speaker }}</p>
                            @endif

                            @if ($webinar->description)
                                @include('partials.rich-text', ['html' => $webinar->description, 'class' => 'mt-3 line-clamp-3 text-sm leading-relaxed text-slate-600'])
                                @if (Str::length(Str::plainText($webinar->description)) > 150)
                                    <button type="button" data-webinar-toggle class="mt-1 self-start text-xs font-semibold text-blue-600 hover:text-blue-700">
                                        Selengkapnya
                                    </button>
                                @endif
                            @endif

                            {{-- Registration count badge --}}
                            @if (!$isPast && $webinar->registrations_count > 0)
                                <p class="mt-2 flex items-center gap-1 text-xs text-slate-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-3.5 w-3.5">
                                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"/>
                                        <circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="1.6"/>
                                    </svg>
                                    {{ $webinar->registrations_count }} peserta terdaftar
                                </p>
                            @endif

                            <div class="mt-auto pt-4">
                                @if ($isPast)
                                    <span class="inline-flex w-full items-center justify-center rounded-md bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-400">
                                        Pendaftaran Ditutup
                                    </span>
                                @else
                                    <button type="button"
                                            onclick="openRegistrationModal({{ $webinar->id }}, {{ json_encode($webinar->title) }})"
                                            class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <circle cx="8.5" cy="7" r="4" stroke="currentColor" stroke-width="1.8"/>
                                            <line x1="20" y1="8" x2="20" y2="14" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                            <line x1="23" y1="11" x2="17" y2="11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                                        </svg>
                                        Daftar Sekarang
                                    </button>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $webinars->links() }}
            </div>
        @endif
    </section>

    {{-- ========== REGISTRATION MODAL ========== --}}
    <div id="registration-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-navy-950/60 p-4 backdrop-blur-sm" style="display:none;">
        <div class="relative w-full max-w-lg animate-[modalSlideUp_0.3s_ease-out] overflow-hidden rounded-2xl bg-white shadow-2xl">
            {{-- Modal header --}}
            <div class="border-b border-slate-200 bg-gradient-to-r from-teal-500 to-teal-600 px-6 py-5">
                <button type="button" onclick="closeRegistrationModal()" class="absolute right-4 top-4 rounded-full bg-white/20 p-1.5 text-white transition hover:bg-white/30">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                        <path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </button>
                <h3 class="font-heading text-lg font-bold text-white">Form Pendaftaran Webinar</h3>
                <p id="modal-webinar-title" class="mt-1 text-sm text-white/80 line-clamp-2"></p>
            </div>

            {{-- Modal body --}}
            <form id="registration-form" method="POST" action="" class="max-h-[70vh] overflow-y-auto p-6">
                @csrf

                <div class="space-y-4">
                    {{-- Nama --}}
                    <div>
                        <label for="reg-name" class="block text-sm font-medium text-navy-900">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" id="reg-name" name="name" required maxlength="255" value="{{ old('name') }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                               placeholder="Masukkan nama lengkap Anda">
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="reg-email" class="block text-sm font-medium text-navy-900">Email <span class="text-red-500">*</span></label>
                        <input type="email" id="reg-email" name="email" required maxlength="255" value="{{ old('email') }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                               placeholder="contoh@email.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Telepon --}}
                    <div>
                        <label for="reg-phone" class="block text-sm font-medium text-navy-900">No. Telepon / WhatsApp</label>
                        <input type="tel" id="reg-phone" name="phone" maxlength="20" value="{{ old('phone') }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                               placeholder="08xx-xxxx-xxxx">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        {{-- Instansi --}}
                        <div>
                            <label for="reg-institution" class="block text-sm font-medium text-navy-900">Instansi / Lembaga</label>
                            <input type="text" id="reg-institution" name="institution" maxlength="255" value="{{ old('institution') }}"
                                   class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                   placeholder="Nama instansi">
                            @error('institution')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pekerjaan --}}
                        <div>
                            <label for="reg-occupation" class="block text-sm font-medium text-navy-900">Pekerjaan</label>
                            <input type="text" id="reg-occupation" name="occupation" maxlength="255" value="{{ old('occupation') }}"
                                   class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                   placeholder="Pekerjaan Anda">
                            @error('occupation')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    {{-- Referensi / Sumber Info --}}
                    <div>
                        <label for="reg-referral" class="block text-sm font-medium text-navy-900">Siapa yang Mereferensikan / Sumber Info <span class="text-xs text-slate-400 font-normal">(Opsional)</span></label>
                        <input type="text" id="reg-referral" name="referral_source" maxlength="255" value="{{ old('referral_source') }}"
                               class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                               placeholder="Contoh: Teman, Rekan Kerja, Instagram Diskominfo, WhatsApp Group, dll.">
                        @error('referral_source')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Motivasi --}}
                    <div>
                        <label for="reg-motivation" class="block text-sm font-medium text-navy-900">Motivasi Mengikuti Webinar</label>
                        <textarea id="reg-motivation" name="motivation" maxlength="1000" rows="3"
                                  class="mt-1 w-full rounded-lg border border-slate-300 px-4 py-2.5 text-sm transition focus:border-teal-500 focus:ring-2 focus:ring-teal-500/20"
                                  placeholder="Apa yang ingin Anda pelajari dari webinar ini?">{{ old('motivation') }}</textarea>
                        @error('motivation')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Submit --}}
                <div class="mt-6 flex items-center gap-3">
                    <button type="submit"
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-lg bg-teal-500 px-6 py-3 text-sm font-bold text-navy-950 transition hover:bg-teal-400 focus:ring-2 focus:ring-teal-500/50 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                            <path d="M22 2L11 13" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M22 2l-7 20-4-9-9-4 20-7z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        Kirim Pendaftaran
                    </button>
                    <button type="button" onclick="closeRegistrationModal()"
                            class="rounded-lg border border-slate-300 px-5 py-3 text-sm font-medium text-slate-600 transition hover:bg-slate-50">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <style>
        @keyframes modalSlideUp {
            from { opacity: 0; transform: translateY(20px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }
    </style>
@endsection

@push('scripts')
    <script>
        // Toggle description
        document.querySelectorAll('[data-webinar-toggle]').forEach((button) => {
            button.addEventListener('click', () => {
                const desc = button.previousElementSibling;
                const expanded = desc.classList.toggle('line-clamp-3') === false;
                button.textContent = expanded ? 'Sembunyikan' : 'Selengkapnya';
            });
        });

        // Registration modal
        const modal = document.getElementById('registration-modal');
        const form = document.getElementById('registration-form');
        const modalTitle = document.getElementById('modal-webinar-title');

        function openRegistrationModal(webinarId, webinarTitle) {
            form.action = '/webinar/' + webinarId + '/register';
            modalTitle.textContent = webinarTitle;
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Focus on first input after animation
            setTimeout(() => {
                document.getElementById('reg-name').focus();
            }, 100);
        }

        function closeRegistrationModal() {
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeRegistrationModal();
        });

        // Close on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && modal.style.display === 'flex') closeRegistrationModal();
        });

        // If there are validation errors, re-open the modal
        @if ($errors->any())
            document.addEventListener('DOMContentLoaded', () => {
                // Try to find the right webinar to open
                const firstBtn = document.querySelector('[onclick^="openRegistrationModal"]');
                if (firstBtn) firstBtn.click();
            });
        @endif
    </script>
@endpush
