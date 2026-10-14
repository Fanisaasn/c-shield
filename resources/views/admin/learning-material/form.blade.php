@extends('admin.layouts.app')
@section('title', $material->exists ? 'Edit Materi Belajar' : 'Tambah Materi Belajar')
@section('content')
@php
    $kind = old('kind', $material->isInteractive() ? 'interactive' : ($material->isLink() ? 'link' : 'file'));
@endphp
<form method="POST" enctype="multipart/form-data"
      action="{{ $material->exists ? route('admin.learning-material.update', $material) : route('admin.learning-material.store') }}"
      class="max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @if ($material->exists) @method('PUT') @endif

    <div>
        <label class="text-sm font-medium" for="title">Judul materi</label>
        <input id="title" name="title" value="{{ old('title', $material->title) }}" class="mt-1 w-full rounded-md border-slate-300" required>
        @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-sm font-medium" for="description">Ringkasan / pengantar <span class="text-slate-400">(opsional)</span></label>
        <textarea id="description" name="description" rows="4" class="mt-1 w-full rounded-md border-slate-300">{{ old('description', $material->description) }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="text-sm font-medium" for="material-kind">Jenis materi</label>
        <select id="material-kind" name="kind" class="mt-1 w-full rounded-md border-slate-300" required>
            <option value="file" @selected($kind === 'file')>File (PDF atau video MP4/WebM)</option>
            <option value="interactive" @selected($kind === 'interactive')>Video interaktif (HTML/CSS/JS)</option>
            <option value="link" @selected($kind === 'link')>Link video YouTube / Instagram</option>
        </select>
        @error('kind')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="file-fields">
        <label class="text-sm font-medium" for="file">
            File materi
            @if ($material->file_path && ! $material->isInteractive())<span class="text-slate-400">(kosongkan bila tidak ingin mengganti)</span>@endif
        </label>
        <input id="file" type="file" name="file" accept=".pdf,.mp4,.webm" class="mt-1 block w-full text-sm">
        <input id="duration-seconds" type="hidden" name="duration_seconds" value="">
        <p class="mt-1 text-xs text-slate-400">Format: PDF atau video MP4/WebM. Maksimal 100 MB. Untuk PPT/PPTX, simpan dulu sebagai PDF.</p>
        @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="interactive-fields" class="space-y-2 rounded-lg border border-teal-200 bg-teal-50 p-4">
        <label class="text-sm font-semibold text-navy-900" for="interactive-zip">
            Paket video interaktif (ZIP)
            @if ($material->isInteractive())<span class="font-normal text-slate-500">(kosongkan bila tidak ingin mengganti)</span>@endif
        </label>
        <input id="interactive-zip" type="file" accept=".zip,application/zip" name="interactive_zip" class="block w-full text-sm">
        <p class="text-xs leading-5 text-slate-600">
            Sama seperti paket di Video Edukasi: ZIP wajib berisi <code>index.html</code> beserta CSS, JavaScript, gambar, atau audio yang digunakan. Maksimal 39 MB.
        </p>
        <p class="text-xs leading-5 text-slate-600">
            Agar responden bisa lanjut, paket harus memberi tahu bahwa video sudah selesai dengan menjalankan
            <code>parent.postMessage({ type: 'cshield:interactive-complete' }, '*')</code> di akhir. Paket bawaan "Jaga Data, Jaga Diri" sudah melakukannya.
        </p>
        <p class="text-xs font-medium text-amber-700">Hanya unggah paket dari sumber tepercaya. Script dijalankan dalam iframe terisolasi.</p>
        @error('interactive_zip')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div id="link-fields" class="space-y-4">
        <div>
            <label class="text-sm font-medium" for="external-url">Link video</label>
            <input id="external-url" type="url" name="external_url" value="{{ old('external_url', $material->external_url) }}"
                   placeholder="https://youtube.com/watch?v=... atau https://instagram.com/reel/..." class="mt-1 w-full rounded-md border-slate-300">
            <p class="mt-1 text-xs text-slate-400">YouTube (video, Shorts) atau Instagram (post/reel publik).</p>
            @error('external_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="text-sm font-medium" for="link-duration">Durasi video <span class="text-slate-400">(menit:detik, misalnya 1:30)</span></label>
            <input id="link-duration" name="link_duration" value="{{ old('link_duration', $material->isLink() ? $material->durationLabel() : null) }}"
                   placeholder="1:30" inputmode="numeric" class="mt-1 w-40 rounded-md border-slate-300">
            <p id="link-duration-help" class="mt-1 text-xs text-slate-400">
                YouTube: terisi otomatis setelah link dimasukkan; responden tidak dapat mempercepat atau melewati video.
                Instagram: wajib diisi. Instagram tidak memberi tahu kapan video selesai, jadi responden baru bisa lanjut setelah durasi ini berlalu.
            </p>
            @error('link_duration')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    @if ($material->file_path)
        <p class="text-xs text-slate-500">
            File saat ini ({{ $material->typeLabel() }}):
            <a href="{{ $material->fileUrl() }}" target="_blank" rel="noopener" class="text-teal-700 underline">{{ $material->original_name ?: basename($material->file_path) }}</a>
        </p>
    @endif

    <div class="flex items-center gap-3">
        <button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold">Simpan materi</button>
        <a href="{{ route('admin.learning-material.index') }}" class="text-sm text-slate-500">Batal</a>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const kind = document.getElementById('material-kind');
    const fileFields = document.getElementById('file-fields');
    const interactiveFields = document.getElementById('interactive-fields');
    const fileInput = document.getElementById('file');
    const durationInput = document.getElementById('duration-seconds');

    const linkFields = document.getElementById('link-fields');
    const urlInput = document.getElementById('external-url');
    const linkDuration = document.getElementById('link-duration');

    const syncFields = () => {
        [[fileFields, 'file'], [interactiveFields, 'interactive'], [linkFields, 'link']].forEach(([fields, value]) => {
            fields.hidden = kind.value !== value;
            fields.querySelectorAll('input').forEach(input => input.disabled = kind.value !== value);
        });
    };
    kind.addEventListener('change', syncFields);
    syncFields();

    // Fill in a YouTube video's length by briefly loading it in a hidden player.
    const youtubeId = (url) => (url.match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|embed\/|shorts\/|live\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i) || [])[1];
    let probeHolder = null;
    const probeYoutube = (id) => {
        const run = () => {
            probeHolder?.remove();
            probeHolder = document.createElement('div');
            probeHolder.style.cssText = 'position:absolute;width:1px;height:1px;overflow:hidden;left:-9999px';
            const target = document.createElement('div');
            probeHolder.appendChild(target);
            document.body.appendChild(probeHolder);
            const player = new YT.Player(target, {
                videoId: id,
                events: {
                    onReady: () => {
                        const seconds = Math.floor(player.getDuration());
                        if (seconds > 0) linkDuration.value = `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;
                        player.destroy();
                    },
                },
            });
        };
        if (window.YT && window.YT.Player) return run();
        window.onYouTubeIframeAPIReady = run;
        if (!document.getElementById('yt-iframe-api')) {
            const script = document.createElement('script');
            script.id = 'yt-iframe-api';
            script.src = 'https://www.youtube.com/iframe_api';
            document.head.appendChild(script);
        }
    };
    urlInput.addEventListener('change', () => {
        const id = youtubeId(urlInput.value);
        if (id) probeYoutube(id);
    });

    // Measure a chosen video's length so the server can check respondents watched it in full.
    fileInput.addEventListener('change', () => {
        durationInput.value = '';
        const file = fileInput.files[0];
        if (!file || !file.type.startsWith('video/')) return;

        const probe = document.createElement('video');
        probe.preload = 'metadata';
        probe.onloadedmetadata = () => {
            if (Number.isFinite(probe.duration)) durationInput.value = Math.floor(probe.duration);
            URL.revokeObjectURL(probe.src);
        };
        probe.src = URL.createObjectURL(file);
    });
});
</script>
@endpush
