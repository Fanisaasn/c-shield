@extends('admin.layouts.app')
@section('title', 'Materi Belajar')
@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.learning-material.update') }}" class="max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">
    @csrf
    @method('PUT')

    <div>
        <h2 class="font-heading font-bold">Materi Social Engineering</h2>
        <p class="mt-1 text-xs text-slate-500">
            Materi ini wajib dipelajari responden setelah Pre-Assessment. Post-Assessment baru dapat dibuka setelah
            responden membuka materi ini dan mencentang konfirmasi sudah mempelajarinya.
        </p>
    </div>

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
        <label class="text-sm font-medium" for="file">
            File materi
            @if ($material->file_path)<span class="text-slate-400">(kosongkan bila tidak ingin mengganti)</span>@endif
        </label>
        <input id="file" type="file" name="file" accept=".pdf,.ppt,.pptx,.mp4,.webm" class="mt-1 block w-full text-sm" @required(! $material->file_path)>
        <p class="mt-1 text-xs text-slate-400">
            Format: PDF, PPT/PPTX, atau video MP4/WebM. Maksimal 100 MB.
            PDF dan video tampil langsung di halaman. PPT/PPTX hanya tampil langsung bila situs sudah online (memakai
            penampil Microsoft Office); di localhost responden perlu mengunduhnya, jadi disarankan mengekspor PPT ke PDF.
        </p>
        @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        @if ($material->file_path)
            <p class="mt-2 text-xs text-slate-500">
                File saat ini ({{ strtoupper($material->file_type) }}):
                <a href="{{ $material->fileUrl() }}" target="_blank" rel="noopener" class="text-teal-700 underline">{{ $material->original_name ?: basename($material->file_path) }}</a>
            </p>
        @endif
    </div>

    <button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold">Simpan materi</button>
</form>
@endsection
