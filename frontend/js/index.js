document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var header = document.querySelector('.elite-site-header');
    var scrollFrame = null;
    var previousScrollBehavior = '';

    function stopAnchorScroll() {
        if (scrollFrame !== null) {
            cancelAnimationFrame(scrollFrame);
            scrollFrame = null;
            document.documentElement.style.scrollBehavior = previousScrollBehavior;
        }
    }

    window.addEventListener('wheel', stopAnchorScroll, {passive: true});
    window.addEventListener('touchstart', stopAnchorScroll, {passive: true});
    window.addEventListener('keydown', function (event) {
        if (['ArrowUp', 'ArrowDown', 'PageUp', 'PageDown', 'Home', 'End', ' '].includes(event.key)) {
            stopAnchorScroll();
        }
    });

    function updateScrollState() {
        body.classList.toggle('scrollDown', window.scrollY > 12);
    }

    function scrollToSelector(selector) {
        var target = document.getElementById(decodeURIComponent(selector.slice(1)));

        if (!target) {
            return;
        }

        stopAnchorScroll();
        var offset = header ? parseFloat(getComputedStyle(header).getPropertyValue('--menu-compact-height')) || header.offsetHeight : 0;
        var start = window.scrollY;
        var targetTop = target.getBoundingClientRect().top + start - offset - 12;
        var destination = Math.max(0, Math.min(targetTop, document.documentElement.scrollHeight - window.innerHeight));
        var distance = destination - start;
        var duration = Math.min(2200, Math.max(1400, Math.abs(distance) * .6));
        var startedAt = performance.now();
        previousScrollBehavior = document.documentElement.style.scrollBehavior;
        document.documentElement.style.scrollBehavior = 'auto';

        function animateScroll(now) {
            var progress = Math.min((now - startedAt) / duration, 1);
            var eased = (1 - Math.cos(Math.PI * progress)) / 2;
            window.scrollTo(0, start + distance * eased);
            if (progress < 1) {
                scrollFrame = requestAnimationFrame(animateScroll);
            } else {
                stopAnchorScroll();
            }
        }
        scrollFrame = requestAnimationFrame(animateScroll);
    }

    function bindScrollTrigger(selector, targetSelector) {
        document.querySelectorAll(selector).forEach(function (element) {
            element.addEventListener('click', function (event) {
                if (event.defaultPrevented || element.matches('[data-open-booking]')) {
                    return;
                }
                event.preventDefault();
                scrollToSelector(targetSelector);
            });
        });
    }

    function createSwiper(selector, options) {
        var element = typeof selector === 'string' ? document.querySelector(selector) : selector;

        if (!element || element.swiper || typeof Swiper === 'undefined') return;
        var slideCount = element.querySelectorAll('.swiper-wrapper > .swiper-slide').length;
        if (!slideCount) return;
        var instance;
        var currentBreakpoint;
        function refreshSlider() {
            var breakpoint = Object.keys(options.breakpoints || {}).map(Number).sort(function (a, b) { return a - b; })
                .filter(function (width) { return window.innerWidth >= width; }).pop();
            var key = breakpoint === undefined ? 'base' : String(breakpoint);
            if (instance && key === currentBreakpoint) return;
            currentBreakpoint = key;
            var activeIndex = instance ? instance.realIndex : (options.initialSlide || 0);
            if (instance) instance.destroy(true, true);
            var settings = Object.assign({}, options, (options.breakpoints || {})[breakpoint] || {});
            delete settings.breakpoints;
            var fits = slideCount <= Number(settings.slidesPerView || 1);
            settings.loop = Boolean(options.loop) && !fits;
            settings.autoplay = fits ? false : options.autoplay;
            settings.allowTouchMove = !fits;
            settings.grabCursor = Boolean(options.grabCursor) && !fits;
            settings.watchOverflow = true;
            settings.initialSlide = fits ? 0 : Math.min(activeIndex, slideCount - 1);
            if (fits) settings.centeredSlides = false;
            element.classList.toggle('slider-static', fits);
            instance = new Swiper(element, settings);
        }
        refreshSlider();
        window.addEventListener('resize', refreshSlider, {passive: true});
    }

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, {passive: true});

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        var href = anchor.getAttribute('href');

        if (!href || href === '#') {
            return;
        }

        anchor.addEventListener('click', function (event) {
            if (event.defaultPrevented || anchor.matches('[data-open-booking]') || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || !document.getElementById(decodeURIComponent(href.slice(1)))) {
                return;
            }

            event.preventDefault();
            scrollToSelector(href);
        });
    });

    bindScrollTrigger('.elite-nav-cta, .elite-club-cta, [data-consultation]', '#consultation');

    createSwiper('.hero-slider .swiper', {
        spaceBetween: 0,
        effect: 'fade',
        lazy: true,
        loop: true,
        autoplay: {
            delay: 2500,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.hero-slider .swiper-pagination',
            type: 'fraction',
        },
        navigation: {
            nextEl: '.hero-slider .swiper-button-next',
            prevEl: '.hero-slider .swiper-button-prev',
        },
    });

    createSwiper('.ebc-benefit-slider .swiper', {
        loop: true,
        grabCursor: true,
        autoplay: {
            delay: 3200,
            disableOnInteraction: false,
        },
        pagination: {
            el: '.ebc-benefit-slider .swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.ebc-benefit-slider .swiper-button-next',
            prevEl: '.ebc-benefit-slider .swiper-button-prev',
        },
        breakpoints: {
            0: {slidesPerView: 1.08, spaceBetween: 14},
            576: {slidesPerView: 2, spaceBetween: 16},
            992: {slidesPerView: 3, spaceBetween: 18},
            1200: {slidesPerView: 5, spaceBetween: 10},
        },
    });

    [
        {
            selector: '.ballroom-slider .swiper',
            next: '.ballroom-slider .swiper-button-next',
            prev: '.ballroom-slider .swiper-button-prev',
            delay: 4200,
        },
        {
            selector: '.suite-slider .swiper',
            next: '.suite-slider .swiper-button-next',
            prev: '.suite-slider .swiper-button-prev',
            delay: 4600,
        },
        {
            selector: '.flex-suite-slider .swiper',
            next: '.flex-suite-slider .swiper-button-next',
            prev: '.flex-suite-slider .swiper-button-prev',
            delay: 5000,
        },
    ].forEach(function (slider) {
        createSwiper(slider.selector, {
            loop: true,
            autoplay: {
                delay: slider.delay,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: slider.next,
                prevEl: slider.prev,
            },
        });
    });

    document.querySelectorAll('.venue-slider .swiper').forEach(function (element) {
        var slider = element.closest('.venue-slider');
        var slideCount = element.querySelectorAll('.swiper-slide').length;

        createSwiper(element, {
            loop: slideCount > 1,
            autoplay: slideCount > 1 ? {delay: 4200, disableOnInteraction: false} : false,
            navigation: {
                nextEl: slider.querySelector('.swiper-button-next'),
                prevEl: slider.querySelector('.swiper-button-prev'),
            },
        });
    });

    createSwiper('.event-slider', {
        loop: true,
        centeredSlides: true,
        initialSlide: 0,
        slidesPerView: 3,
        spaceBetween: 20,
        speed: 700,
        autoplay: {
            delay: 4000,
            disableOnInteraction: false,
        },
        navigation: {
            nextEl: '.event-slider .swiper-button-next',
            prevEl: '.event-slider .swiper-button-prev',
        },
        breakpoints: {
            0: {slidesPerView: 1.2, spaceBetween: 12},
            768: {slidesPerView: 2, spaceBetween: 16},
            992: {slidesPerView: 3, spaceBetween: 20},
        },
    });

    createSwiper('.amenities-slider', {
        loop: true,
        spaceBetween: 18,
        pagination: {
            el: '.amenities-slider .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            0: {slidesPerView: 1.1, spaceBetween: 14},
            576: {slidesPerView: 2, spaceBetween: 18},
            992: {slidesPerView: 2, spaceBetween: 32},
        },
    });

    createSwiper('.latest-news-slider', {
        loop: true,
        spaceBetween: 28,
        pagination: {
            el: '.latest-news-slider .swiper-pagination',
            clickable: true,
        },
        breakpoints: {
            0: {slidesPerView: 1.1, spaceBetween: 16},
            576: {slidesPerView: 2, spaceBetween: 22},
            992: {slidesPerView: 3, spaceBetween: 28},
        },
    });
});
