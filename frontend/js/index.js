document.addEventListener('DOMContentLoaded', function () {
    var body = document.body;
    var header = document.querySelector('.elite-site-header');

    function updateScrollState() {
        body.classList.toggle('scrollDown', window.scrollY > 12);
    }

    function scrollToSelector(selector) {
        var target = document.querySelector(selector);

        if (!target) {
            return;
        }

        var offset = header ? header.offsetHeight : 0;
        var targetTop = target.getBoundingClientRect().top + window.scrollY - offset;

        window.scrollTo({
            top: Math.max(targetTop, 0),
            behavior: 'smooth',
        });
    }

    function bindScrollTrigger(selector, targetSelector) {
        document.querySelectorAll(selector).forEach(function (element) {
            element.addEventListener('click', function (event) {
                event.preventDefault();
                scrollToSelector(targetSelector);
            });
        });
    }

    function createSwiper(selector, options) {
        var element = document.querySelector(selector);

        if (element && !element.swiper && typeof Swiper !== 'undefined') {
            new Swiper(element, options);
        }
    }

    updateScrollState();
    window.addEventListener('scroll', updateScrollState, {passive: true});

    document.querySelectorAll('a[href^="#"]').forEach(function (anchor) {
        var href = anchor.getAttribute('href');

        if (!href || href === '#') {
            return;
        }

        anchor.addEventListener('click', function (event) {
            if (!document.querySelector(href)) {
                return;
            }

            event.preventDefault();
            scrollToSelector(href);
        });
    });

    bindScrollTrigger('.hero-consult-button, .ballroom-booking, .elite-club-cta', '#consultation');

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

        new Swiper(element, {
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
