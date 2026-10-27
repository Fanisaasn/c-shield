@extends('user.layouts.app')

@section('title', 'Materi Belajar')
@section('meta_description', 'Pelajari materi Social Engineering C-SHIELD sebelum mengerjakan Post-Assessment.')

@php
    $allDone = $materials->isNotEmpty() && $currentIndex === null;
    // Only the current (unfinished) material enforces reading/watching; finished ones are just for review.
    $enforce = $shown !== null && $shownIndex === $currentIndex;
@endphp

@section('content')

    <section class="bg-navy-900 py-14">
        <div class="mx-auto max-w-2xl px-4 text-center sm:px-6 lg:px-8">
            <span class="inline-flex items-center gap-2 rounded-full border border-teal-400/30 bg-teal-500/10 px-3 py-1 text-xs font-semibold text-teal-300">
                Self Assessment &mdash; Langkah 5 dari 7
            </span>
            <h1 class="mt-4 font-heading text-2xl font-bold text-white sm:text-3xl">
                {{ $shown?->title ?? 'Pelajari Materi Terlebih Dahulu' }}
            </h1>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-relaxed text-slate-300">
                Pelajari setiap materi secara berurutan sampai selesai. Post-Assessment baru dapat dibuka setelah semua materi selesai dipelajari.
            </p>
        </div>
    </section>

    <section class="mx-auto max-w-4xl px-4 py-10 sm:px-6 lg:px-8">
        @if (session('error'))
            <div class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($materials->isEmpty())
            <div class="rounded-xl border border-amber-200 bg-amber-50 p-6 text-sm text-amber-800">
                <p class="font-semibold">Materi belajar belum tersedia.</p>
                <p class="mt-1">Post-Assessment belum dapat dibuka. Silakan kembali lagi nanti.</p>
            </div>
        @else
            {{-- Daftar materi: selesai, sedang dipelajari, atau terkunci --}}
            <ol class="mb-8 grid gap-2 sm:grid-cols-2">
                @foreach ($materials as $index => $material)
                    @php
                        $isDone = in_array($material->id, $doneIds, true);
                        $isCurrent = $index === $currentIndex;
                        $isShown = $index === $shownIndex;
                        $isOpenable = $isDone || $isCurrent;
                    @endphp
                    <li>
                        @if ($isOpenable)
                            <a href="{{ route('self-assessment.materi', ['langkah' => $index + 1]) }}"
                               @class([
                                   'flex items-center gap-3 rounded-lg border px-4 py-3 text-sm transition',
                                   'border-teal-400 bg-teal-50 ring-1 ring-teal-400' => $isShown,
                                   'border-slate-200 bg-white hover:border-teal-300' => ! $isShown,
                               ])>
                        @else
                            <div class="flex items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-400" aria-disabled="true">
                        @endif
                                <span @class([
                                    'flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                                    'bg-emerald-500 text-white' => $isDone,
                                    'bg-teal-500 text-navy-950' => $isCurrent,
                                    'bg-slate-200 text-slate-500' => ! $isOpenable,
                                ])>
                                    @if ($isDone)
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>
                                    @elseif (! $isOpenable)
                                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/></svg>
                                    @else
                                        {{ $index + 1 }}
                                    @endif
                                </span>
                                <span class="min-w-0">
                                    <span @class(['block truncate font-semibold', 'text-navy-900' => $isOpenable])>{{ $material->title }}</span>
                                    <span class="block text-xs {{ $isOpenable ? 'text-slate-500' : 'text-slate-400' }}">
                                        {{ $material->typeLabel() }} &middot; {{ $isDone ? 'Selesai' : ($isCurrent ? 'Sedang dipelajari' : 'Terkunci') }}
                                    </span>
                                </span>
                        @if ($isOpenable)
                            </a>
                        @else
                            </div>
                        @endif
                    </li>
                @endforeach
            </ol>

            @if ($shown !== null)
                @if ($shown->description)
                    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 text-sm leading-relaxed text-slate-700">
                        {!! nl2br(e($shown->description)) !!}
                    </div>
                @endif

                <div class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                    @if ($shown->file_type === 'pdf')
                        {{-- Same-origin path: PDF.js fetches the file, and an absolute APP_URL (localhost vs 127.0.0.1) would be blocked as cross-origin. --}}
                        <div id="pdf-viewer" data-src="{{ parse_url($shown->fileUrl(), PHP_URL_PATH) }}" class="h-[75vh] overflow-y-auto bg-slate-100 p-3 sm:p-4">
                            <p id="pdf-loading" class="py-10 text-center text-sm text-slate-500">Memuat materi&hellip;</p>
                        </div>
                    @elseif ($shown->file_type === 'video')
                        <video id="material-video" src="{{ $shown->fileUrl() }}" controls playsinline preload="metadata"
                               controlsList="nodownload noplaybackrate" disablePictureInPicture class="w-full bg-black"></video>
                    @elseif ($shown->file_type === 'youtube')
                        <div class="aspect-video bg-black">
                            <div id="material-youtube" data-video-id="{{ $shown->youtubeId() }}" class="h-full w-full"></div>
                        </div>
                    @elseif ($shown->file_type === 'instagram')
                        <div class="flex justify-center bg-slate-50 p-3">
                            <iframe src="{{ $shown->instagramEmbedUrl() }}" title="Video Instagram: {{ $shown->title }}"
                                    class="h-[80vh] max-h-[760px] w-full max-w-md rounded-lg border-0 bg-white" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>
                        </div>
                    @elseif ($shown->isInteractive())
                        <div class="aspect-video bg-navy-950">
                            <iframe id="material-interactive" class="h-full w-full border-0" src="{{ $shown->fileUrl() }}"
                                    title="Video interaktif: {{ $shown->title }}" allow="autoplay; fullscreen"
                                    sandbox="allow-scripts allow-forms allow-modals" allowfullscreen></iframe>
                        </div>
                    @else
                        {{-- Materi presentasi lama (PPT/PPTX) yang diunggah sebelum format ini dinonaktifkan. --}}
                        <div class="p-8 text-center text-sm text-slate-600">
                            <p>Materi berupa file presentasi. Buka file berikut dan pelajari sampai slide terakhir.</p>
                            <a id="material-download" href="{{ $shown->fileUrl() }}" target="_blank" rel="noopener"
                               class="mt-4 inline-block rounded-md bg-navy-900 px-4 py-2 font-semibold text-white">Buka materi</a>
                        </div>
                    @endif
                </div>

                @if ($enforce)
                    <form method="POST" action="{{ route('self-assessment.materi.selesai', $shown) }}" class="mt-6 rounded-xl border border-slate-200 bg-white p-6">
                        @csrf
                        <div id="study-status" class="mb-4 flex items-center gap-2 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800" role="status">
                            <svg class="h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>
                            </svg>
                            <span id="study-status-text">
                                @switch($shown->file_type)
                                    @case('pdf') Baca materi sampai halaman terakhir untuk melanjutkan. @break
                                    @case('video') Tonton video sampai selesai untuk melanjutkan. Video tidak dapat dipercepat atau dilewati. @break
                                    @case('interactive') Selesaikan video interaktif sampai akhir untuk melanjutkan. @break
                                    @case('youtube') Tonton video sampai selesai untuk melanjutkan. Video tidak dapat dipercepat atau dilewati. @break
                                    @case('instagram') Putar dan tonton video sampai selesai. Tombol lanjut aktif setelah {{ $shown->durationLabel() ?? 'durasi video' }} berlalu. @break
                                    @default Buka materi di atas untuk melanjutkan.
                                @endswitch
                            </span>
                        </div>
                        <button type="submit" id="finish-material" disabled
                                class="w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                            {{ $shownIndex === $materials->count() - 1 ? 'Selesai mempelajari materi' : 'Lanjut ke materi berikutnya' }}
                        </button>
                    </form>
                @elseif ($currentIndex !== null)
                    <a href="{{ route('self-assessment.materi') }}"
                       class="mt-6 block w-full rounded-md bg-teal-500 px-5 py-3 text-center text-sm font-semibold text-navy-950 transition hover:bg-teal-400">
                        Kembali ke materi yang sedang dipelajari
                    </a>
                @endif
            @endif

            @if ($allDone)
                <form method="POST" action="{{ route('self-assessment.materi.lanjut') }}" class="mt-8 rounded-xl border border-slate-200 bg-white p-6">
                    @csrf
                    <p class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        Semua materi sudah selesai dipelajari. Anda dapat membuka kembali materi di atas sebelum memulai Post-Assessment.
                    </p>

                    <label class="flex items-start gap-3 text-sm text-navy-900">
                        <input type="checkbox" name="confirm_studied" value="1" id="confirm-studied"
                               class="mt-0.5 h-4 w-4 rounded border-slate-300 text-teal-500 focus:ring-teal-500">
                        <span>Saya sudah mempelajari semua materi dan siap mengerjakan Post-Assessment.</span>
                    </label>

                    <button type="submit" id="start-post" disabled
                            class="mt-6 w-full rounded-md bg-teal-500 px-5 py-3 text-sm font-semibold text-navy-950 transition hover:bg-teal-400 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-400">
                        Mulai Post-Assessment
                    </button>
                </form>
            @endif
        @endif
    </section>

@endsection

@push('scripts')
    @if ($allDone)
        <script>
            (function () {
                const checkbox = document.getElementById('confirm-studied');
                const button = document.getElementById('start-post');
                const refresh = () => { button.disabled = !checkbox.checked; };
                checkbox.addEventListener('change', refresh);
                refresh();
            })();
        </script>
    @endif

    @if ($shown?->file_type === 'pdf')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    @endif

    @if ($shown !== null)
        <script>
            (function () {
                const enforce = @json($enforce);
                const finishButton = document.getElementById('finish-material');
                const statusBox = document.getElementById('study-status');
                const statusText = document.getElementById('study-status-text');

                function setStatus(text) {
                    if (statusText) statusText.textContent = text;
                }

                function unlock(text) {
                    if (!finishButton) return;
                    finishButton.disabled = false;
                    statusBox.className = 'mb-4 flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700';
                    setStatus(text);
                }

                // PDF: render every page and require each one to have been scrolled into view.
                const pdfViewer = document.getElementById('pdf-viewer');
                if (pdfViewer) {
                    const loading = document.getElementById('pdf-loading');

                    if (!window.pdfjsLib) {
                        loading.textContent = 'Penampil materi gagal dimuat. Periksa koneksi internet lalu muat ulang halaman.';
                        return;
                    }

                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

                    pdfjsLib.getDocument(pdfViewer.dataset.src).promise.then(async function (pdf) {
                        loading.remove();
                        const total = pdf.numPages;
                        const seen = new Set();
                        const firstPage = await pdf.getPage(1);
                        const baseViewport = firstPage.getViewport({ scale: 1 });

                        const reportProgress = () => {
                            if (!enforce || finishButton.disabled === false) return;
                            if (seen.size >= total) {
                                unlock('Semua halaman sudah dibaca. Anda dapat melanjutkan.');
                                return;
                            }
                            const unread = [];
                            for (let n = 1; n <= total && unread.length < 5; n++) if (!seen.has(n)) unread.push(n);
                            setStatus(`Halaman dibaca: ${seen.size} dari ${total}. Belum dibaca: halaman ${unread.join(', ')}${total - seen.size > unread.length ? ', …' : ''}.`);
                        };

                        const renderPage = async (holder) => {
                            if (holder.dataset.rendered) return;
                            holder.dataset.rendered = '1';
                            const page = await pdf.getPage(Number(holder.dataset.page));
                            const ratio = window.devicePixelRatio || 1;
                            const viewport = page.getViewport({ scale: holder.clientWidth / page.getViewport({ scale: 1 }).width });
                            const canvas = document.createElement('canvas');
                            canvas.width = Math.floor(viewport.width * ratio);
                            canvas.height = Math.floor(viewport.height * ratio);
                            canvas.className = 'block h-auto w-full';
                            holder.style.aspectRatio = `${viewport.width} / ${viewport.height}`;
                            holder.replaceChildren(canvas);
                            await page.render({
                                canvasContext: canvas.getContext('2d'),
                                viewport,
                                transform: ratio !== 1 ? [ratio, 0, 0, ratio, 0, 0] : null,
                            }).promise;
                        };

                        // Render pages shortly before they scroll into view.
                        const renderObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => { if (entry.isIntersecting) renderPage(entry.target); });
                        }, { root: pdfViewer, rootMargin: '600px 0px' });

                        // A page counts as read once half of it (or half the viewer, for tall pages) has been on screen.
                        const seenObserver = new IntersectionObserver((entries) => {
                            entries.forEach(entry => {
                                if (!entry.isIntersecting) return;
                                const visible = entry.intersectionRect.height;
                                if (entry.intersectionRatio >= 0.5 || visible >= entry.rootBounds.height * 0.5) {
                                    seen.add(Number(entry.target.dataset.page));
                                    seenObserver.unobserve(entry.target);
                                }
                            });
                            reportProgress();
                        }, { root: pdfViewer, threshold: [0, 0.25, 0.5, 0.75, 1] });

                        for (let n = 1; n <= total; n++) {
                            const holder = document.createElement('div');
                            holder.dataset.page = n;
                            holder.className = 'mx-auto mb-3 w-full max-w-3xl bg-white shadow-sm';
                            holder.style.aspectRatio = `${baseViewport.width} / ${baseViewport.height}`;
                            holder.setAttribute('aria-label', `Halaman ${n} dari ${total}`);
                            pdfViewer.appendChild(holder);
                            renderObserver.observe(holder);
                            seenObserver.observe(holder);
                        }

                        reportProgress();
                    }).catch(function () {
                        loading.textContent = 'Materi gagal dimuat. Muat ulang halaman, atau hubungi admin bila masalah berlanjut.';
                    });
                }

                // Video: no seeking ahead of what has been watched, no speed-up, must reach the end.
                const video = document.getElementById('material-video');
                if (video && enforce) {
                    let watched = 0;

                    video.addEventListener('timeupdate', () => {
                        // Only grow through normal playback, not through a jump.
                        if (!video.seeking && video.currentTime > watched && video.currentTime - watched < 2) {
                            watched = video.currentTime;
                        }
                    });
                    video.addEventListener('seeking', () => {
                        if (video.currentTime > watched + 0.5) {
                            video.currentTime = watched;
                        }
                    });
                    video.addEventListener('ratechange', () => {
                        if (video.playbackRate !== 1) video.playbackRate = 1;
                    });
                    video.addEventListener('ended', () => {
                        unlock('Video sudah selesai ditonton. Anda dapat melanjutkan.');
                    });
                }

                // YouTube: same rules as an uploaded video, enforced through the IFrame Player API.
                const youtubeHolder = document.getElementById('material-youtube');
                if (youtubeHolder) {
                    window.onYouTubeIframeAPIReady = () => {
                        let watched = 0;
                        let finished = false;
                        const player = new YT.Player(youtubeHolder, {
                            width: '100%',
                            height: '100%',
                            videoId: youtubeHolder.dataset.videoId,
                            playerVars: { rel: 0, playsinline: 1, modestbranding: 1, disablekb: enforce ? 1 : 0 },
                            events: {
                                onPlaybackRateChange: () => {
                                    if (enforce && player.getPlaybackRate() !== 1) player.setPlaybackRate(1);
                                },
                                onStateChange: (event) => {
                                    if (!enforce || event.data !== YT.PlayerState.ENDED) return;
                                    // Only counts if the end was reached by watching, not by jumping there.
                                    if (watched >= player.getDuration() - 3) {
                                        finished = true;
                                        unlock('Video sudah selesai ditonton. Anda dapat melanjutkan.');
                                    } else {
                                        player.seekTo(watched, true);
                                    }
                                },
                            },
                        });

                        if (!enforce) return;

                        setInterval(() => {
                            if (finished || typeof player.getCurrentTime !== 'function') return;
                            const time = player.getCurrentTime();
                            if (time > watched + 2) {
                                player.seekTo(watched, true);
                            } else if (time > watched) {
                                watched = time;
                            }
                        }, 500);
                    };

                    const script = document.createElement('script');
                    script.src = 'https://www.youtube.com/iframe_api';
                    document.head.appendChild(script);
                }

                // Instagram reports nothing about playback, so the video's length is required to pass instead.
                // Counted from when the server first saw it opened, so reloading the page does not restart it.
                const instagramWait = @json($shown->file_type === 'instagram' && $enforce ? max(0, (int) $shown->duration_seconds - (int) $secondsSinceOpened) : null);
                if (instagramWait !== null) {
                    let remaining = instagramWait;
                    const tick = () => {
                        if (remaining <= 0) {
                            unlock('Waktu menonton sudah terpenuhi. Pastikan video sudah ditonton sampai selesai, lalu lanjutkan.');
                            return;
                        }
                        setStatus(`Putar dan tonton video sampai selesai. Tombol lanjut aktif dalam ${Math.floor(remaining / 60)}:${String(remaining % 60).padStart(2, '0')}.`);
                        remaining -= 1;
                        setTimeout(tick, 1000);
                    };
                    tick();
                }

                // Interactive video: the package reports when it has been completed.
                const interactive = document.getElementById('material-interactive');
                if (interactive && enforce) {
                    window.addEventListener('message', (event) => {
                        if (event.source === interactive.contentWindow && event.data && event.data.type === 'cshield:interactive-complete') {
                            unlock('Video interaktif sudah diselesaikan. Anda dapat melanjutkan.');
                        }
                    });
                }

                // Legacy presentation: can only be required to be opened.
                const download = document.getElementById('material-download');
                if (download && enforce) {
                    download.addEventListener('click', () => unlock('Pelajari materi sampai slide terakhir, lalu lanjutkan.'));
                }
            })();
        </script>
    @endif
@endpush
