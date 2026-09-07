<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
<base href="{{ asset('frontend') }}/">
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
<meta charset="utf-8">
<title>{{ $websiteSettings->seo_title ?: $websiteSettings->title }}</title>
<meta name="generator" content="Bootply" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
	
@include('partials.website-meta', ['seoPageTitle' => $websiteSettings->seo_title ?: $websiteSettings->title])

<!------------------- CSS ------------------->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/swiper-bundle.min.css" rel="stylesheet">
<link href="css/fonts.css" rel="stylesheet">
<link href="css/common.css" rel="stylesheet">
<link href="css/index.css?v={{ filemtime(base_path('frontend/css/index.css')) }}" rel="stylesheet">

<!------------------- FONT ------------------->
{!! $websiteSettings->head_code !!}
</head>

<body>

<!------------------- NAVIGATOR ------------------->
<header class="elite-site-header fix-top">
	<nav class="elite-site-nav" aria-label="Điều hướng chính">
		<div class="container elite-nav-content">
			<div class="elite-nav-left">
				<details class="elite-language"><summary><svg class="elite-language-icon" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="8.5"></circle><path d="M3.5 12h17M12 3.5c2.2 2.4 3.3 5.2 3.3 8.5S14.2 18.1 12 20.5c-2.2-2.4-3.3-5.2-3.3-8.5S9.8 5.9 12 3.5Z"></path></svg>{{ $languages[$locale] }}</summary>@foreach ($languages as $code => $label) @if ($code !== $locale)<a href="{{ route('home', ['locale' => $code]) }}">{{ $label }}</a>@endif @endforeach</details>
				@foreach ($headerMenus->take(2) as $menu)<a href="{{ $menu->url }}">{{ $menu->label }}</a>@endforeach
			</div>
			<a class="elite-nav-logo" href="{{ route('home', ['locale' => $locale]) }}" aria-label="{{ $websiteSettings->title }}">
				<img class="elite-nav-logo-white" src="{{ asset($websiteSettings->white_logo_path ?: 'frontend/img/logo-trang.png') }}" alt="{{ $websiteSettings->title }}">
				<img class="elite-nav-logo-color" src="{{ asset($websiteSettings->logo_path ?: 'frontend/img/logo.png') }}" alt="{{ $websiteSettings->title }}">
			</a>
			<div class="elite-nav-right">@foreach ($headerMenus->slice(2) as $menu)<a class="{{ $loop->last ? 'elite-nav-cta' : '' }}" href="{{ $menu->url }}">{{ $menu->label }}</a>@endforeach</div>
		</div>
	</nav>
</header>
<!------------------- END NAVIGATOR ------------------->

<!------------------- HERO ------------------->
<section class="sec-hero">
	<div class="hero-slider">
		<div class="swiper">
			<div class="swiper-wrapper">
				@forelse ($heroSliders as $slider)
					<div class="swiper-slide">
						<span style="background-image:url('{{ asset($slider->image_path) }}')" class="w-100 thumb"></span>
						<div class="hero-content">
							<div class="hero-text">
								<h1>{{ $slider->title }}</h1>
								@if ($slider->description)<p>{{ $slider->description }}</p>@endif
								@if ($slider->button_label)<a class="hero-consult-button d-inline-block text-decoration-none" href="{{ $slider->button_url ?: '#consultation' }}">{{ $slider->button_label }}</a>@endif
							</div>
						</div>
					</div>
				@empty
					
				@endforelse
			</div>
			<div class="swiper-navigator">
				<div class="swiper-pagination"></div>
				<div class="swiper-navigator-btn">
					<div class="swiper-button-prev"><i class="icon-prev-thin"></i></div>
					<div class="swiper-button-next"><i class="icon-next-thin"></i></div>
				</div>
			</div>
		</div>
	</div>
	
</section>

<!------------------- EBC BENEFITS ------------------->
@php($benefitIcons = ['icon-location', 'icon-stretch', 'icon-grid', 'icon-star-fill', 'icon-building-filled'])
@if($aboutSection)
<section class="sec-ebc-benefits" id="about">
	<div class="ebc-benefits-frame" @if($aboutSection?->images->isNotEmpty() || $aboutSection?->image_path) style="--about-background:url('{{ asset($aboutSection->images->first()?->path ?: $aboutSection->image_path) }}')" @endif>
		<div class="container">
			<div class="ebc-event-copy">
				<img class="ebc-event-logo" src="{{ asset($aboutSection->icon_path ?: 'frontend/img/logo.png') }}" alt="Elite Business Center">
				@if($aboutSection?->content)
					<div class="ebc-event-rich-content">{!! $aboutSection->content !!}</div>
				@endif
			</div>
			<div class="ebc-benefit-slider">
				<div class="swiper">
					<div class="swiper-wrapper">
						@if($aboutSection?->children->isNotEmpty())
							@foreach($aboutSection->children as $benefit)
								<div class="swiper-slide">
									<article class="ebc-benefit-card">
										@if($benefit->icon_path)<img class="ebc-benefit-icon" src="{{ asset($benefit->icon_path) }}" alt="">@endif
										<h2 class="">{{ $benefit->title }}</h2>
										<span class="ebc-card-mark" aria-hidden="true"></span>
										<div class="ebc-benefit-card-content">{!! $benefit->content !!}</div>
									</article>
								</div>
							@endforeach
						@endif
					</div>
					<div class="swiper-pagination"></div>
				</div>
				<button class="swiper-button-prev" type="button" aria-label="Thẻ trước"><i class="icon-prev-thin"></i></button>
				<button class="swiper-button-next" type="button" aria-label="Thẻ tiếp theo"><i class="icon-next-thin"></i></button>
			</div>
		</div>
	</div>
</section>
@endif

@if($ballroomSection)
<!------------------- BALLROOM FROM DATABASE ------------------->
<section class="sec-ballroom" id="ballroom">
	<div class="container">
		<div class="ballroom-heading text-center">
			<h2 class="font-wasted-vindey">{{ $ballroomSection->title }}</h2>
			<div class="venue-content">{!! $ballroomSection->content !!}</div>
		</div>
		<div class="venue-list">
			@foreach($ballroomSection->children as $venue)
				<article class="row g-4 align-items-center venue-layout">
					<div class="col-lg-6 {{ $loop->even ? 'order-lg-2' : '' }}">
						<div class="venue-slider">
							@if($venue->images->isNotEmpty() || $venue->image_path)
								<div class="swiper"><div class="swiper-wrapper">
									@forelse($venue->images as $image)<div class="swiper-slide"><span class="venue-media" style="background-image:url('{{ asset($image->path) }}')"></span></div>@empty<div class="swiper-slide"><span class="venue-media" style="background-image:url('{{ asset($venue->image_path) }}')"></span></div>@endforelse
								</div></div>
								@if($venue->images->count() > 1)<button class="swiper-button-prev" type="button" aria-label="Ảnh trước"><i class="icon-prev-thin"></i></button><button class="swiper-button-next" type="button" aria-label="Ảnh tiếp theo"><i class="icon-next-thin"></i></button>@endif
							@else
								<div class="venue-media venue-media--empty"></div>
							@endif
						</div>
					</div>
					<div class="col-lg-6 {{ $loop->even ? 'order-lg-1' : '' }}">
						<div class="venue-info">
							<h3 class="font-wasted-vindey">{{ $venue->title }}</h3>
							<div class="venue-content">{!! $venue->content !!}</div>
							@include('partials.section-quick-items', ['quickSection' => $venue])
							<a class="ballroom-booking d-inline-block text-decoration-none mt-3" href="{{ $venue->link_url ?: route('home', ['locale' => $locale]) . '#consultation' }}">{{ $venue->link_label ?: ($translations['Đặt lịch hẹn'] ?? 'Đặt lịch hẹn') }}</a>
						</div>
					</div>
				</article>
			@endforeach
		</div>
	</div>
</section>
@endif

@if($servicesSection)
<!------------------- SERVICE EXPERIENCE FROM DATABASE ------------------->
<section class="sec-service-experience" id="menu">
	<div class="container">
		<div class="service-experience-heading text-center">
			<h2 class="font-wasted-vindey">{{ $servicesSection->title }}</h2>
			<div class="service-description">{!! $servicesSection->content !!}</div>
		</div>
		<div class="event-slider swiper">
			<div class="swiper-wrapper">
				@foreach($servicesSection->children as $service)
					@php($serviceImage = $service->images->first()?->path ?: $service->image_path)
					<div class="swiper-slide"><article class="event-card">
						<div class="event-card-image {{ $serviceImage ? '' : 'event-card-image--empty' }}" @if($serviceImage) style="background-image:url('{{ asset($serviceImage) }}')" @endif></div>
						<h3>{{ $service->title }}</h3>
						<div class="event-card-content">{!! $service->content !!}</div>
					</article></div>
				@endforeach
			</div>
			<button class="swiper-button-prev" type="button" aria-label="Dịch vụ trước"></button>
			<button class="swiper-button-next" type="button" aria-label="Dịch vụ tiếp theo"></button>
		</div>
	</div>
</section>
@endif


@if($amenitiesSection)
<!------------------- SERVICES & AMENITIES FROM DATABASE ------------------->
<section class="sec-services-amenities">
	<div class="container">
		<h2 class="font-wasted-vindey">{{ $amenitiesSection->title }}</h2>
		<div class="swiper amenities-slider">
			<div class="swiper-wrapper">
				@foreach($amenitiesSection->children as $amenity)
					@php($amenityImage = $amenity->images->first()?->path ?: $amenity->image_path)
					<div class="swiper-slide"><article class="amenity-card {{ $amenityImage ? '' : 'amenity-card--empty' }}" @if($amenityImage) style="background-image:url('{{ asset($amenityImage) }}')" @endif><div class="amenity-card-content"><h3>{{ $amenity->title }}</h3>@if($amenity->sub_title)<div class="amenity-sub-title">{{ $amenity->sub_title }}</div>@endif<div class="amenity-description">{!! $amenity->content !!}</div></div></article></div>
				@endforeach
			</div>
			<div class="swiper-pagination"></div>
		</div>
	</div>
</section>
@endif

@if($eliteClubSection)
<!------------------- ELITE CLUB FROM DATABASE ------------------->
@php($eliteClubImage = $eliteClubSection->images->first()?->path ?: $eliteClubSection->image_path)
<section id="clbhoivien" class="sec-elite-club" @if($eliteClubImage) style="--elite-club-background:url('{{ asset($eliteClubImage) }}')" @endif>
	<div class="container">
		<div class="elite-club-content">
			<h2 class="font-wasted-vindey">{{ $eliteClubSection->title }}</h2>
			@if($eliteClubSection->sub_title)<span>{{ $eliteClubSection->sub_title }}</span>@endif
			<div class="elite-club-description">{!! $eliteClubSection->content !!}</div>
			@if($eliteClubSection->link_url && $eliteClubSection->link_label)<a class="elite-club-cta" href="{{ $eliteClubSection->link_url }}">{{ $eliteClubSection->link_label }}</a>@endif
		</div>
	</div>
</section>
@endif

@if($supportSection)
<!------------------- CONSULTATION FROM DATABASE ------------------->
@php($supportImage = $supportSection->images->first()?->path ?: $supportSection->image_path)
<section class="sec-consultation" id="consultation" @if($supportImage) style="--consultation-background:url('{{ asset($supportImage) }}')" @endif>
	<div class="container">
		<div class="consultation-layout">
			<div class="consultation-intro">
				<h2 class="font-wasted-vindey">{{ $supportSection->title }}</h2>
				<div class="consultation-description">{!! $supportSection->content !!}</div>
			</div>
			<form class="consultation-form">
				<h3>Thông Tin Liên Hệ</h3>
				<label>Dịch vụ yêu cầu <sup>*</sup><select name="service" required><option value="">Chọn dịch vụ</option><option>Đặt lịch tham quan</option><option>Tổ chức sự kiện</option><option>Đăng ký hội viên</option></select></label>
				<label>Họ và tên <sup>*</sup><input type="text" name="name" placeholder="Nhập họ và tên của bạn" required></label>
				<label>Email <sup>*</sup><input type="email" name="email" placeholder="example@email.com" required></label>
				<label>Số điện thoại <sup>*</sup><input type="tel" name="phone" placeholder="0123456789" required></label>
				<label>Chi tiết yêu cầu <sup>*</sup><textarea name="message" placeholder="Mô tả chi tiết về sự kiện và yêu cầu của bạn..." required></textarea></label>
				<button type="submit">Liên hệ</button>
			</form>
		</div>
	</div>
</section>
@endif

@if($newsArticles->isNotEmpty())
<section class="sec-latest-news" id="news">
    <div class="container">
        <div class="latest-news-heading"><span>Latest news</span><h2 class="font-wasted-vindey">{{ ['vi' => 'Tin Tức Mới Nhất', 'en' => 'Latest News', 'zh' => '最新新闻', 'ko' => '최신 뉴스'][$locale] }}</h2></div>
        <div class="swiper latest-news-slider"><div class="swiper-wrapper">
        @foreach ($newsArticles as $newsArticle)
            <div class="swiper-slide"><article class="latest-news-card">
                <a class="latest-news-image" href="{{ route('news.show', ['locale' => $locale, 'news' => $newsArticle->id]) }}" style="background-image:url('{{ asset($newsArticle->image_path ?: 'frontend/images/ebc-event-hall.png') }}')" aria-label="{{ $newsArticle->title }}"></a>
                <div class="latest-news-body"><time datetime="{{ $newsArticle->published_at->toDateString() }}"><i class="icon-clock"></i> {{ $newsArticle->published_at->format('d.m.Y') }}</time><h3>{{ $newsArticle->title }}</h3><a class="latest-news-link" href="{{ route('news.show', ['locale' => $locale, 'news' => $newsArticle->id]) }}" aria-label="{{ $newsArticle->title }}"><i class="icon-next-thin"></i></a></div>
            </article></div>
        @endforeach
        </div><div class="swiper-pagination"></div></div>
        <a href="{{ route('news.index', ['locale' => $locale]) }}">{{ ['vi' => 'Xem tất cả tin tức', 'en' => 'All news', 'zh' => '所有新闻', 'ko' => '모든 뉴스'][$locale] }}</a>
    </div>
</section>
@endif

<!------------------- FOOTER ------------------->
<footer class="elite-footer">
	<div class="container">
		<div class="elite-footer-main">
			<div class="elite-footer-brand">
				<img src="{{ asset($websiteSettings->white_logo_path ?: $websiteSettings->logo_path ?: 'frontend/img/logo-trang.png') }}" alt="{{ $websiteSettings->title }}">
			</div>
			<div class="elite-footer-links">
				<h3>Về Chúng Tôi</h3>
				<a href="#">Hệ thống sảnh</a><a href="#">Dịch vụ</a><a href="#">Thực đơn tiệc</a>
			</div>
			<div class="elite-footer-contact">
				<h3>Liên Hệ</h3>
				<p>Tầng 2, tòa Capital Elite, 18 Phạm Hùng, phường Từ Liêm, Hà Nội</p>
				<a class="elite-footer-phone" href="tel:0986003663">0986 003 663</a>
				<div class="elite-footer-social"><a href="#" aria-label="Facebook"><i class="icon-facebook"></i></a><a href="#" aria-label="YouTube"><i class="icon-youtube"></i></a></div>
			</div>
		</div>
		<div class="elite-footer-bottom"><span>Copyright © 2026 Elite Business Center</span><div><a href="#">Chính sách Cookie</a><a href="#">Điều khoản &amp; Điều kiện</a><a href="#">Chính sách quyền riêng tư</a></div></div>
	</div>
</footer>
<!------------------- END: FOOTER ------------------->

@if(auth()->user()?->is_admin)
<a class="admin-shortcut" href="{{ route('admin.dashboard') }}">Vào quản trị</a>
@endif

<!------------------- JS core------------------->
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/swiper-bundle.min.js"></script>
<script src="js/index.js?v={{ filemtime(base_path('frontend/js/index.js')) }}"></script>
<script>
    // Keep the existing landing markup intact while applying the active locale's copy.
    (() => {
        const translations = @json($translations, JSON_UNESCAPED_UNICODE);

        if (!Object.keys(translations).length) return;

        const translate = (value) => translations[value.trim()] || value;
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        const textNodes = [];
        let node;

        while ((node = walker.nextNode())) textNodes.push(node);
        textNodes.forEach((textNode) => {
            if (textNode.parentElement.closest('.elite-language')) return;

            const original = textNode.nodeValue;
            const translated = translate(original);
            if (translated !== original) {
                textNode.nodeValue = original.replace(original.trim(), translated);
            }
        });

        document.querySelectorAll('[placeholder], [aria-label]').forEach((element) => {
            ['placeholder', 'aria-label'].forEach((attribute) => {
                const original = element.getAttribute(attribute);
                if (original) element.setAttribute(attribute, translate(original));
            });
        });
    })();
</script>

{!! $websiteSettings->footer_code !!}
</body>
</html>
