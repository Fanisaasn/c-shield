@php
    // Old entries were saved as plain text; convert their line breaks so Trix keeps them.
    $editorValue = (string) old($name, $value);
    if ($editorValue !== '' && ! preg_match('/<\/?[a-z][^>]*>/i', $editorValue)) {
        $editorValue = nl2br(e($editorValue), false);
    }
@endphp
<div>
    <label class="text-sm font-medium" for="{{ $name }}-editor">{{ $label }}</label>
    <input id="{{ $name }}-input" type="hidden" name="{{ $name }}" value="{{ $editorValue }}">
    <trix-editor id="{{ $name }}-editor" input="{{ $name }}-input" data-upload-url="{{ route('admin.editor-uploads.store') }}"
                 class="trix-content rich-text mt-1 block rounded-md border border-slate-400 bg-white px-3 py-2 text-sm focus-within:border-[#0f8fa0]"
                 style="min-height: {{ $minHeight ?? '8rem' }}"></trix-editor>
    <p class="mt-1 text-xs text-slate-400">Blok teks lalu klik ikon link untuk menautkan. Ctrl + klik link untuk membukanya. Klik ikon klip untuk melampirkan file.</p>
    @error($name)
        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
    @enderror
</div>
