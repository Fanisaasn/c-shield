<?php

namespace App\Http\Controllers;

use App\Models\Webinar;

class WebinarController extends Controller
{
    /**
     * Display a paginated list of published webinars, soonest first.
     */
    public function index()
    {
        $webinars = Webinar::query()
            ->where('is_published', true)
            ->orderBy('webinar_date')
            ->paginate(9)
            ->withQueryString();

        return view('user.webinars.index', compact('webinars'));
    }
}
