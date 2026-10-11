@extends('user.layouts.app')

@section('title', 'Materi Belajar')
@section('meta_description', 'Pelajari materi Social Engineering C-SHIELD sebelum mengerjakan Post-Assessment.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 5 dari 7
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                {{ $material?->title ?? 'Pelajari Materi Terlebih Dahulu' }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Pelajari materi berikut dengan saksama. Post-Assessment baru dapat dibuka setelah Anda mempelajari materi ini.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($material === null)
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
                <p class="font-semibold">Materi belajar belum tersedia.</p>
                <p class="mt-1">Post-Assessment belum dapat dibuka. Silakan kembali lagi nanti.</p>
            </div>
        @else
            @php
                $fileUrl = $material->fileUrl();
                $host = parse_url(url('/'), PHP_URL_HOST);
                $isPublicHost = $host && ! in_array($host, ['localhost', '127.0.0.1'], true) && ! str_ends_with($host, '.test');
            @endphp

            @if ($material->description)
                <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-700">
                    {!! nl2br(e($material->description)) !!}
                </div>
            @endif

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                @if ($material->file_type === 'pdf')
                    <iframe src="{{ $fileUrl }}" title="{{ $material->title }}" class="h-[75vh] w-full"></iframe>
                @elseif ($material->file_type === 'video')
                    <video src="{{ $fileUrl }}" controls controlsList="nodownload" class="w-full bg-black"></video>
                @elseif ($material->file_type === 'ppt' && $isPublicHost)
                    <iframe src="https://view.officeapps.live.com/op/embed.aspx?src={{ urlencode($fileUrl) }}"
                            title="{{ $material->title }}" class="h-[75vh] w-full"></iframe>
                @else
                    <div class="p-8 text-center text-sm text-slate-600">
                        <p>Materi berupa file presentasi. Unduh dan buka file berikut untuk mempelajarinya.</p>
                    </div>
                @endif

                <div class="flex items-center justify-between gap-3 border-t border-slate-200 px-4 py-3 text-xs text-slate-500">
                    <span class="truncate">{{ $material->original_name ?: basename($material->file_path) }}</span>
                    <a href="{{ $fileUrl }}" target="_blank" rel="noopener" class="shrink-0 font-semibold text-blue-600 hover:text-blue-700">
                        Buka / Unduh
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('self-assessment.materi.lanjut') }}" class="mt-8 rounded-xl border border-slate-200 bg-white p-6">
                @csrf

                <div id="study-lock" class="mb-4 flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                    </svg>
                    <span>Post-Assessment terkunci. Pelajari materi di atas, lalu centang konfirmasi di bawah.</span>
                </div>

                <label class="flex items-start gap-3 text-sm text-navy-900">
                    <input type="checkbox" name="confirm_studied" value="1" id="confirm-studied"
                           class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-500 focus:ring-teal-500">
                    <span>Saya sudah mempelajari materi di atas dan siap mengerjakan Post-Assessment.</span>
                </label>

                <button type="submit" id="start-post" disabled
                        class="mt-6 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                    Mulai Post-Assessment
                </button>
            </form>
        @endif
    </section>

@endsection

@if ($material !== null)
    @push('scripts')
        <script>
            (function () {
                const lockBox = document.getElementById('study-lock');
                const checkbox = document.getElementById('confirm-studied');
                const button = document.getElementById('start-post');

                function refresh() {
                    lockBox.classList.toggle('hidden', checkbox.checked);
                    lockBox.classList.toggle('flex', !checkbox.checked);
                    button.disabled = !checkbox.checked;
                }

                checkbox.addEventListener('change', refresh);
                refresh();
            })();
        </script>
    @endpush
@endif
