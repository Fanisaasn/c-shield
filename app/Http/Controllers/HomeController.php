<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Webinar;

class HomeController extends Controller
{
    /**
     * Display the public landing page.
     */
    public function index()
    {
        $latestArticles = Article::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        $upcomingWebinars = Webinar::query()
            ->where('is_published', true)
            ->where('webinar_date', '>=', now())
            ->orderBy('webinar_date')
            ->take(2)
            ->get();

        return view('user.home', compact('latestArticles', 'upcomingWebinars'));
    }
}
