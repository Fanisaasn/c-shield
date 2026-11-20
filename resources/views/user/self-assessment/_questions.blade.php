{{-- Question list shared by the Pre- and Post-Assessment quiz pages. --}}
<div class="space-y-4">
    @foreach ($questions as $question)
        @php
            $correctCount = $question->options->where('is_correct', true)->count();
            $isMultiple = $correctCount > 1;
            $oldAnswer = collect((array) old('answers.' . $question->id))->map(fn ($id) => (int) $id);
        @endphp
        <fieldset class="quiz-question scroll-mt-28 rounded-xl border border-slate-200 bg-white p-5 transition-colors"
                  data-number="{{ $loop->iteration }}"
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
                                   @checked($oldAnswer->contains($option->id))
                                   class="mt-0.5 border-slate-300 text-blue-600 focus:ring-blue-600">
                        @endif
                        <span>{{ $option->option_text }}</span>
                    </label>
                @endforeach
            </div>
            <p class="quiz-error mt-3 hidden items-center gap-1.5 text-xs font-medium text-red-600">
                <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
                Soal ini belum dijawab.
            </p>
        </fieldset>
    @endforeach
</div>

<div class="quiz-summary mt-6 hidden items-start gap-2 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
    <span class="quiz-summary-text"></span>
</div>

@once
    @push('scripts')
        <script>
            function quizAnswered(fieldset) {
                return Boolean(fieldset.querySelector('input:checked'));
            }

            function markQuestion(fieldset, invalid) {
                const error = fieldset.querySelector('.quiz-error');
                error.classList.toggle('hidden', !invalid);
                error.classList.toggle('flex', invalid);
                fieldset.classList.toggle('border-red-400', invalid);
                fieldset.classList.toggle('bg-red-50/40', invalid);
                fieldset.classList.toggle('border-slate-200', !invalid);
            }

            function refreshSummary(form) {
                const summary = form.querySelector('.quiz-summary');
                if (!summary || summary.classList.contains('hidden')) return;
                const missing = Array.from(form.querySelectorAll('.quiz-question')).filter(function (f) { return !quizAnswered(f); });
                if (missing.length === 0) {
                    summary.classList.replace('flex', 'hidden');
                    return;
                }
                summary.querySelector('.quiz-summary-text').textContent =
                    'Masih ada ' + missing.length + ' soal yang belum dijawab: nomor ' +
                    missing.map(function (f) { return f.dataset.number; }).join(', ') + '. Jawab semua soal untuk mengirim.';
            }

            document.querySelectorAll('.quiz-question').forEach(function (fieldset) {
                const limit = Number(fieldset.dataset.limit || 0);
                const boxes = fieldset.querySelectorAll('input[type="checkbox"]');

                function refresh() {
                    if (limit) {
                        const checked = fieldset.querySelectorAll('input[type="checkbox"]:checked').length;
                        boxes.forEach(function (box) {
                            box.disabled = !box.checked && checked >= limit;
                        });
                    }
                    if (quizAnswered(fieldset)) markQuestion(fieldset, false);
                    refreshSummary(fieldset.closest('form'));
                }

                fieldset.querySelectorAll('input').forEach(function (input) { input.addEventListener('change', refresh); });
                if (limit) refresh();
            });

            // Mark every unanswered question (radio and checkbox) instead of the browser's single tooltip.
            function validateQuiz(form) {
                const missing = [];

                form.querySelectorAll('.quiz-question').forEach(function (fieldset) {
                    const invalid = !quizAnswered(fieldset);
                    markQuestion(fieldset, invalid);
                    if (invalid) missing.push(fieldset);
                });

                if (missing.length) {
                    const summary = form.querySelector('.quiz-summary');
                    summary.classList.replace('hidden', 'flex');
                    refreshSummary(form);
                    missing[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
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
