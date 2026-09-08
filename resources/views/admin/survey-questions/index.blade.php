@extends('admin.layouts.app')
@section('title', 'Survei Kepuasan')
@section('content')
<div class="rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-heading font-bold">Tambah pertanyaan survei</h2>
    <p class="mt-1 text-sm text-slate-500">Pertanyaan akan tampil di survei kepuasan dengan pilihan jawaban Sangat Tidak Setuju &ndash; Sangat Setuju.</p>
    <form class="mt-4 flex flex-col gap-3 sm:flex-row" method="POST" action="{{ route('admin.survey-questions.store') }}">
        @csrf
        <textarea name="question" placeholder="Tulis pertanyaan survei" class="w-full rounded-md border-slate-300" required></textarea>
        <button class="w-max shrink-0 rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Tambah</button>
    </form>
</div>

<div class="mt-6 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Urutan</th>
                <th class="px-4 py-3">Pertanyaan</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($questions as $question)
                <tr>
                    <td class="px-4 py-3 align-top text-slate-500">{{ $question->sort_order }}</td>
                    <td class="px-4 py-3 align-top text-navy-900">
                        {{ $question->question }}
                        <details class="mt-2 text-xs">
                            <summary class="cursor-pointer text-teal-700">Edit</summary>
                            <form method="POST" action="{{ route('admin.survey-questions.update', $question) }}" class="mt-2 flex flex-col gap-2 rounded border border-slate-200 p-3 sm:flex-row sm:items-start">
                                @csrf
                                @method('PUT')
                                <textarea name="question" class="w-full rounded border-slate-300" required>{{ $question->question }}</textarea>
                                <input type="number" name="sort_order" value="{{ $question->sort_order }}" class="w-20 rounded border-slate-300" required>
                                <button class="w-max shrink-0 rounded bg-slate-800 px-3 py-1.5 text-white">Simpan</button>
                            </form>
                        </details>
                    </td>
                    <td class="px-4 py-3 align-top text-right">
                        <form method="POST" action="{{ route('admin.survey-questions.destroy', $question) }}">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus pertanyaan survei ini?')" class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-4 py-8 text-center text-slate-400">Belum ada pertanyaan survei.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
