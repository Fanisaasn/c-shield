@extends('admin.layouts.app')
@section('title', 'Video Materi Assessment')
@section('content')
<div class="rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-heading font-bold">Tambah video materi</h2>
    <p class="mt-1 text-sm text-slate-500">
        Video ini ditampilkan kepada pengunjung antara Pre-Assessment dan Post-Assessment. Pilih tema agar video
        hanya tampil untuk tema tersebut, atau biarkan "Umum" agar video ini menjadi cadangan untuk tema yang
        belum punya video sendiri. Dalam satu tema (atau Umum), hanya satu video yang bisa aktif &mdash; tandai
        salah satu sebagai "Aktifkan" di bawah.
    </p>
    <form class="mt-4 space-y-3" method="POST" action="{{ route('admin.assessment-videos.store') }}" enctype="multipart/form-data">
        @csrf
        <div>
            <label class="block text-sm font-medium text-navy-900">Tema Self-Assessment</label>
            <select name="assessment_category_id" class="mt-1.5 w-full rounded-md">
                <option value="">Umum (semua tema)</option>
                @foreach ($categories as $option)
                    <option value="{{ $option->id }}" {{ old('assessment_category_id') == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                @endforeach
            </select>
            @error('assessment_category_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-navy-900">Judul</label>
            <input type="text" name="title" value="{{ old('title') }}" required class="mt-1.5 w-full rounded-md">
            @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-navy-900">Deskripsi</label>
            <textarea name="description" class="mt-1.5 w-full rounded-md">{{ old('description') }}</textarea>
        </div>
        <div>
            <label class="block text-sm font-medium text-navy-900">Link video (YouTube, dll.)</label>
            <input type="url" name="video_url" value="{{ old('video_url') }}" placeholder="https://..." class="mt-1.5 w-full rounded-md">
            @error('video_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-navy-900">Atau unggah file video (MP4/WebM/MOV, maks. 100 MB)</label>
            <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" class="mt-1.5 w-full rounded-md">
            @error('video') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
        </div>
        <button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Tambah</button>
    </form>
</div>

<div class="mt-6 space-y-4">
    @forelse ($videos as $video)
        <div class="rounded-xl border {{ $video->is_active ? 'border-teal-400 bg-teal-50/40' : 'border-slate-200 bg-white' }} p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="font-heading font-bold text-navy-900">{{ $video->title }}</h3>
                        @if ($video->is_active)
                            <span class="rounded-full bg-teal-500 px-2.5 py-0.5 text-xs font-semibold text-navy-950">Aktif</span>
                        @endif
                    </div>
                    <p class="mt-1 text-xs font-medium uppercase tracking-wide text-blue-600">
                        {{ $video->category->name ?? 'Umum (semua tema)' }}
                    </p>
                    @if ($video->description)
                        <p class="mt-1 text-sm text-slate-500">{{ $video->description }}</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-400">
                        {{ $video->video_path ? 'File: '.basename($video->video_path) : ($video->video_url ?: 'Belum ada sumber video') }}
                    </p>
                </div>
                <div class="flex shrink-0 flex-col items-end gap-2">
                    @unless ($video->is_active)
                        <form method="POST" action="{{ route('admin.assessment-videos.activate', $video) }}">
                            @csrf
                            <button class="rounded bg-slate-800 px-3 py-1.5 text-xs font-semibold text-white">Aktifkan</button>
                        </form>
                    @endunless
                    <form method="POST" action="{{ route('admin.assessment-videos.destroy', $video) }}">
                        @csrf
                        @method('DELETE')
                        <button onclick="return confirm('Hapus video materi ini?')" class="text-xs text-red-600">Hapus</button>
                    </form>
                </div>
            </div>

            <details class="mt-3 text-xs">
                <summary class="cursor-pointer text-teal-700">Edit</summary>
                <form method="POST" action="{{ route('admin.assessment-videos.update', $video) }}" enctype="multipart/form-data" class="mt-2 space-y-2 rounded border border-slate-200 p-3">
                    @csrf
                    @method('PUT')
                    <select name="assessment_category_id" class="w-full rounded border-slate-300">
                        <option value="">Umum (semua tema)</option>
                        @foreach ($categories as $option)
                            <option value="{{ $option->id }}" {{ $video->assessment_category_id == $option->id ? 'selected' : '' }}>{{ $option->name }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="title" value="{{ $video->title }}" required class="w-full rounded border-slate-300">
                    <textarea name="description" class="w-full rounded border-slate-300">{{ $video->description }}</textarea>
                    <input type="url" name="video_url" value="{{ $video->video_url }}" placeholder="https://..." class="w-full rounded border-slate-300">
                    <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime" class="w-full rounded border-slate-300">
                    <button class="rounded bg-slate-800 px-3 py-1.5 text-white">Simpan</button>
                </form>
            </details>
        </div>
    @empty
        <p class="text-center text-sm text-slate-400">Belum ada video materi. Tambahkan salah satu di atas.</p>
    @endforelse
</div>
@endsection
