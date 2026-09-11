<link rel="icon" href="{{ asset($websiteSettings->favicon_path ?: 'frontend/images/favicon.png') }}">
@if($websiteSettings->seo_description)<meta name="description" content="{{ $websiteSettings->seo_description }}">@endif
@if($websiteSettings->seo_keywords)<meta name="keywords" content="{{ $websiteSettings->seo_keywords }}">@endif
<meta property="og:site_name" content="{{ $websiteSettings->title }}">
<meta property="og:title" content="{{ $seoPageTitle }}">
@if($websiteSettings->seo_description)<meta property="og:description" content="{{ $websiteSettings->seo_description }}">@endif
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:image" content="{{ $websiteSettings->social_image_url }}">
<meta property="og:image:alt" content="{{ $websiteSettings->title }}">
