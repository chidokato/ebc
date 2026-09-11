@extends('news.layout')
@section('title', ['vi' => 'Tin tức', 'en' => 'News', 'zh' => '新闻', 'ko' => '뉴스'][$locale])
@section('content')
<nav class="d-flex gap-3 mb-4">@foreach (\App\Models\NewsArticle::LANGUAGES as $code => $label)<a href="{{ \App\Support\LocalizedUrl::route('news.index', ['locale' => $code]) }}" @if($code === $locale) aria-current="page" @endif>{{ $label }}</a>@endforeach</nav>
<h1 class="mb-4">@yield('title')</h1><div class="row g-4">@forelse ($articles as $article)<div class="col-md-4"><article class="card h-100">
@if($article->image_path)<img src="{{ asset($article->image_path) }}" alt="" class="card-img-top" style="height:200px;object-fit:cover">@endif
<div class="card-body"><time>{{ $article->published_at->format('d/m/Y') }}</time><h2 class="h4 mt-2"><a href="{{ \App\Support\LocalizedUrl::route('news.show', ['locale' => $locale, 'news' => $article->id]) }}">{{ $article->title }}</a></h2><p>{{ $article->excerpt }}</p></div></article></div>
@empty<p>{{ ['vi' => 'Chưa có tin tức.', 'en' => 'No news yet.', 'zh' => '暂无新闻。', 'ko' => '아직 뉴스가 없습니다.'][$locale] }}</p>@endforelse</div><div class="mt-4">{{ $articles->links() }}</div>
@endsection
