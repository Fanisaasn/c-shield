<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Flyer;
use App\Models\SurveyQuestion;

class FlyerController extends Controller
{
    /**
     * Display a paginated list of published flyers.
     */
    public function index(Request $request)
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $search = trim($validated['q'] ?? '');

        $flyers = Flyer::query()
            ->where('is_published', true)
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->with(['images' => fn ($query) => $query->orderBy('sort_order')->limit(1)])
            ->withCount('images')
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.flyers.index', compact('flyers', 'search'));
    }

    /**
     * Display a single published flyer.
     */
    public function show(Flyer $flyer)
    {
        abort_unless($flyer->is_published, 404);

        $flyer->load('images');

        $surveyQuestions = SurveyQuestion::query()->orderBy('sort_order')->get();

        return view('user.flyers.show', compact('flyer', 'surveyQuestions'));
    }
}
