<!doctype html>
<html lang="{{ $locale }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title') | {{ $websiteSettings->title }}</title>
@include('partials.website-meta', ['seoPageTitle' => trim($__env->yieldContent('title')) . ' | ' . $websiteSettings->title])
<link href="{{ asset('admin-assets/css/bootstrap.min.css') }}" rel="stylesheet">
<style>body{background:#f7f5ef;color:#20382d}header{background:#173b2b;color:white}header a{color:inherit}main{max-width:1080px;margin:auto;padding:48px 20px}a{color:#245940}.news-content{white-space:normal;overflow-wrap:anywhere;font-size:18px;line-height:1.9}.news-cover{width:100%;max-height:520px;object-fit:cover;border-radius:12px}h1{overflow-wrap:anywhere}.news-content blockquote{border-left:4px solid #b7c6bb;padding-left:20px;font-style:italic}.news-content p{margin-bottom:1em}.news-content ul,.news-content ol{padding-left:2em}</style>
{!! $websiteSettings->head_code !!}
</head><body><header class="p-4"><div class="container d-flex justify-content-between flex-wrap gap-3"><a href="{{ route('home', ['locale' => $locale]) }}">@if($websiteSettings->white_logo_path || $websiteSettings->logo_path)<img src="{{ asset($websiteSettings->white_logo_path ?: $websiteSettings->logo_path) }}" alt="{{ $websiteSettings->title }}" style="max-width:180px;max-height:64px">@else{{ $websiteSettings->title }}@endif</a><a href="{{ route('news.index', ['locale' => $locale]) }}">{{ ['vi' => 'Tin tức', 'en' => 'News', 'zh' => '新闻', 'ko' => '뉴스'][$locale] }}</a></div></header><main>@yield('content')</main>{!! $websiteSettings->footer_code !!}
</body></html>
