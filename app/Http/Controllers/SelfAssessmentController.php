<?php

namespace App\Http\Controllers;

use App\Models\AssessmentAnswer;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentCategory;
use App\Models\AssessmentOption;
use App\Models\AssessmentUser;
use App\Models\AssessmentVideo;
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
     * Show the respondent identity form that starts a Pre-Assessment for
     * the chosen theme. Always shown fresh: a respondent is allowed to
     * take the Self-Assessment as many times as they like, each run
     * starting its own identity + Pre/Post attempt pair.
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
        $request->session()->forget(['assessment_post_attempt_id', 'assessment_video_watched']);

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
     * Show the mandatory "Video Materi" step between the Pre-Assessment
     * result and the Post-Assessment. If no active video is configured by
     * the admin yet, the user is not blocked: they are sent straight into
     * the Post-Assessment.
     */
    public function video(Request $request)
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

        if ($request->session()->get('assessment_post_attempt_id')) {
            return redirect()->route('self-assessment.post.quiz');
        }

        $video = AssessmentVideo::query()
            ->where('is_active', true)
            ->where(function ($query) use ($category) {
                $query->where('assessment_category_id', $category->id)
                    ->orWhereNull('assessment_category_id');
            })
            // Prefer a video made specifically for this theme over the
            // fallback (no-theme) video.
            ->orderByRaw('assessment_category_id IS NULL')
            ->latest()
            ->first();

        if ($video === null) {
            return $this->startPostAssessment($request)
                ->with('info', 'Video materi belum tersedia. Anda dapat langsung mengerjakan Post-Assessment.');
        }

        return view('user.self-assessment.video', compact('category', 'video'));
    }

    /**
     * Mark the video step as done, open the Post-Assessment attempt, and
     * move the user into the Post-Assessment quiz.
     */
    public function completeVideo(Request $request): RedirectResponse
    {
        $attempt = $this->currentPreAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null || $attempt->completed_at === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Silakan pilih tema dan isi data diri terlebih dahulu.');
        }

        return $this->startPostAssessment($request);
    }

    /**
     * Show every question belonging to the chosen theme for the
     * Post-Assessment, once the video step is done.
     */
    public function postQuiz(Request $request)
    {
        $attempt = $this->currentPostAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null || ! $request->session()->get('assessment_video_watched')) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Selesaikan Pre-Assessment dan video materi terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.post.result');
        }

        $questions = $category->questions()
            ->with(['options' => fn ($query) => $query->orderBy('order')])
            ->orderBy('order')
            ->get();

        return view('user.self-assessment.post-quiz', compact('category', 'questions'));
    }

    /**
     * Save the submitted Post-Assessment answers, score the attempt, and
     * redirect to the result page.
     */
    public function storePostQuiz(Request $request): RedirectResponse
    {
        $attempt = $this->currentPostAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null || ! $request->session()->get('assessment_video_watched')) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Selesaikan Pre-Assessment dan video materi terlebih dahulu.');
        }

        if ($attempt->completed_at !== null) {
            return redirect()->route('self-assessment.post.result');
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

        return redirect()->route('self-assessment.post.result');
    }

    /**
     * Show the Post-Assessment result: the user's final understanding
     * score, level, and a per-question review.
     */
    public function postResult(Request $request)
    {
        $attempt = $this->currentPostAttempt($request);
        $category = $this->currentTheme($request);

        if ($attempt === null || $category === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Silakan pilih tema dan isi data diri terlebih dahulu.');
        }

        if ($attempt->completed_at === null) {
            return redirect()->route('self-assessment.post.quiz');
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

        return view('user.self-assessment.post-result', [
            'attempt' => $attempt,
            'category' => $category,
            'respondent' => $respondent,
            'answerReview' => $answerReview,
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
        $category = $this->currentTheme($request);

        if ($preAttempt === null || $postAttempt === null || $category === null
            || $preAttempt->completed_at === null || $postAttempt->completed_at === null) {
            return redirect()->route('self-assessment.themes')
                ->with('error', 'Selesaikan Pre-Assessment dan Post-Assessment terlebih dahulu.');
        }

        $respondent = AssessmentUser::find($request->session()->get('assessment_user_id'));

        return view('user.self-assessment.comparison', [
            'category' => $category,
            'respondent' => $respondent,
            'preAttempt' => $preAttempt,
            'postAttempt' => $postAttempt,
        ]);
    }

    /**
     * Open the Post-Assessment attempt (once) and mark the video step as
     * watched, then send the user into the Post-Assessment quiz.
     */
    private function startPostAssessment(Request $request): RedirectResponse
    {
        $postAttemptId = $request->session()->get('assessment_post_attempt_id');

        if ($postAttemptId === null) {
            $attempt = AssessmentAttempt::create([
                'assessment_user_id' => $request->session()->get('assessment_user_id'),
                'type' => 'post',
                'started_at' => now(),
            ]);

            $request->session()->put('assessment_post_attempt_id', $attempt->id);
        }

        $request->session()->put('assessment_video_watched', true);

        return redirect()->route('self-assessment.post.quiz');
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
