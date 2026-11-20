@extends('admin.layouts.app')
@section('title', ($item->exists ? 'Edit ' : 'Tambah ').$title)
@section('content')
@vite('resources/js/admin-editor.js')
<form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.'.$route.'.update',$item) : route('admin.'.$route.'.store') }}" class="max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">@csrf @if($item->exists) @method('PUT') @endif
<div><label class="text-sm font-medium">Judul</label><input name="title" value="{{ old('title',$item->title) }}" class="mt-1 w-full rounded-md border-slate-300" required>@error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror</div>
@if($slug)<div><label class="text-sm font-medium">Slug <span class="text-slate-400">(otomatis bila kosong)</span></label><input name="slug" value="{{ old('slug',$item->slug) }}" class="mt-1 w-full rounded-md border-slate-300">@error('slug')<p class="mt-1 text-xs text-red-600">{{ $message === 'validation.unique' || str_contains($message, 'taken') ? 'Slug ini sudah dipakai artikel lain. Ganti slug atau kosongkan agar dibuat otomatis.' : $message }}</p>@enderror</div>@endif
@if($route === 'articles')
<div><label class="text-sm font-medium">Ringkasan</label><textarea name="excerpt" class="mt-1 w-full rounded-md border-slate-300">{{ old('excerpt',$item->excerpt) }}</textarea></div>
@include('admin.partials.rich-editor', ['name' => 'content', 'label' => 'Isi artikel', 'value' => $item->content, 'minHeight' => '18rem'])
<div>
    <label class="text-sm font-medium">Link sumber asli <span class="text-slate-400">(opsional)</span></label>
    <input type="url" name="source_url" value="{{ old('source_url',$item->source_url) }}" placeholder="https://instagram.com/p/..." class="mt-1 w-full rounded-md border-slate-300">
    @error('source_url')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
@endif
@if($route === 'videos')
@php($selectedVideoType = old('type', $item->type ?: 'normal'))
<div>
    <label class="text-sm font-medium" for="video-type">Jenis video</label>
    <select id="video-type" name="type" class="mt-1 w-full rounded-md border-slate-300" required>
        <option value="normal" @selected($selectedVideoType === 'normal')>Video biasa</option>
        <option value="interactive" @selected($selectedVideoType === 'interactive')>Video interaktif (HTML/CSS/JS)</option>
    </select>
    <p class="mt-1 text-xs text-slate-400">Video biasa menggunakan link atau MP4/WebM/MOV. Video interaktif menggunakan paket ZIP.</p>
    @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
<div id="normal-video-fields" class="space-y-5">
<div>
    <label class="text-sm font-medium">Link video (Instagram/YouTube/sumber lain) <span class="text-slate-400">(opsional bila unggah file)</span></label>
    <input type="url" name="video_url" value="{{ old('video_url',$item->video_url) }}" placeholder="https://instagram.com/reel/... atau https://youtube.com/watch?v=..." class="mt-1 w-full rounded-md border-slate-300">
    @error('video_url')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
<div>
    <label class="text-sm font-medium" for="video">File video <span class="text-slate-400">(opsional bila isi link di atas)</span></label>
    <input id="video" type="file" accept=".mp4,.webm,.mov,video/mp4,video/webm,video/quicktime" name="video" class="mt-1 block w-full text-sm">
    @error('video')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @if($item->video_path)
        <p class="mt-1 text-xs text-slate-500">File saat ini: <a href="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($item->video_path) }}" target="_blank" rel="noopener" class="text-teal-700 underline">{{ basename($item->video_path) }}</a></p>
    @endif
</div>
</div>
<div id="interactive-video-fields" class="space-y-2 rounded-lg border border-teal-200 bg-teal-50 p-4">
    <label class="text-sm font-semibold text-navy-900" for="interactive-zip">Paket video interaktif (ZIP)</label>
    <input id="interactive-zip" type="file" accept=".zip,application/zip" name="interactive_zip" class="block w-full text-sm">
    <p class="text-xs leading-5 text-slate-600">ZIP wajib berisi <code>index.html</code> beserta CSS, JavaScript, gambar, audio, atau video yang digunakan. Path relatif di dalam paket akan dipertahankan. Maksimal 39 MB.</p>
    <p class="text-xs font-medium text-amber-700">Hanya unggah paket dari sumber tepercaya. Script dijalankan dalam iframe terisolasi.</p>
    @error('interactive_zip')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
    @if($item->interactive_path)
        <p class="text-xs text-emerald-700">Paket saat ini sudah terpasang. Kosongkan field jika tidak ingin menggantinya.</p>
    @endif
</div>
<div><label class="text-sm font-medium">Deskripsi</label><textarea name="description" class="mt-1 w-full rounded-md border-slate-300">{{ old('description',$item->description) }}</textarea></div>
@endif
@if($route === 'flyers')
@include('admin.partials.rich-editor', ['name' => 'description', 'label' => 'Deskripsi / Caption', 'value' => $item->description])
<div>
    <label class="text-sm font-medium">Link sumber asli <span class="text-slate-400">(opsional)</span></label>
    <input type="url" name="source_url" value="{{ old('source_url',$item->source_url) }}" placeholder="https://instagram.com/p/..." class="mt-1 w-full rounded-md border-slate-300">
    @error('source_url')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
<div data-gallery data-max-mb="5" data-max-files="10" data-max-total-mb="38">
    <div class="flex flex-wrap items-baseline justify-between gap-2">
        <label class="text-sm font-medium" for="flyer-images">Galeri gambar</label>
        <span class="text-xs text-slate-400">JPG, PNG, GIF, WebP &middot; maks 5 MB per gambar &middot; maks 10 gambar sekali unggah</span>
    </div>
    <div data-gallery-grid class="mt-2 grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-5">
        @foreach($item->exists ? $item->images : [] as $image)
            <div data-existing="{{ $image->id }}" class="group relative aspect-square overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                <img src="{{ asset('storage/' . $image->image) }}" alt="Gambar flyer" class="h-full w-full object-cover">
                <button type="button" data-delete-existing title="Hapus gambar" aria-label="Hapus gambar" class="absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow ring-1 ring-slate-200 hover:bg-red-600 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        @endforeach
        <button type="button" data-gallery-add class="flex aspect-square flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-slate-300 bg-white text-slate-500 hover:border-teal-400 hover:text-teal-700">
            <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
            <span class="text-xs font-medium">Tambah gambar</span>
        </button>
    </div>
    <input id="flyer-images" type="file" accept="image/jpeg,image/png,image/gif,image/webp" name="images[]" multiple class="hidden">
    <div data-gallery-deleted></div>
    <p data-gallery-note class="mt-2 hidden text-xs text-slate-500"></p>
    <p data-gallery-error class="mt-1 hidden text-xs text-red-600"></p>
    @error('images')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    @error('images.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
@push('scripts')
<script>
document.querySelectorAll('[data-gallery]').forEach((box) => {
    const grid = box.querySelector('[data-gallery-grid]');
    const addTile = box.querySelector('[data-gallery-add]');
    const input = box.querySelector('input[type="file"]');
    const deleted = box.querySelector('[data-gallery-deleted]');
    const note = box.querySelector('[data-gallery-note]');
    const error = box.querySelector('[data-gallery-error]');
    const maxMb = Number(box.dataset.maxMb);
    const maxFiles = Number(box.dataset.maxFiles);
    const maxTotal = Number(box.dataset.maxTotalMb) * 1024 * 1024;
    let files = [];
    let removedCount = 0;

    const showError = (messages) => {
        error.innerHTML = messages.map((m) => `<span class="block">${m}</span>`).join('');
        error.classList.toggle('hidden', messages.length === 0);
    };

    const syncInput = () => {
        const transfer = new DataTransfer();
        files.forEach(({ file }) => transfer.items.add(file));
        input.files = transfer.files;

        const parts = [];
        if (files.length) parts.push(`${files.length} gambar baru akan diunggah`);
        if (removedCount) parts.push(`${removedCount} gambar akan dihapus`);
        note.textContent = parts.length ? `${parts.join(' · ')} setelah klik Simpan.` : '';
        note.classList.toggle('hidden', parts.length === 0);
    };

    const removeButton = () => {
        const button = document.createElement('button');
        button.type = 'button';
        button.title = 'Hapus gambar';
        button.setAttribute('aria-label', 'Hapus gambar');
        button.className = 'absolute right-1.5 top-1.5 flex h-7 w-7 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow ring-1 ring-slate-200 hover:bg-red-600 hover:text-white';
        button.innerHTML = '<svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>';
        return button;
    };

    const addFile = (file) => {
        const url = URL.createObjectURL(file);
        const tile = document.createElement('div');
        tile.className = 'relative aspect-square overflow-hidden rounded-lg border-2 border-teal-400 bg-slate-50';
        tile.innerHTML = `<img src="${url}" alt="" class="h-full w-full object-cover"><span class="absolute bottom-1.5 left-1.5 rounded bg-teal-500 px-1.5 py-0.5 text-[10px] font-semibold text-navy-950">Baru</span>`;
        tile.querySelector('img').alt = file.name;
        tile.title = file.name;
        const entry = { file, url, tile };
        const button = removeButton();
        button.addEventListener('click', () => {
            URL.revokeObjectURL(url);
            tile.remove();
            files = files.filter((item) => item !== entry);
            syncInput();
        });
        tile.appendChild(button);
        grid.insertBefore(tile, addTile);
        files.push(entry);
    };

    addTile.addEventListener('click', () => input.click());

    input.addEventListener('change', () => {
        const picked = Array.from(input.files).filter((file) => !files.some(({ file: f }) => f === file));
        const errors = [];
        let total = files.reduce((sum, { file }) => sum + file.size, 0);

        picked.forEach((file) => {
            if (!/^image\/(jpeg|png|gif|webp)$/.test(file.type)) errors.push(`${file.name}: format tidak didukung.`);
            else if (file.size > maxMb * 1024 * 1024) errors.push(`${file.name}: ${(file.size / 1048576).toFixed(1)} MB, melebihi batas ${maxMb} MB.`);
            else if (files.length >= maxFiles) errors.push(`${file.name}: maksimal ${maxFiles} gambar sekali unggah.`);
            else if (total + file.size > maxTotal) errors.push(`${file.name}: total ukuran unggahan melebihi ${box.dataset.maxTotalMb} MB. Simpan dulu, lalu tambah lagi.`);
            else { total += file.size; addFile(file); }
        });

        showError(errors);
        syncInput();
    });

    grid.querySelectorAll('[data-delete-existing]').forEach((button) => {
        button.addEventListener('click', () => {
            const tile = button.closest('[data-existing]');
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'delete_images[]';
            hidden.value = tile.dataset.existing;
            deleted.appendChild(hidden);
            tile.remove();
            removedCount++;
            syncInput();
        });
    });
});
</script>
@endpush
@endif
@if($route === 'webinars')
<div class="grid gap-4 sm:grid-cols-2">
    <div>
        <label class="text-sm font-medium">Narasumber</label>
        <input name="speaker" value="{{ old('speaker',$item->speaker) }}" class="mt-1 w-full rounded-md {{ $errors->has('speaker') ? 'border-red-500' : 'border-slate-300' }}">
        @error('speaker')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">Tanggal & waktu</label>
        <input type="datetime-local" name="webinar_date" value="{{ old('webinar_date', optional($item->webinar_date)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md {{ $errors->has('webinar_date') ? 'border-red-500' : 'border-slate-300' }}" required>
        @error('webinar_date')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label class="text-sm font-medium">Platform</label>
        <input name="platform" value="{{ old('platform',$item->platform) }}" class="mt-1 w-full rounded-md {{ $errors->has('platform') ? 'border-red-500' : 'border-slate-300' }}">
        @error('platform')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
@include('admin.partials.rich-editor', ['name' => 'description', 'label' => 'Deskripsi', 'value' => $item->description])
@endif
@if($upload)
@php($uploadMaxMb = ['webinars' => 4][$route] ?? 2)
@php($currentUpload = $item->{$upload})
@php($currentUploadUrl = $currentUpload ? (\Illuminate\Support\Str::startsWith($currentUpload, ['http://', 'https://']) ? $currentUpload : asset('storage/' . $currentUpload)) : null)
@php($canRemoveUpload = $route !== 'videos')
<div data-image-upload data-max-mb="{{ $uploadMaxMb }}" data-original="{{ $currentUploadUrl }}" data-original-name="Sudah tersimpan" data-can-remove="{{ $canRemoveUpload ? '1' : '0' }}">
    <label class="text-sm font-medium" for="{{ $upload }}">{{ $uploadLabel }}</label>
    <div data-card class="mt-2 hidden items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3">
        <img data-preview src="" alt="Pratinjau {{ strtolower($uploadLabel) }}" class="h-20 w-32 shrink-0 rounded-md border border-slate-200 bg-white object-cover">
        <div class="min-w-0 flex-1">
            <p data-status class="text-xs font-semibold text-slate-400"></p>
            <p data-name class="truncate text-sm font-medium text-navy-900"></p>
            <p data-meta class="text-xs text-slate-500"></p>
        </div>
        <div class="flex shrink-0 flex-wrap justify-end gap-2">
            <button type="button" data-pick class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-700 hover:border-teal-400 hover:text-teal-700">Ganti</button>
            <button type="button" data-remove class="inline-flex items-center gap-1 rounded-md border border-red-200 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 7h12M9 7V5a1 1 0 011-1h4a1 1 0 011 1v2m2 0v12a2 2 0 01-2 2H8a2 2 0 01-2-2V7"/></svg>
                <span data-remove-label>Hapus</span>
            </button>
        </div>
    </div>
    <button type="button" data-empty class="mt-2 hidden w-full flex-col items-center justify-center gap-1 rounded-lg border-2 border-dashed border-slate-300 bg-white px-4 py-6 text-sm text-slate-500 hover:border-teal-400 hover:text-teal-700">
        <svg class="h-7 w-7" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.6-4.6a2 2 0 012.8 0L16 16m-2-2l1.6-1.6a2 2 0 012.8 0L20 14M14 8h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <span class="font-medium">Klik untuk memilih gambar</span>
        <span class="text-xs text-slate-400">JPG, PNG, GIF, WebP &middot; maks {{ $uploadMaxMb }} MB</span>
    </button>
    <p data-removed-note class="mt-2 hidden text-sm text-slate-600">Gambar dihapus. Perubahan berlaku setelah klik <strong>Simpan</strong>. <button type="button" data-undo class="font-medium text-teal-700 underline">Batalkan</button></p>
    <input id="{{ $upload }}" type="file" accept="image/jpeg,image/png,image/gif,image/webp" name="{{ $upload }}" class="hidden">
    @if($canRemoveUpload)<input type="hidden" name="remove_{{ $upload }}" value="0" data-remove-input>@endif
    <p data-size-error class="mt-1 hidden text-xs text-red-600"></p>
    @error($upload)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
</div>
@push('scripts')
<script>
document.querySelectorAll('[data-image-upload]').forEach((box) => {
    const $ = (selector) => box.querySelector(selector);
    const input = $('input[type="file"]');
    const card = $('[data-card]');
    const empty = $('[data-empty]');
    const removedNote = $('[data-removed-note]');
    const removeInput = $('[data-remove-input]');
    const error = $('[data-size-error]');
    const maxMb = Number(box.dataset.maxMb);
    const original = box.dataset.original;
    const canRemove = box.dataset.canRemove === '1';
    let objectUrl = null;
    let removed = false;

    const formatSize = (bytes) => bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
    const toggle = (el, show, display = 'flex') => { el.classList.toggle('hidden', !show); el.classList.toggle(display, show); };

    const render = () => {
        const file = input.files[0];
        const showCard = Boolean(file || (original && !removed));
        toggle(card, showCard);
        toggle(empty, !showCard);
        toggle(removedNote, removed && !file, 'block');
        if (removeInput) removeInput.value = removed && !file ? '1' : '0';
        if (!showCard) return;

        $('[data-preview]').src = file ? objectUrl : original;
        $('[data-status]').textContent = file ? 'Gambar baru · belum disimpan' : 'Gambar saat ini';
        $('[data-status]').classList.toggle('text-teal-600', Boolean(file));
        $('[data-name]').textContent = file ? file.name : box.dataset.originalName;
        $('[data-meta]').textContent = file ? formatSize(file.size) : `Maks ${maxMb} MB · JPG, PNG, GIF, WebP`;
        $('[data-remove-label]').textContent = file ? 'Batal' : 'Hapus';
        toggle($('[data-remove]'), Boolean(file) || canRemove, 'inline-flex');
    };

    const clearFile = () => {
        input.value = '';
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;
    };

    $('[data-pick]').addEventListener('click', () => input.click());
    empty.addEventListener('click', () => input.click());
    $('[data-undo]').addEventListener('click', () => { removed = false; render(); });

    $('[data-remove]').addEventListener('click', () => {
        if (input.files.length) clearFile();
        else removed = true;
        render();
    });

    input.addEventListener('change', () => {
        error.classList.add('hidden');
        const file = input.files[0];
        if (objectUrl) URL.revokeObjectURL(objectUrl);
        objectUrl = null;

        if (file && file.size > maxMb * 1024 * 1024) {
            clearFile();
            error.textContent = `Ukuran file ${formatSize(file.size)} melebihi batas ${maxMb} MB. Pilih gambar yang lebih kecil.`;
            error.classList.remove('hidden');
        } else if (file) {
            objectUrl = URL.createObjectURL(file);
            removed = false;
        }
        render();
    });

    render();
});
</script>
@endpush
@endif
@if($publishedAt)<div><label class="text-sm font-medium">Tanggal publikasi</label><input type="datetime-local" name="published_at" value="{{ old('published_at', optional($item->published_at)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md border-slate-300"></div>@endif
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$item->exists ? $item->is_published : true))> Publikasikan</label><div class="flex gap-3"><button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Simpan</button><a href="{{ route('admin.'.$route.'.index') }}" class="px-4 py-2 text-sm text-slate-600">Batal</a></div></form>
@if($route === 'videos')
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('video-type');
    const normalFields = document.getElementById('normal-video-fields');
    const interactiveFields = document.getElementById('interactive-video-fields');
    const syncFields = () => {
        const interactive = type.value === 'interactive';
        normalFields.hidden = interactive;
        interactiveFields.hidden = !interactive;
        normalFields.querySelectorAll('input').forEach(input => input.disabled = interactive);
        interactiveFields.querySelectorAll('input').forEach(input => input.disabled = !interactive);
    };
    type.addEventListener('change', syncFields);
    syncFields();
});
</script>
@endpush
@endif
@endsection
