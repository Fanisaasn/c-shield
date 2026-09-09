@extends('user.layouts.app')

@section('title', 'Video Materi')
@section('meta_description', 'Tonton video materi edukasi keamanan siber C-SHIELD sebelum mengerjakan Post-Assessment.')

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-3xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 5 dari 8
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                {{ $video->title }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Pelajari materi berikut sebelum melanjutkan ke Post-Assessment untuk tema {{ $category->name }}.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-3xl px-4 py-10 sm:px-6 lg:px-8">
        <div class="rounded-xl border border-slate-200 bg-white p-6">
            @if ($video->description)
                <p class="mb-4 text-sm text-slate-600">{{ $video->description }}</p>
            @endif

            <div class="overflow-hidden rounded-lg bg-navy-950">
                @if ($video->video_path)
                    <video id="materiVideo" controls class="aspect-video w-full" src="{{ asset('storage/'.$video->video_path) }}"></video>
                @elseif ($video->video_url)
                    <iframe id="materiFrame" class="aspect-video w-full" src="{{ $video->embedUrl() }}" title="{{ $video->title }}" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                @endif
            </div>

            <form method="POST" action="{{ route('self-assessment.video.complete') }}" class="mt-6">
                @csrf

                @if ($video->video_path)
                    <p id="videoHint" class="mb-3 text-center text-xs text-slate-400">
                        Tombol lanjut akan aktif otomatis setelah video selesai ditonton.
                    </p>
                @else
                    <label class="mb-3 flex items-center justify-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" id="watchedCheckbox" class="border-slate-300 text-blue-600 focus:ring-blue-600">
                        Saya sudah menonton video materi ini
                    </label>
                @endif

                <button type="submit" id="continueButton" disabled
                        class="w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 disabled:cursor-not-allowed disabled:opacity-50">
                    Lanjut ke Post-Assessment
                </button>
            </form>
        </div>
    </section>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const continueButton = document.getElementById('continueButton');
            const materiVideo = document.getElementById('materiVideo');
            const watchedCheckbox = document.getElementById('watchedCheckbox');

            if (materiVideo) {
                materiVideo.addEventListener('ended', function () {
                    continueButton.disabled = false;
                });
            }

            if (watchedCheckbox) {
                watchedCheckbox.addEventListener('change', function () {
                    continueButton.disabled = ! this.checked;
                });
            }
        });
    </script>
@endpush
