@extends('user.layouts.app')

@section('title', 'Webinar')
@section('meta_description', 'Jadwal webinar keamanan siber dari C-SHIELD Diskominfo Kota Cimahi.')

@section('content')
    <section class="bg-navy-900 py-12">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <h1 class="font-heading text-3xl font-bold text-white">Webinar</h1>
            <p class="mt-2 max-w-2xl text-sm text-slate-300">
                Sesi edukasi keamanan siber terjadwal, daring maupun luring. Daftar langsung melalui tautan
                registrasi resmi pada setiap webinar.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-12 sm:px-6 lg:px-8">
        @if ($webinars->isEmpty())
            <p class="rounded-xl border border-dashed border-slate-300 p-10 text-center text-sm text-slate-500">
                Belum ada webinar yang dijadwalkan.
            </p>
        @else
            <div class="space-y-6">
                @foreach ($webinars as $webinar)
                    @php $isPast = $webinar->webinar_date->isPast(); @endphp
                    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm {{ $isPast ? 'opacity-60' : '' }}">
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="rounded-full bg-blue-600/10 px-3 py-1 text-xs font-semibold text-blue-600">
                                {{ $webinar->webinar_date->translatedFormat('d M Y, H:i') }} WIB
                            </span>
                            @if ($webinar->platform)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600">
                                    {{ $webinar->platform }}
                                </span>
                            @endif
                            @if ($isPast)
                                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-500">Selesai</span>
                            @endif
                        </div>

                        <h2 class="mt-3 font-heading text-xl font-bold text-navy-900">{{ $webinar->title }}</h2>

                        @if ($webinar->speaker)
                            <p class="mt-1 text-sm text-slate-500">Narasumber: {{ $webinar->speaker }}</p>
                        @endif

                        @if ($webinar->description)
                            <p class="mt-3 text-sm leading-relaxed text-slate-600">{{ $webinar->description }}</p>
                        @endif

                        <a href="{{ $webinar->registration_url }}" target="_blank" rel="noopener"
                           class="mt-5 inline-flex items-center gap-2 rounded-md bg-teal-500 px-5 py-2.5 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 {{ $isPast ? 'pointer-events-none opacity-50' : '' }}">
                            Register Now
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-4 w-4">
                                <path d="M7 17 17 7M9 7h8v8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </a>
                    </div>
                @endforeach
            </div>

            <div class="mt-10">
                {{ $webinars->links() }}
            </div>
        @endif
    </section>
@endsection
