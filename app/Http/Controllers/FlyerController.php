<?php

namespace App\Http\Controllers;

use App\Models\Flyer;

class FlyerController extends Controller
{
    /**
     * Display a paginated list of published flyers.
     */
    public function index()
    {
        $flyers = Flyer::query()
            ->where('is_published', true)
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

        return view('user.flyers.show', compact('flyer'));
    }
}
