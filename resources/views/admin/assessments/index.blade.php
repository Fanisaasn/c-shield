@extends('admin.layouts.app')
@section('title', 'Self-Assessment')
@section('content')

<section class="rounded-xl border border-slate-200 bg-white p-5">
    <h2 class="font-heading font-bold">Tambah soal pilihan ganda</h2>
    <p class="mt-1 text-sm text-slate-500">
        Soal dipakai untuk Pre-Assessment dan Post-Assessment. Setiap soal memiliki
        {{ \App\Http\Controllers\Admin\AssessmentController::MIN_OPTIONS }}&ndash;{{ \App\Http\Controllers\Admin\AssessmentController::MAX_OPTIONS }}
        pilihan jawaban, dan boleh memiliki lebih dari satu jawaban benar. Responden harus memilih semua jawaban benar agar soal dihitung benar.
    </p>
    <div class="mt-4">
        @include('admin.assessments._form', [
            'formId' => 'create',
            'action' => route('admin.assessments.questions.store'),
            'question' => null,
            'submitLabel' => 'Simpan soal',
        ])
    </div>
</section>

<section class="mt-6 rounded-xl border border-slate-200 bg-white p-5">
    <div class="flex items-center justify-between">
        <h2 class="font-heading font-bold">Daftar soal</h2>
        <span class="text-xs text-slate-500">{{ $questions->count() }} soal</span>
    </div>

    <ol class="mt-4 space-y-4">
        @forelse ($questions as $question)
            <li class="rounded-lg border border-slate-200 p-4">
                <div class="flex items-start gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-navy-900 text-xs font-bold text-white">{{ $loop->iteration }}</span>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-navy-900">{{ $question->question }}</p>

                        <ul class="mt-2 space-y-1">
                            @foreach ($question->options as $option)
                                <li class="flex items-center gap-2 text-sm {{ $option->is_correct ? 'font-semibold text-emerald-700' : 'text-slate-600' }}">
                                    <span class="w-5 text-center">{{ chr(65 + $loop->index) }}.</span>
                                    <span>{{ $option->option_text }}</span>
                                    @if ($option->is_correct)
                                        <span class="rounded bg-emerald-50 px-1.5 py-0.5 text-[10px] font-semibold uppercase text-emerald-700">Benar</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($question->answers_count > 0)
                            <p class="mt-2 text-xs text-slate-400">Sudah dijawab {{ $question->answers_count }} kali.</p>
                        @endif

                        <div class="mt-3 flex items-start gap-4">
                            <details class="flex-1 text-sm" @if (old('form_id') === 'edit-'.$question->id) open @endif>
                                <summary class="cursor-pointer text-xs font-semibold text-teal-700">Edit</summary>
                                <div class="mt-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                    @include('admin.assessments._form', [
                                        'formId' => 'edit-'.$question->id,
                                        'action' => route('admin.assessments.questions.update', $question),
                                        'question' => $question,
                                        'submitLabel' => 'Simpan perubahan',
                                    ])
                                </div>
                            </details>

                            <form method="POST" action="{{ route('admin.assessments.questions.destroy', $question) }}"
                                  onsubmit="return confirm(@js($question->answers_count > 0
                                      ? 'Soal ini sudah dijawab '.$question->answers_count.' kali. Menghapusnya juga menghapus jawaban tersebut dari riwayat responden. Lanjutkan?'
                                      : 'Hapus soal ini?'))">
                                @csrf
                                @method('DELETE')
                                <button class="text-xs font-semibold text-red-600 hover:text-red-700">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            </li>
        @empty
            <li class="rounded-lg border border-dashed border-slate-300 p-6 text-center text-sm text-slate-400">Belum ada soal.</li>
        @endforelse
    </ol>
</section>

@endsection

@push('scripts')
    <script>
        document.querySelectorAll('.mc-form').forEach(function (form) {
            const list = form.querySelector('.mc-options');
            const addButton = form.querySelector('.mc-add');
            const min = Number(form.dataset.min);
            const max = Number(form.dataset.max);
            let counter = 0;

            function rows() {
                return list.querySelectorAll('.mc-option');
            }

            function refresh() {
                const all = rows();
                all.forEach(function (row, index) {
                    row.querySelector('.mc-letter').textContent = String.fromCharCode(65 + index) + '.';
                    row.querySelector('.mc-remove').disabled = all.length <= min;
                });
                addButton.disabled = all.length >= max;
            }

            addButton.addEventListener('click', function () {
                if (rows().length >= max) return;

                const key = 'new' + Date.now() + (++counter);
                const row = rows()[0].cloneNode(true);
                row.dataset.key = key;
                row.querySelectorAll('input[type="hidden"]').forEach(function (input) { input.remove(); });

                const correct = row.querySelector('.mc-correct');
                correct.value = key;
                correct.checked = false;

                const text = row.querySelector('input[type="text"]');
                text.name = 'options[' + key + '][text]';
                text.value = '';

                list.appendChild(row);
                refresh();
                text.focus();
            });

            const correctError = form.querySelector('.mc-correct-error');

            list.addEventListener('change', function (event) {
                if (event.target.classList.contains('mc-correct') && event.target.checked) {
                    correctError.classList.add('hidden');
                }
            });

            form.addEventListener('submit', function (event) {
                if (!form.querySelector('.mc-correct:checked')) {
                    event.preventDefault();
                    correctError.classList.remove('hidden');
                }
            });

            list.addEventListener('click', function (event) {
                const button = event.target.closest('.mc-remove');
                if (!button || rows().length <= min) return;
                button.closest('.mc-option').remove();
                refresh();
            });

            refresh();
        });
    </script>
@endpush
