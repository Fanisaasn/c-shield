<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssessmentController extends Controller
{
    public const MIN_OPTIONS = 2;

    public const MAX_OPTIONS = 5;

    public function index()
    {
        $questions = AssessmentQuestion::query()
            ->with(['options' => fn ($query) => $query->orderBy('order')])
            ->withCount('answers')
            ->orderBy('order')
            ->get();

        return view('admin.assessments.index', compact('questions'));
    }

    public function storeQuestion(Request $request): RedirectResponse
    {
        [$text, $options, $correctKeys] = $this->validatedQuestion($request);

        DB::transaction(function () use ($text, $options, $correctKeys) {
            $question = AssessmentQuestion::create([
                'question' => $text,
                'order' => ((int) AssessmentQuestion::max('order')) + 1,
            ]);

            $this->syncOptions($question, $options, $correctKeys);
        });

        return redirect()->route('admin.assessments.index')->with('success', 'Soal berhasil ditambahkan.');
    }

    public function updateQuestion(Request $request, AssessmentQuestion $question): RedirectResponse
    {
        [$text, $options, $correctKeys] = $this->validatedQuestion($request, $question);

        DB::transaction(function () use ($question, $text, $options, $correctKeys) {
            $question->update(['question' => $text]);
            $this->syncOptions($question, $options, $correctKeys);
        });

        return redirect()->route('admin.assessments.index')->with('success', 'Soal berhasil diperbarui.');
    }

    public function destroyQuestion(AssessmentQuestion $question): RedirectResponse
    {
        $question->delete();

        return redirect()->route('admin.assessments.index')->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Validate a submitted multiple-choice question.
     *
     * Expects `question`, `options[key][text]` (+ `options[key][id]` for an
     * existing option when editing) and `correct_options[]` holding the keys
     * of the correct options (one or more). Returns [question text, options,
     * correct keys].
     */
    private function validatedQuestion(Request $request, ?AssessmentQuestion $question = null): array
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'max:2000'],
            'options' => ['required', 'array', 'min:'.self::MIN_OPTIONS, 'max:'.self::MAX_OPTIONS],
            'options.*.text' => ['required', 'string', 'max:255'],
            'options.*.id' => ['nullable', 'integer'],
            'correct_options' => ['required', 'array', 'min:1'],
            'correct_options.*' => ['required', 'string'],
        ], [
            'question.required' => 'Pertanyaan wajib diisi.',
            'options.min' => 'Soal minimal memiliki '.self::MIN_OPTIONS.' pilihan jawaban.',
            'options.max' => 'Soal maksimal memiliki '.self::MAX_OPTIONS.' pilihan jawaban.',
            'options.*.text.required' => 'Semua pilihan jawaban wajib diisi.',
            'correct_options.required' => 'Tandai minimal satu jawaban yang benar.',
        ]);

        $options = $data['options'];
        $correctKeys = array_values(array_unique(array_map('strval', $data['correct_options'])));

        if (array_diff($correctKeys, array_map('strval', array_keys($options))) !== []) {
            throw ValidationException::withMessages(['correct_options' => 'Tandai minimal satu jawaban yang benar.']);
        }

        $texts = array_map(fn ($option) => mb_strtolower(trim($option['text'])), $options);
        if (count($texts) !== count(array_unique($texts))) {
            throw ValidationException::withMessages(['options' => 'Pilihan jawaban tidak boleh sama.']);
        }

        $existingIds = $question ? $question->options()->pluck('id')->all() : [];
        $submittedIds = array_values(array_filter(array_map(fn ($option) => (int) ($option['id'] ?? 0), $options)));

        if (array_diff($submittedIds, $existingIds) !== []) {
            throw ValidationException::withMessages(['options' => 'Data pilihan jawaban tidak valid. Muat ulang halaman lalu coba lagi.']);
        }

        // Removing an option some respondent already picked would erase it from their answer history.
        $removedIds = array_diff($existingIds, $submittedIds);
        if ($removedIds !== [] && DB::table('assessment_answer_options')->whereIn('assessment_option_id', $removedIds)->exists()) {
            throw ValidationException::withMessages([
                'options' => 'Pilihan yang sudah pernah dijawab responden tidak dapat dihapus. Ubah teksnya saja.',
            ]);
        }

        return [$data['question'], $options, $correctKeys];
    }

    /**
     * Update existing options in place (so stored answers keep pointing at
     * them), create new ones, and delete the ones that were removed.
     */
    private function syncOptions(AssessmentQuestion $question, array $options, array $correctKeys): void
    {
        $keptIds = [];
        $order = 0;

        foreach ($options as $key => $option) {
            $attributes = [
                'option_text' => trim($option['text']),
                'order' => $order++,
                'is_correct' => in_array((string) $key, $correctKeys, true),
            ];

            if (! empty($option['id'])) {
                $question->options()->whereKey($option['id'])->update($attributes);
                $keptIds[] = (int) $option['id'];
            } else {
                $keptIds[] = $question->options()->create($attributes)->id;
            }
        }

        $question->options()->whereNotIn('id', $keptIds)->delete();
    }
}
