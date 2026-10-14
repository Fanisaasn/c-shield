<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\SurveyQuestion;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Display a paginated list of published articles.
     */
    public function index(Request $request)
    {
        $validated = $request->validate(['q' => ['nullable', 'string', 'max:200']]);
        $search = trim($validated['q'] ?? '');

        $articles = Article::query()
            ->where('is_published', true)
            ->when($search !== '', fn ($query) => $query->whereLike('title', '%'.$search.'%'))
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.articles.index', compact('articles', 'search'));
    }

    /**
     * Display a single published article.
     */
    public function show(Article $article)
    {
        abort_unless($article->is_published, 404);

        $surveyQuestions = SurveyQuestion::query()->orderBy('sort_order')->get();

        return view('user.articles.show', compact('article', 'surveyQuestions'));
    }
}
