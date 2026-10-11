{{--
    Multiple-choice question form, shared by "Tambah soal" and each "Edit".
    Params: $formId, $action, $question (null when creating), $submitLabel.
--}}
@php
    use App\Http\Controllers\Admin\AssessmentController;

    $isOld = old('form_id') === $formId;

    if ($isOld) {
        $rows = collect(old('options', []))->map(fn ($option, $key) => [
            'key' => (string) $key,
            'id' => $option['id'] ?? null,
            'text' => $option['text'] ?? '',
        ])->values();
        $correctKeys = array_map('strval', (array) old('correct_options', []));
    } elseif ($question) {
        $rows = $question->options->values()->map(fn ($option, $index) => [
            'key' => (string) $index,
            'id' => $option->id,
            'text' => $option->option_text,
        ]);
        $correctKeys = $question->options->values()
            ->filter(fn ($option) => $option->is_correct)
            ->keys()
            ->map(fn ($index) => (string) $index)
            ->all();
    } else {
        $rows = collect(range(0, 3))->map(fn ($index) => ['key' => (string) $index, 'id' => null, 'text' => '']);
        $correctKeys = [];
    }
@endphp

<form method="POST" action="{{ $action }}" class="mc-form space-y-4"
      data-min="{{ AssessmentController::MIN_OPTIONS }}" data-max="{{ AssessmentController::MAX_OPTIONS }}">
    @csrf
    @if ($question) @method('PUT') @endif
    <input type="hidden" name="form_id" value="{{ $formId }}">

    @if ($isOld && $errors->any())
        <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            <ul class="list-disc space-y-0.5 pl-4">
                @foreach (array_unique($errors->all()) as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label class="text-sm font-medium text-navy-900" for="{{ $formId }}-question">Pertanyaan</label>
        <textarea id="{{ $formId }}-question" name="question" rows="3" required
                  class="mt-1 w-full rounded-md border-slate-300 text-sm"
                  placeholder="Contoh: Apa yang dimaksud dengan phishing?">{{ $isOld ? old('question') : $question?->question }}</textarea>
    </div>

    <div>
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-navy-900">Pilihan jawaban</span>
            <span class="text-xs text-slate-400">Centang semua jawaban yang benar (boleh lebih dari satu)</span>
        </div>

        <div class="mc-options mt-2 space-y-2">
            @foreach ($rows as $row)
                <div class="mc-option flex items-center gap-2" data-key="{{ $row['key'] }}">
                    <input type="checkbox" name="correct_options[]" value="{{ $row['key'] }}" @checked(in_array($row['key'], $correctKeys, true))
                           class="mc-correct h-4 w-4 shrink-0 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500" title="Tandai sebagai jawaban benar">
                    <span class="mc-letter w-5 shrink-0 text-center text-sm font-semibold text-slate-500"></span>
                    @if ($row['id'])
                        <input type="hidden" name="options[{{ $row['key'] }}][id]" value="{{ $row['id'] }}">
                    @endif
                    <input type="text" name="options[{{ $row['key'] }}][text]" value="{{ $row['text'] }}" required maxlength="255"
                           class="w-full rounded-md border-slate-300 text-sm" placeholder="Tulis pilihan jawaban">
                    <button type="button" class="mc-remove shrink-0 rounded-md px-2 py-1 text-xs text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:text-slate-300 disabled:hover:bg-transparent"
                            title="Hapus pilihan">Hapus</button>
                </div>
            @endforeach
        </div>

        <button type="button" class="mc-add mt-2 text-xs font-semibold text-teal-700 hover:text-teal-800 disabled:cursor-not-allowed disabled:text-slate-300">
            + Tambah pilihan
        </button>
        <p class="mc-correct-error mt-2 hidden text-xs font-medium text-red-600">Centang minimal satu jawaban yang benar.</p>
    </div>

    <button class="rounded-md bg-teal-500 px-4 py-2 text-sm font-semibold text-navy-950 hover:bg-teal-400">{{ $submitLabel }}</button>
</form>
