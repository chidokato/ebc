<?php

namespace App\Http\Controllers;

use App\Models\NewsArticle;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $locale = $request->route('locale');
        return view('news.index', ['locale' => $locale, 'articles' => NewsArticle::where('locale', $locale)->published()->orderByDesc('published_at')->paginate(12)]);
    }

    public function show(Request $request)
    {
        $locale = $request->route('locale');
        $news = $request->route('news');
        $article = NewsArticle::where('locale', $locale)->published()->findOrFail($news);
        return view('news.show', ['locale' => $locale, 'article' => $article, 'translations' => NewsArticle::where('translation_group', $article->translation_group)->published()->get()]);
    }
}
