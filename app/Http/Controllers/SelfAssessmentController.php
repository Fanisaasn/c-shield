<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentQuestion;
use App\Models\AssessmentUser;
use App\Models\LearningMaterial;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SelfAssessmentController extends Controller
{
    /**
     * Show the Self-Assessment landing page: a dropdown to start the
     * Pre-Assessment, or (once the session's Pre-Assessment is completed
     * and its Post-Assessment is not yet finished) the Post-Assessment.
     */
    public function index(Request $request)
    {
        $postUnlocked = $this->postUnlocked($request);

        return view('user.self-assessment.index', compact('postUnlocked'));
    }

    /**
     * Dispatch on the chosen mode: Pre-Assessment always starts a fresh
     * identity form; Post-Assessment is only reachable while the session's
     * current round has a completed Pre- and an unfinished Post-Assessment.
     */
    public function start(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mode' => 'required|in:pre,post',
        ]);

        if ($validated['mode'] === 'pre') {
            return redirect()->route('self-assessment.create');
        }

        if (! $this->postUnlocked($request)) {
            return $this->postLockedRedirect();
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
            'domicile' => ['required', Rule::in(AssessmentUser::domicileOptions())],
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
        $request->session()->forget(['assessment_post_attempt_id', 'assessment_materi_viewed']);

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
     * Show the mandatory study step between the Pre-Assessment result and
     * the Post-Assessment: the single admin-managed Social Engineering
     * material. Opening it is recorded so the Post-Assessment can unlock.
     */
    public function materi(Request $request)
    {
        if (! $this->postUnlocked($request)) {
            return $this->postLockedRedirect();
        }

        if ($request->session()->get('assessment_post_attempt_id')) {
            return redirect()->route('self-assessment.post.quiz');
        }

        $material = LearningMaterial::current();

        if ($material?->isReady()) {
            $request->session()->put('assessment_materi_viewed', true);
        }

        return view('user.self-assessment.materi', [
            'material' => $material?->isReady() ? $material : null,
        ]);
    }

    /**
     * Open the Post-Assessment attempt and move the user into the
     * Post-Assessment quiz, once the study material has been opened and
     * the user has confirmed studying it.
     */
    public function materiLanjut(Request $request): RedirectResponse
    {
        if (! $this->postUnlocked($request)) {
            return $this->postLockedRedirect();
        }

        if ($request->session()->get('assessment_post_attempt_id') === null) {
            $material = LearningMaterial::current();

            if (! $material?->isReady() || ! $request->session()->has('assessment_materi_viewed')) {
                return redirect()->route('self-assessment.materi')
                    ->with('error', 'Pelajari materi terlebih dahulu sebelum mengerjakan Post-Assessment.');
            }

            if (! $request->boolean('confirm_studied')) {
                return redirect()->route('self-assessment.materi')
                    ->with('error', 'Centang konfirmasi bahwa Anda sudah mempelajari materi.');
            }

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
            'assessment_materi_viewed',
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
        $questions = $this->questions()->keyBy('id');

        // A single-answer question submits one option id (radio); a
        // multi-answer question submits an array of option ids (checkboxes).
        $validated = $request->validate([
            'answers' => 'required|array|size:' . $questions->count(),
            'answers.*' => 'required',
        ], [
            'answers.size' => 'Jawab semua pertanyaan terlebih dahulu.',
            'answers.*.required' => 'Jawab semua pertanyaan terlebih dahulu.',
        ]);

        $selections = [];

        foreach ($questions as $questionId => $question) {
            $chosen = collect((array) ($validated['answers'][$questionId] ?? []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->sort()
                ->values();

            if ($chosen->isEmpty() || $chosen->diff($question->options->pluck('id'))->isNotEmpty()) {
                return back()->withInput()->with('error', 'Jawaban tidak valid. Silakan coba lagi.');
            }

            $selections[$questionId] = $chosen;
        }

        DB::transaction(function () use ($questions, $selections, $attempt) {
            foreach ($selections as $questionId => $chosen) {
                $correctIds = $questions[$questionId]->options->where('is_correct', true)->pluck('id')->sort()->values();

                // All-or-nothing: every correct option picked and nothing else.
                $answer = AssessmentAnswer::create([
                    'assessment_attempt_id' => $attempt->id,
                    'assessment_question_id' => $questionId,
                    'is_correct' => $chosen->all() === $correctIds->all(),
                ]);

                $answer->options()->attach($chosen->all());
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
            ->with([
                'question.options' => fn ($query) => $query->orderBy('order'),
                'options' => fn ($query) => $query->orderBy('order'),
            ])
            ->get()
            ->sortBy('question.order')
            ->map(fn (AssessmentAnswer $answer) => [
                'question' => $answer->question->question,
                'chosen' => $answer->options->pluck('option_text')->implode(', '),
                'is_correct' => $answer->is_correct,
                'correct_option' => $answer->is_correct
                    ? null
                    : $answer->question->options->where('is_correct', true)->pluck('option_text')->implode(', '),
            ])
            ->values();
    }

    /**
     * Whether the Post-Assessment may be (re)entered: the session's
     * Pre-Assessment is completed and its Post-Assessment is not finished.
     * Once a Post-Assessment is finished the round is over, and a new
     * Pre-Assessment must be taken before the Post-Assessment opens again.
     */
    private function postUnlocked(Request $request): bool
    {
        return $this->currentPreAttempt($request)?->completed_at !== null
            && $this->currentPostAttempt($request)?->completed_at === null;
    }

    private function postLockedRedirect(): RedirectResponse
    {
        return redirect()->route('self-assessment.index')
            ->with('error', 'Kerjakan Pre-Assessment terlebih dahulu.');
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
