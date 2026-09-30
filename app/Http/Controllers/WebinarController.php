<?php

namespace App\Http\Controllers;

use App\Models\Webinar;

class WebinarController extends Controller
{
    /**
     * Display a paginated list of published webinars: upcoming ones first
     * (soonest on top), then past ones (most recently finished on top).
     */
    public function index()
    {
        $now = now();

        $webinars = Webinar::query()
            ->where('is_published', true)
            ->orderByRaw('webinar_date < ? ASC', [$now])
            ->orderByRaw('CASE WHEN webinar_date >= ? THEN webinar_date END ASC', [$now])
            ->orderByDesc('webinar_date')
            ->paginate(9)
            ->withQueryString();

        return view('user.webinars.index', compact('webinars'));
    }
}
