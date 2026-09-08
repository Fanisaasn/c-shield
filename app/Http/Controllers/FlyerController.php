<?php

namespace App\Http\Controllers;

use App\Models\Flyer;
use App\Models\SurveyQuestion;

class FlyerController extends Controller
{
    /**
     * Display a paginated list of published flyers.
     */
    public function index()
    {
        $flyers = Flyer::query()
            ->where('is_published', true)
            ->with(['images' => fn ($query) => $query->orderBy('sort_order')->limit(1)])
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.flyers.index', compact('flyers'));
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
