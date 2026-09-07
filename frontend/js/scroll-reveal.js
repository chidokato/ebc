document.addEventListener('DOMContentLoaded', function () {
    if (!('IntersectionObserver' in window)) return;

    // Animate blocks around sliders, leaving slide transforms and fixed backgrounds intact.
    var blocks = document.querySelectorAll([
        '.ebc-event-copy', '.ebc-benefit-slider', '.ballroom-heading', '.venue-layout > div',
        '.service-experience-heading', '.event-slider', '.sec-services-amenities > .container > h2',
        '.amenities-slider', '.elite-club-content', '.consultation-intro', '.consultation-form',
        '.latest-news-heading', '.latest-news-slider', '.elite-footer-main', '.elite-footer-bottom'
    ].join(', '));

    var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
            if (entry.isIntersecting) reveal(entry.target);
        });
    }, {threshold: 0, rootMargin: '0px 0px -32px 0px'});

    function reveal(block) {
        block.classList.add('scroll-reveal-visible');
        observer.unobserve(block);
    }

    blocks.forEach(function (block) {
        // Keep content already visible (including restored scroll positions) immediately readable.
        if (block.getBoundingClientRect().top < window.innerHeight - 32) return;
        var direction = 'up';
        if (block.matches('.ebc-event-copy, .elite-club-content, .consultation-intro')) direction = 'left';
        if (block.matches('.consultation-form')) direction = 'right';
        if (block.parentElement.matches('.venue-layout')) {
            direction = block.classList.contains('order-lg-2') ? 'right'
                : block.classList.contains('order-lg-1') ? 'left'
                : block === block.parentElement.firstElementChild ? 'left' : 'right';
        }
        block.classList.add('scroll-reveal-' + direction);
        block.classList.add('scroll-reveal');
        observer.observe(block);
        block.addEventListener('focusin', function () { reveal(block); }, {once: true});
    });
});
