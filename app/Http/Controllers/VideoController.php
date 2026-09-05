<?php

namespace App\Http\Controllers;

use App\Models\Video;

class VideoController extends Controller
{
    /**
     * Display a paginated list of published videos.
     */
    public function index()
    {
        $videos = Video::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.videos.index', compact('videos'));
    }

    /**
     * Display a single published video.
     */
    public function show(Video $video)
    {
        abort_unless($video->is_published, 404);

        return view('user.videos.show', compact('video'));
    }
}
