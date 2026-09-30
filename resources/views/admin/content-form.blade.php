@extends('admin.layouts.app')
@section('title', ($item->exists ? 'Edit ' : 'Tambah ').$title)
@section('content')
@vite('resources/js/admin-editor.js')
<form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.'.$route.'.update',$item) : route('admin.'.$route.'.store') }}" class="max-w-3xl space-y-5 rounded-xl border border-slate-200 bg-white p-6">@csrf @if($item->exists) @method('PUT') @endif
<div><label class="text-sm font-medium">Judul</label><input name="title" value="{{ old('title',$item->title) }}" class="mt-1 w-full rounded-md border-slate-300" required></div>
@if($slug)<div><label class="text-sm font-medium">Slug <span class="text-slate-400">(otomatis bila kosong)</span></label><input name="slug" value="{{ old('slug',$item->slug) }}" class="mt-1 w-full rounded-md border-slate-300"></div>@endif
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
@include('admin.partials.rich-editor', ['name' => 'description', 'label' => 'Deskripsi', 'value' => $item->description])
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
<div>
    <label class="text-sm font-medium">Galeri gambar</label>
    <input type="file" accept="image/*" name="images[]" multiple class="mt-1 block w-full text-sm">
    @error('images.*')
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
    @if($item->exists && $item->images->isNotEmpty())
        <div class="mt-3 grid grid-cols-3 gap-3 sm:grid-cols-4">
            @foreach($item->images as $image)
                <label class="group relative block cursor-pointer overflow-hidden rounded-md border border-slate-200">
                    <img src="{{ asset('storage/' . $image->image) }}" class="aspect-square w-full object-cover">
                    <span class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-1 bg-black/60 py-1 text-xs text-white">
                        <input type="checkbox" name="delete_images[]" value="{{ $image->id }}"> Hapus
                    </span>
                </label>
            @endforeach
        </div>
    @endif
</div>
@endif
@if($route === 'webinars')<div class="grid gap-4 sm:grid-cols-2"><div><label class="text-sm font-medium">Narasumber</label><input name="speaker" value="{{ old('speaker',$item->speaker) }}" class="mt-1 w-full rounded-md border-slate-300"></div><div><label class="text-sm font-medium">Tanggal & waktu</label><input type="datetime-local" name="webinar_date" value="{{ old('webinar_date', optional($item->webinar_date)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md border-slate-300" required></div><div><label class="text-sm font-medium">Platform</label><input name="platform" value="{{ old('platform',$item->platform) }}" class="mt-1 w-full rounded-md border-slate-300"></div><div><label class="text-sm font-medium">Link pendaftaran</label><input type="url" name="registration_url" value="{{ old('registration_url',$item->registration_url) }}" class="mt-1 w-full rounded-md border-slate-300" required></div></div>@include('admin.partials.rich-editor', ['name' => 'description', 'label' => 'Deskripsi', 'value' => $item->description])
@endif
@if($upload)<div><label class="text-sm font-medium">{{ $uploadLabel }}</label><input type="file" accept="image/*" name="{{ $upload }}" class="mt-1 block w-full text-sm">@if($item->{$upload})<p class="mt-1 text-xs text-slate-400">File saat ini tersimpan.</p>@endif</div>@endif
@if($publishedAt)<div><label class="text-sm font-medium">Tanggal publikasi</label><input type="datetime-local" name="published_at" value="{{ old('published_at', optional($item->published_at)->format('Y-m-d\\TH:i')) }}" class="mt-1 w-full rounded-md border-slate-300"></div>@endif
<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$item->exists ? $item->is_published : true))> Publikasikan</label><div class="flex gap-3"><button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Simpan</button><a href="{{ route('admin.'.$route.'.index') }}" class="px-4 py-2 text-sm text-slate-600">Batal</a></div></form>
@endsection
