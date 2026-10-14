@extends('admin.layouts.app')
@section('title', 'Materi Belajar')
@section('content')
<div class="flex flex-wrap items-start justify-between gap-3">
    <p class="max-w-2xl text-sm text-slate-500">
        Materi wajib dipelajari responden secara berurutan setelah Pre-Assessment.
    </p>
    <a href="{{ route('admin.learning-material.create') }}" class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950">Tambah Materi</a>
</div>

<div class="mt-5 overflow-hidden rounded-xl border border-slate-200 bg-white">
    <table class="min-w-full text-left text-sm">
        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
            <tr>
                <th class="px-4 py-3">Urutan</th>
                <th class="px-4 py-3">Judul</th>
                <th class="px-4 py-3">Jenis</th>
                <th class="px-4 py-3">Diperbarui</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($materials as $material)
                <tr>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <span class="w-6 font-semibold text-navy-900">{{ $loop->iteration }}</span>
                            @foreach (['up' => '↑', 'down' => '↓'] as $direction => $arrow)
                                <form method="POST" action="{{ route('admin.learning-material.move', $material) }}">
                                    @csrf
                                    <input type="hidden" name="direction" value="{{ $direction }}">
                                    <button class="rounded border border-slate-200 px-2 text-slate-500 hover:bg-slate-50 disabled:opacity-30"
                                            title="{{ $direction === 'up' ? 'Naikkan' : 'Turunkan' }}"
                                            @disabled($direction === 'up' ? $loop->first : $loop->last)>{{ $arrow }}</button>
                                </form>
                            @endforeach
                        </div>
                    </td>
                    <td class="px-4 py-3 font-medium text-navy-900">
                        {{ $material->title }}
                        @unless ($material->isReady())
                            <span class="ml-1 text-xs font-normal text-amber-600">(belum ada file, dilewati)</span>
                        @endunless
                    </td>
                    <td class="px-4 py-3">
                        <span class="rounded-full px-2 py-1 text-xs font-semibold {{ $material->isInteractive() ? 'bg-teal-50 text-teal-700' : 'bg-slate-100 text-slate-600' }}">{{ $material->typeLabel() }}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $material->updated_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <a class="mr-3 text-teal-700" href="{{ route('admin.learning-material.edit', $material) }}">Edit</a>
                        <form class="inline" method="POST" action="{{ route('admin.learning-material.destroy', $material) }}">
                            @csrf
                            @method('DELETE')
                            <button onclick="return confirm('Hapus materi ini?')" class="text-red-600">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada materi. Post-Assessment tidak dapat dibuka sampai ada minimal satu materi.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
