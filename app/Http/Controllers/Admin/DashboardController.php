<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentUser;
use App\Models\Flyer;
use App\Models\Video;
use App\Models\Webinar;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index()
    {
        $stats = [
            [
                'label' => 'Artikel',
                'total' => Article::count(),
                'published' => Article::where('is_published', true)->count(),
            ],
            [
                'label' => 'Video',
                'total' => Video::count(),
                'published' => Video::where('is_published', true)->count(),
            ],
            [
                'label' => 'Flyer',
                'total' => Flyer::count(),
                'published' => Flyer::where('is_published', true)->count(),
            ],
            [
                'label' => 'Webinar',
                'total' => Webinar::count(),
                'published' => Webinar::where('is_published', true)->count(),
            ],
        ];

        $assessmentStats = [
            'participants' => AssessmentUser::count(),
            'attempts' => AssessmentAttempt::count(),
        ];

        return view('admin.dashboard', compact('stats', 'assessmentStats'));
    }
}
