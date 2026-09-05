<?php

namespace App\Http\Controllers;

use App\Models\Article;

class ArticleController extends Controller
{
    /**
     * Display a paginated list of published articles.
     */
    public function index()
    {
        $articles = Article::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->paginate(9)
            ->withQueryString();

        return view('user.articles.index', compact('articles'));
    }

    /**
     * Display a single published article.
     */
    public function show(Article $article)
    {
        abort_unless($article->is_published, 404);

        return view('user.articles.show', compact('article'));
    }
}
