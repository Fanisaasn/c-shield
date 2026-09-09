<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentOption;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentUser;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelfAssessmentController extends Controller
{
    /**
     * Show the Self-Assessment landing page: a dropdown to start the
     * Pre-Assessment, or (once the session's Pre-Assessment is completed)
     * the Post-Assessment.
     */
    public function index(Request $request)
    {
        $preCompleted = $this->currentPreAttempt($request)?->completed_at !== null;

        return view('user.self-assessment.index', compact('preCompleted'));
    }

    /**
     * Dispatch on the chosen mode: Pre-Assessment always starts a fresh
     * identity form; Post-Assessment is only reachable once the session's
     * Pre-Assessment attempt is completed.
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:pre,post',
        ]);

        if ($validated['mode'] === 'pre') {
            return redirect()->route('self-assessment.create');
        }

        if ($this->currentPreAttempt($request)?->completed_at === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Kerjakan Pre-Assessment terlebih dahulu.');
        }

        return redirect()->route('self-assessment.materi');
    }

    /**
     * Show the respondent identity form that starts a Pre-Assessment.
     * Always shown fresh: a respondent is allowed to take the
     * Self-Assessment as many times as they like, each run starting its
     * own identity + Pre/Post attempt pair.
     */
    public function create()
    {
        return view('user.self-assessment.create');
    }

    /**
     * Store the respondent identity, open a Pre-Assessment attempt, and
     * remember both in the session so the quiz/result pages can find them
     * without requiring an account.
     */
    public function store(Request $request): RedirectResponse
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
        $request->session()->forget('assessment_post_attempt_id');

        return redirect()->route('self-assessment.pre.quiz');
    }

    /**
     * Show every assessment question on a single page.
     */
    public function preQuiz(Request $request)
    {
        $attempt = $this->currentPreAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Silakan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.pre.result');
        }

        $questions = $this->questions();

        return view('user.self-assessment.pre-quiz', compact('questions'));
    }

    /**
     * Save the submitted Pre-Assessment answers, score the attempt, and
     * redirect to the result page.
     */
    public function storePreQuiz(Request $request): RedirectResponse
    {
        $attempt = $this->currentPreAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Silakan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.pre.result');
        }

        return $this->storeAnswers($request, $attempt) ?? redirect()->route('self-assessment.pre.result');
    }

    /**
     * Show the Pre-Assessment result: overall score, level, and a
     * per-question review.
     */
    public function preResult(Request $request)
    {
        $attempt = $this->currentPreAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Silakan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at === null) {
            return redirect()->route('self-assessment.pre.quiz');
        }

        $respondent = AssessmentUser::find($request->session()->get('assessment_user_id'));

        return view('user.self-assessment.pre-result', [
            'attempt' => $attempt,
            'respondent' => $respondent,
            'answerReview' => $this->answerReview($attempt),
        ]);
    }

    /**
     * Show the study-reminder step between the Pre-Assessment result and
     * the Post-Assessment: a nudge to study the site's existing Video,
     * Artikel, and Flyer content (nothing here is tracked).
     */
    public function materi(Request $request)
    {
        $attempt = $this->currentPreAttempt($request);

        if ($attempt === null || $attempt->completed_at === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Kerjakan Pre-Assessment terlebih dahulu.');
        }

        if ($request->session()->get('assessment_post_attempt_id')) {
            return redirect()->route('self-assessment.post.quiz');
        }

        return view('user.self-assessment.materi');
    }

    /**
     * Open the Post-Assessment attempt and move the user into the
     * Post-Assessment quiz.
     */
    public function materiLanjut(Request $request): RedirectResponse
    {
        $attempt = $this->currentPreAttempt($request);

        if ($attempt === null || $attempt->completed_at === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Kerjakan Pre-Assessment terlebih dahulu.');
        }

        if ($request->session()->get('assessment_post_attempt_id') === null) {
            $postAttempt = AssessmentAttempt::create([
                'assessment_user_id' => $request->session()->get('assessment_user_id'),
                'type' => 'post',
                'started_at' => now(),
            ]);

            $request->session()->put('assessment_post_attempt_id', $postAttempt->id);
        }

        return redirect()->route('self-assessment.post.quiz');
    }

    /**
     * Show every assessment question for the Post-Assessment, once the
     * study-reminder step is done.
     */
    public function postQuiz(Request $request)
    {
        $attempt = $this->currentPostAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Selesaikan Pre-Assessment terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.post.result');
        }

        $questions = $this->questions();

        return view('user.self-assessment.post-quiz', compact('questions'));
    }

    /**
     * Save the submitted Post-Assessment answers, score the attempt, and
     * redirect to the result page.
     */
    public function storePostQuiz(Request $request): RedirectResponse
    {
        $attempt = $this->currentPostAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Selesaikan Pre-Assessment terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.post.result');
        }

        return $this->storeAnswers($request, $attempt) ?? redirect()->route('self-assessment.post.result');
    }

    /**
     * Show the Post-Assessment result: the user's final understanding
     * score, level, and a per-question review.
     */
    public function postResult(Request $request)
    {
        $attempt = $this->currentPostAttempt($request);

        if ($attempt === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Selesaikan Pre-Assessment terlebih dahulu.');
        }

        if ($attempt->completed_at === null) {
            return redirect()->route('self-assessment.post.quiz');
        }

        $respondent = AssessmentUser::find($request->session()->get('assessment_user_id'));

        return view('user.self-assessment.post-result', [
            'attempt' => $attempt,
            'respondent' => $respondent,
            'answerReview' => $this->answerReview($attempt),
        ]);
    }

    /**
     * Show the respondent their own Pre vs Post score comparison, the
     * final step of the Self-Assessment journey.
     */
    public function comparison(Request $request)
    {
        $preAttempt = $this->currentPreAttempt($request);
        $postAttempt = $this->currentPostAttempt($request);

        if ($preAttempt === null || $postAttempt === null
            || $preAttempt->completed_at === null || $postAttempt->completed_at === null) {
            return redirect()->route('self-assessment.index')
                ->with('error', 'Selesaikan Pre-Assessment dan Post-Assessment terlebih dahulu.');
        }

        $respondent = AssessmentUser::find($request->session()->get('assessment_user_id'));

        return view('user.self-assessment.comparison', [
            'respondent' => $respondent,
            'preAttempt' => $preAttempt,
            'postAttempt' => $postAttempt,
        ]);
    }

    /**
     * Clear the respondent's Self-Assessment session so a new round (fresh
     * identity + Pre-Assessment) can start; Post-Assessment locks again
     * until that new Pre-Assessment is completed.
     */
    public function restart(Request $request): RedirectResponse
    {
        $request->session()->forget([
            'assessment_user_id',
            'assessment_pre_attempt_id',
            'assessment_post_attempt_id',
        ]);

        return redirect()->route('self-assessment.create');
    }

    /**
     * Every assessment question, in order, with its options. The same
     * single set of questions is used for both Pre- and Post-Assessment.
     */
    private function questions()
    {
        return AssessmentQuestion::query()
            ->with(['options' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get();
    }

    /**
     * Validate and store the submitted answers for the given attempt, then
     * finalize its score. Returns a redirect back to the form on a
     * validation failure, or null on success.
     */
    private function storeAnswers(Request $request, AssessmentAttempt $attempt): ?RedirectResponse
    {
        $questionIds = AssessmentQuestion::query()->pluck('id');

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

        return null;
    }

    /**
     * Build the per-question answer review shown on a result page.
     */
    private function answerReview(AssessmentAttempt $attempt)
    {
        return $attempt->answers()
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
     * Resolve the Post-Assessment attempt tied to the current browser
     * session, or null if none exists / it no longer exists in storage.
     */
    private function currentPostAttempt(Request $request): ?AssessmentAttempt
    {
        $attemptId = $request->session()->get('assessment_post_attempt_id');

        return $attemptId ? AssessmentAttempt::find($attemptId) : null;
    }
}
