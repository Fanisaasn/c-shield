<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\AssessmentAttempt;
use App\Models\AssessmentCategory;
use App\Models\AssessmentUser;
use App\Models\Flyer;
use App\Models\Video;
use App\Models\Webinar;
use Illuminate\Support\Collection;

class DashboardController extends Controller
{
    private const LEVEL_ORDER = ['Sangat Rendah', 'Rendah', 'Cukup', 'Baik', 'Sangat Baik'];

    private const GENDER_ORDER = ['Laki-laki', 'Perempuan'];

    private const AGE_BRACKETS = [
        '< 18' => [null, 17],
        '18–25' => [18, 25],
        '26–35' => [26, 35],
        '36–45' => [36, 45],
        '46–55' => [46, 55],
        '56+' => [56, null],
    ];

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

        $currentYear = now()->year;

        $completedAttempts = AssessmentAttempt::query()
            ->whereNotNull('completed_at')
            ->whereYear('completed_at', $currentYear)
            ->with(['user', 'answers.question.category'])
            ->get()
            ->filter(fn (AssessmentAttempt $attempt) => $attempt->user !== null);

        return view('admin.dashboard', [
            'stats' => $stats,
            'assessmentStats' => $assessmentStats,
            'currentYear' => $currentYear,
            'levelDistribution' => $this->levelDistribution($completedAttempts),
            'scoreByCategory' => $this->averageScoreByCategory($completedAttempts),
            'scoreByGender' => $this->averageScoreByGender($completedAttempts),
            'scoreByEducation' => $this->averageScoreByEducation($completedAttempts),
            'scoreByAge' => $this->averageScoreByAge($completedAttempts),
            'scoreByDomicile' => $this->averageScoreByDomicile($completedAttempts),
        ]);
    }

    /**
     * Count of completed attempts per awareness level (fixed order), used
     * for the "Persentase Hasil Assessment" chart.
     */
    private function levelDistribution(Collection $attempts): Collection
    {
        return collect(self::LEVEL_ORDER)->map(fn (string $level) => [
            'label' => $level,
            'count' => $attempts->where('level', $level)->count(),
        ]);
    }

    /**
     * Average score per assessment theme (category), in the themes' own
     * display order. Every attempt now covers exactly one theme, so the
     * theme is read off the attempt's first answered question.
     */
    private function averageScoreByCategory(Collection $attempts): Collection
    {
        $categoryOrder = AssessmentCategory::query()->orderBy('order')->pluck('order', 'name');

        return $attempts
            ->map(fn (AssessmentAttempt $attempt) => [
                'category' => $attempt->answers->first()?->question?->category?->name,
                'score' => (float) $attempt->score,
            ])
            ->filter(fn (array $row) => $row['category'] !== null)
            ->groupBy('category')
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'average' => round($group->avg('score'), 1),
                'count' => $group->count(),
            ])
            ->sortBy(fn (array $row) => $categoryOrder[$row['label']] ?? 999)
            ->values();
    }

    /**
     * Average score per gender, in a fixed order.
     */
    private function averageScoreByGender(Collection $attempts): Collection
    {
        return collect(self::GENDER_ORDER)
            ->map(function (string $gender) use ($attempts) {
                $group = $attempts->filter(fn (AssessmentAttempt $attempt) => $attempt->user->gender === $gender);

                return [
                    'label' => $gender,
                    'average' => $group->isEmpty() ? 0 : round((float) $group->avg('score'), 1),
                    'count' => $group->count(),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    /**
     * Average score per education group, most common group first.
     */
    private function averageScoreByEducation(Collection $attempts): Collection
    {
        return $attempts->groupBy(fn (AssessmentAttempt $attempt) => $attempt->user->education)
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'average' => round((float) $group->avg('score'), 1),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->values();
    }

    /**
     * Average score per age bracket, in a fixed youngest-to-oldest order.
     */
    private function averageScoreByAge(Collection $attempts): Collection
    {
        return collect(self::AGE_BRACKETS)
            ->map(function (array $range, string $label) use ($attempts) {
                [$min, $max] = $range;

                $group = $attempts->filter(function (AssessmentAttempt $attempt) use ($min, $max) {
                    $age = $attempt->user->age;

                    return ($min === null || $age >= $min) && ($max === null || $age <= $max);
                });

                return [
                    'label' => $label,
                    'average' => $group->isEmpty() ? 0 : round((float) $group->avg('score'), 1),
                    'count' => $group->count(),
                ];
            })
            ->filter(fn (array $row) => $row['count'] > 0)
            ->values();
    }

    /**
     * Average score for the most-represented domicile groups.
     */
    private function averageScoreByDomicile(Collection $attempts): Collection
    {
        return $attempts->groupBy(fn (AssessmentAttempt $attempt) => $attempt->user->domicile)
            ->map(fn (Collection $group, string $label) => [
                'label' => $label,
                'average' => round((float) $group->avg('score'), 1),
                'count' => $group->count(),
            ])
            ->sortByDesc('count')
            ->take(8)
            ->values();
    }
}
