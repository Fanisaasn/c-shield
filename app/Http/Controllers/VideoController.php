<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\SurveyQuestion;
use App\Models\Video;

class VideoController extends Controller
{
    /**
     * Display a paginated list of published videos.
     */
    public function index(Request $request)
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $search = trim($validated['q'] ?? '');

        $videos = Video::query()
            ->where('is_published', true)
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.videos.index', compact('videos', 'search'));
    }

    /**
     * Display a single published video.
     */
    public function show(Video $video)
    {
        abort_unless($video->is_published, 404);

        $surveyQuestions = SurveyQuestion::query()->orderBy('sort_order')->get();

        return view('user.videos.show', compact('video', 'surveyQuestions'));
    }
}
