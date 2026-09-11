@extends('news.layout')
@section('title', $article->title)
@section('content')
<nav class="d-flex gap-3 mb-4">@foreach ($translations as $translation)<a href="{{ \App\Support\LocalizedUrl::route('news.show', ['locale' => $translation->locale, 'news' => $translation->id]) }}" @if($translation->locale === $locale) aria-current="page" @endif>{{ \App\Models\NewsArticle::LANGUAGES[$translation->locale] }}</a>@endforeach</nav>
<article><time>{{ $article->published_at->format('d/m/Y') }}</time><h1 class="my-3">{{ $article->title }}</h1><p class="lead">{{ $article->excerpt }}</p>@if($article->image_path)<img src="{{ asset($article->image_path) }}" alt="{{ $article->title }}" class="news-cover mb-4">@endif<div class="news-content">{!! $article->renderedContent() !!}</div></article>
@endsection
