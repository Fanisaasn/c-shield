{{-- Question list shared by the Pre- and Post-Assessment quiz pages. --}}
<div class="space-y-4">
    @foreach ($questions as $question)
        @php
            $correctCount = $question->options->where('is_correct', true)->count();
            $isMultiple = $correctCount > 1;
            $oldAnswer = collect((array) old('answers.' . $question->id))->map(fn ($id) => (int) $id);
        @endphp
        <fieldset class="quiz-question rounded-xl border border-slate-200 bg-white p-5"
                  @if ($isMultiple) data-limit="{{ $correctCount }}" @endif>
            <legend class="sr-only">Pertanyaan {{ $loop->iteration }}</legend>
            <p class="text-sm font-medium text-navy-900">
                {{ $loop->iteration }}. {{ $question->question }}
            </p>
            <p class="mt-1 text-xs font-medium {{ $isMultiple ? 'text-blue-600' : 'text-slate-400' }}">
                {{ $isMultiple ? "Pilih {$correctCount} jawaban yang benar" : 'Pilih 1 jawaban yang benar' }}
            </p>
            <div class="mt-3 space-y-2">
                @foreach ($question->options as $option)
                    <label class="flex items-start gap-2.5 rounded-md border border-slate-200 px-3 py-2.5 text-sm text-slate-700 transition hover:bg-slate-50 has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50 has-[:disabled]:cursor-not-allowed has-[:disabled]:opacity-60">
                        @if ($isMultiple)
                            <input type="checkbox" name="answers[{{ $question->id }}][]" value="{{ $option->id }}"
                                   @checked($oldAnswer->contains($option->id))
                                   class="mt-0.5 rounded border-slate-300 text-blue-600 focus:ring-blue-600">
                        @else
                            <input type="radio" name="answers[{{ $question->id }}]" value="{{ $option->id }}"
                                   @checked($oldAnswer->contains($option->id)) required
                                   class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-600">
                        @endif
                        <span>{{ $option->option_text }}</span>
                    </label>
                @endforeach
            </div>
            @if ($isMultiple)
                <p class="quiz-error mt-2 hidden text-xs font-medium text-red-600">Pilih minimal satu jawaban.</p>
            @endif
        </fieldset>
    @endforeach
</div>

@once
    @push('scripts')
        <script>
            document.querySelectorAll('.quiz-question[data-limit]').forEach(function (fieldset) {
                const limit = Number(fieldset.dataset.limit);
                const boxes = fieldset.querySelectorAll('input[type="checkbox"]');

                function refresh() {
                    const checked = fieldset.querySelectorAll('input[type="checkbox"]:checked').length;
                    boxes.forEach(function (box) {
                        box.disabled = !box.checked && checked >= limit;
                    });
                    if (checked > 0) {
                        fieldset.querySelector('.quiz-error').classList.add('hidden');
                        fieldset.classList.remove('border-red-300');
                    }
                }

                boxes.forEach(function (box) { box.addEventListener('change', refresh); });
                refresh();
            });

            // Checkbox groups have no native "required"; block submit until each has a pick.
            function validateQuiz(form) {
                let firstInvalid = null;

                form.querySelectorAll('.quiz-question[data-limit]').forEach(function (fieldset) {
                    if (!fieldset.querySelector('input[type="checkbox"]:checked')) {
                        fieldset.querySelector('.quiz-error').classList.remove('hidden');
                        fieldset.classList.add('border-red-300');
                        firstInvalid = firstInvalid || fieldset;
                    }
                });

                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    return false;
                }

                const button = form.querySelector('button[type=submit]');
                button.disabled = true;
                button.textContent = 'Memproses...';
                return true;
            }
        </script>
    @endpush
@endonce
