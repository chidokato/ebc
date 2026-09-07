<link rel="icon" href="{{ asset($websiteSettings->favicon_path ?: 'frontend/images/favicon.png') }}">
@if($websiteSettings->seo_description)<meta name="description" content="{{ $websiteSettings->seo_description }}">@endif
@if($websiteSettings->seo_keywords)<meta name="keywords" content="{{ $websiteSettings->seo_keywords }}">@endif
<meta property="og:site_name" content="{{ $websiteSettings->title }}">
<meta property="og:title" content="{{ $seoPageTitle }}">
@if($websiteSettings->seo_description)<meta property="og:description" content="{{ $websiteSettings->seo_description }}">@endif
<meta property="og:url" content="{{ url()->current() }}">
@if($websiteSettings->logo_path)<meta property="og:image" content="{{ asset($websiteSettings->logo_path) }}">@endif
