@extends('admin.layouts.app')
@section('title', 'Self-Assessment')
@section('content')
<section class="rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-heading font-bold">Tambah pertanyaan</h2>
    <form class="mt-4 space-y-3" method="POST" action="{{ route('admin.assessments.questions.store') }}">
        @csrf
        <textarea name="question" placeholder="Pertanyaan" class="w-full rounded-md border-slate-300" required></textarea>
        <div class="grid gap-2 sm:grid-cols-2">
            @for($i=0;$i<4;$i++)
                <label class="flex items-center gap-2"><input type="radio" name="correct_option" value="{{ $i }}" @checked($i===0)><input name="options[]" placeholder="Pilihan {{ chr(65+$i) }}" class="w-full rounded-md border-slate-300" required></label>
            @endfor
        </div>
        <button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold">Simpan pertanyaan</button>
    </form>
</section>
<div class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-heading font-bold">Daftar pertanyaan</h2>
    <ol class="mt-4 list-decimal space-y-3 pl-5">
        @forelse($questions as $question)
            <li class="text-sm text-navy-900">
                {{ $question->question }}
                <div class="mt-1 text-xs text-slate-500">@foreach($question->options->sortBy('order') as $option)<span class="mr-3 {{ $option->is_correct ? 'font-semibold text-emerald-600' : '' }}">{{ $option->option_text }}</span>@endforeach</div>
                <div class="mt-1 flex gap-3">
                    <form method="POST" action="{{ route('admin.assessments.questions.destroy',$question) }}">@csrf @method('DELETE')<button onclick="return confirm('Hapus pertanyaan?')" class="text-xs text-red-600">Hapus</button></form>
                    <details class="text-xs">
                        <summary class="cursor-pointer text-teal-700">Edit</summary>
                        <form method="POST" action="{{ route('admin.assessments.questions.update',$question) }}" class="mt-2 space-y-2 rounded border p-3">@csrf @method('PUT')<textarea name="question" class="block w-full rounded border-slate-300" required>{{ $question->question }}</textarea>@foreach($question->options->sortBy('order')->values() as $i => $option)<label class="block"><input type="radio" name="correct_option" value="{{ $i }}" @checked($option->is_correct)><input name="options[]" value="{{ $option->option_text }}" class="rounded border-slate-300" required></label>@endforeach<button class="rounded bg-slate-800 px-3 py-1.5 text-white">Simpan</button></form>
                    </details>
                </div>
            </li>
        @empty
            <li class="text-slate-400">Belum ada pertanyaan.</li>
        @endforelse
    </ol>
</div>
@endsection
