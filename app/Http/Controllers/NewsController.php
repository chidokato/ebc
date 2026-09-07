<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;

class NewsController extends Controller
{
    public function index(string $locale)
    {
        return view('news.index', ['locale' => $locale, 'articles' => NewsArticle::where('locale', $locale)->published()->orderByDesc('published_at')->paginate(12)]);
    }

    public function show(string $locale, string $news)
    {
        $article = NewsArticle::where('locale', $locale)->published()->findOrFail($news);
        return view('news.show', ['locale' => $locale, 'article' => $article, 'translations' => NewsArticle::where('translation_group', $article->translation_group)->published()->get()]);
    }
}
