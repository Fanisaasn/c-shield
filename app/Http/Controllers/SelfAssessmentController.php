<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentCategory;
use App\Models\AssessmentOption;
use App\Models\AssessmentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelfAssessmentController extends Controller
{
    /**
     * Show the list of assessment themes (categories) the user can pick
     * from before starting a Pre-Assessment.
     */
    public function themes()
    {
        $categories = AssessmentCategory::query()
            ->withCount('questions')
            ->orderBy('order')
            ->get();

        return view('user.self-assessment.themes', compact('categories'));
    }

    /**
     * Show the respondent identity form that starts the Pre-Assessment for
     * the chosen theme.
     */
    public function create(AssessmentCategory $category)
    {
        return view('user.self-assessment.create', compact('category'));
    }

    /**
     * Store the respondent identity, open a Pre-Assessment attempt scoped
     * to the chosen theme, and remember both in the session so the
     * quiz/result pages can find them without requiring an account.
     */
    public function store(Request $request, AssessmentCategory $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_last_digits' => 'required|digits:4',
            'gender' => 'required|in:Laki-laki,Perempuan',
            'age' => 'required|integer|min:5|max:120',
            'education' => 'required|string|max:255',
            'domicile' => 'required|string|max:255',
            'occupation_status' => 'required|string|max:255',
        ]);

        $user = AssessmentUser::create($validated);

        $attempt = AssessmentAttempt::create([
            'assessment_user_id' => $user->id,
            'type' => 'pre',
            'started_at' => now(),
        ]);

        $request->session()->put('assessment_user_id', $user->id);
        $request->session()->put('assessment_pre_attempt_id', $attempt->id);
        $request->session()->put('assessment_theme_category_id', $category->id);

        return redirect()->route('self-assessment.pre.quiz');
    }

    /**
     * Show every question belonging to the chosen theme on a single page.
     */
    public function preQuiz(Request $request)
    {
        $attempt = $this->currentPreAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Silakan pilih tema dan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.pre.result');
        }

        $questions = $category->questions()
            ->with(['options' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get();

        return view('user.self-assessment.pre-quiz', compact('category', 'questions'));
    }

    /**
     * Save the submitted Pre-Assessment answers, score the attempt, and
     * redirect to the result page.
     */
    public function storePreQuiz(Request $request): RedirectResponse
    {
        $attempt = $this->currentPreAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Silakan pilih tema dan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.pre.result');
        }

        $questionIds = $category->questions()->pluck('id');

        $validated = $request->validate([
            'answers' => 'required|array|size:' . $questionIds->count(),
            'answers.*' => 'required|integer|exists:assessment_options,id',
        ]);

        if ($questionIds->diff(array_keys($validated['answers']))->isNotEmpty()) {
            return back()->withInput()->with('error', 'Jawaban tidak valid. Silakan coba lagi.');
        }

        $options = AssessmentOption::query()
            ->whereIn('id', array_values($validated['answers']))
            ->get()
            ->keyBy('id');

        foreach ($validated['answers'] as $questionId => $optionId) {
            $option = $options->get($optionId);

            if ($option === null || (int) $option->assessment_question_id !== (int) $questionId) {
                return back()->withInput()->with('error', 'Jawaban tidak valid. Silakan coba lagi.');
            }
        }

        DB::transaction(function () use ($validated, $options, $attempt) {
            foreach ($validated['answers'] as $questionId => $optionId) {
                $option = $options->get($optionId);

                AssessmentAnswer::create([
                    'assessment_attempt_id' => $attempt->id,
                    'assessment_question_id' => $questionId,
                    'assessment_option_id' => $optionId,
                    'is_correct' => $option->is_correct,
                ]);
            }

            $attempt->finalizeScore();
        });

        return redirect()->route('self-assessment.pre.result');
    }

    /**
     * Show the Pre-Assessment result for the chosen theme: overall score,
     * level, and a per-question review.
     */
    public function preResult(Request $request)
    {
        $attempt = $this->currentPreAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Silakan pilih tema dan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at === null) {
            return redirect()->route('self-assessment.pre.quiz');
        }

        $answerReview = $attempt->answers()
            ->with(['question' => fn ($query) => $query->orderBy('order'), 'option'])
            ->get()
            ->sortBy('question.order')
            ->map(fn (AssessmentAnswer $answer) => [
                'question' => $answer->question->question,
                'chosen' => $answer->option->option_text,
                'is_correct' => $answer->is_correct,
                'correct_option' => $answer->is_correct
                    ? null
                    : $answer->question->options()->where('is_correct', true)->value('option_text'),
            ])
            ->values();

        $respondent = AssessmentUser::find($request->session()->get('assessment_user_id'));

        return view('user.self-assessment.pre-result', [
            'attempt' => $attempt,
            'category' => $category,
            'respondent' => $respondent,
            'answerReview' => $answerReview,
        ]);
    }

    /**
     * Resolve the Pre-Assessment attempt tied to the current browser
     * session, or null if none exists / it no longer exists in storage.
     */
    private function currentPreAttempt(Request $request): ?AssessmentAttempt
    {
        $attemptId = $request->session()->get('assessment_pre_attempt_id');

        return $attemptId ? AssessmentAttempt::find($attemptId) : null;
    }

    /**
     * Resolve the assessment theme (category) chosen for the current
     * browser session, or null if none was chosen / it no longer exists.
     */
    private function currentTheme(Request $request): ?AssessmentCategory
    {
        $categoryId = $request->session()->get('assessment_theme_category_id');

        return $categoryId ? AssessmentCategory::find($categoryId) : null;
    }
}
