import Swiper from 'swiper';
import { EffectFade, Keyboard, Navigation, A11y, Autoplay } from 'swiper/modules';
import { gsap } from 'gsap';

document.addEventListener('DOMContentLoaded', () => {
    const swiperContainer = document.querySelector('.showroom-swiper');
    if (!swiperContainer) return;

    // Tandai bahwa modul Swiper showroom telah dimuat
    window.__showroom_has_autoplay = true;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        gsap.defaults({ duration: 0.01, ease: 'none' });
    }

    let activeTimeline = null;
    let prevSlideElement = null;

    const counterCurrent = document.getElementById('slide-counter-current');
    const counterTotal = document.getElementById('slide-counter-total');
    const ariaLiveRegion = document.getElementById('showroom-aria-live');
    const toggleBtn = document.querySelector('[data-slider-toggle]');

    function updateCounter(swiper) {
        const total = swiper.slides ? swiper.slides.filter(s => !s.classList.contains('swiper-slide-duplicate')).length : 5;
        const current = (swiper.realIndex ?? 0) + 1;

        if (counterCurrent) {
            counterCurrent.textContent = String(current).padStart(2, '0');
        }
        if (counterTotal) {
            counterTotal.textContent = String(total).padStart(2, '0');
        }
        if (ariaLiveRegion) {
            const activeSlide = swiper.slides[swiper.activeIndex];
            const carName = activeSlide?.getAttribute('data-car-name') || `Mobil ${current}`;
            ariaLiveRegion.textContent = `Menampilkan slide ${current} dari ${total}: ${carName}`;
        }
    }

    function runHeroTimeline(swiper) {
        if (activeTimeline) {
            activeTimeline.kill();
        }

        const slide = swiper.slides[swiper.activeIndex];
        if (!slide) return null;

        const car = slide.querySelector('[data-car-img]');
        const bgText = slide.querySelector('[data-bg-text]');
        const specs = slide.querySelectorAll('[data-spec-item]');
        const meta = slide.querySelector('[data-car-meta]');

        if (!car) return null;

        if (prefersReducedMotion) {
            gsap.set([car, bgText, meta, ...specs].filter(Boolean), {
                clearProps: 'all',
                opacity: 1,
                x: 0,
                y: 0,
                scale: 1,
            });
            prevSlideElement = slide;
            return null;
        }

        if (prevSlideElement && prevSlideElement !== slide) {
            const prevCar = prevSlideElement.querySelector('[data-car-img]');
            if (prevCar) {
                gsap.to(prevCar, {
                    x: -120,
                    opacity: 0,
                    scale: 0.95,
                    duration: 0.45,
                    ease: 'power2.in',
                    overwrite: 'auto',
                });
            }
        }
        prevSlideElement = slide;

        const tl = gsap.timeline({ defaults: { overwrite: 'auto' } });

        tl.fromTo(
            car,
            { x: 120, opacity: 0, scale: 0.92, filter: 'drop-shadow(0 15px 15px rgba(15,23,42,.12))' },
            {
                x: 0,
                opacity: 1,
                scale: 1,
                filter: 'drop-shadow(0 25px 25px rgba(15,23,42,.22))',
                duration: 0.75,
                ease: 'back.out(1.1)',
                delay: 0.1,
            }
        )
        .fromTo(bgText, { y: 30, opacity: 0 }, { y: 0, opacity: 1, duration: 0.60, ease: 'power3.out' }, '-=0.60')
        .fromTo(meta, { y: 18, opacity: 0 }, { y: 0, opacity: 1, duration: 0.45, ease: 'power2.out' }, '-=0.45')
        .fromTo(specs, { y: 16, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.06, duration: 0.45, ease: 'power2.out' }, '-=0.30');

        activeTimeline = tl;
        return tl;
    }

    function syncToggleState(isRunning) {
        if (!toggleBtn) return;
        toggleBtn.setAttribute('aria-pressed', isRunning ? 'true' : 'false');
        toggleBtn.setAttribute('aria-label', isRunning ? 'Jeda putar otomatis showroom' : 'Putar otomatis showroom');
        const label = toggleBtn.querySelector('[data-toggle-label]');
        if (label) {
            label.textContent = isRunning ? 'Jeda' : 'Putar';
        }
        const pauseIcon = toggleBtn.querySelector('[data-icon-pause]');
        const playIcon = toggleBtn.querySelector('[data-icon-play]');
        if (pauseIcon && playIcon) {
            pauseIcon.classList.toggle('hidden', !isRunning);
            playIcon.classList.toggle('hidden', isRunning);
        }
    }

    const swiper = new Swiper(swiperContainer, {
        modules: [EffectFade, Keyboard, Navigation, A11y, Autoplay],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: prefersReducedMotion ? 0 : 750,
        autoplay: prefersReducedMotion
            ? false
            : {
                delay: 6500,
                disableOnInteraction: true,
                pauseOnMouseEnter: true,
            },
        allowTouchMove: true,
        grabCursor: true,
        keyboard: { enabled: true },
        loop: true,
        preloadImages: false,
        navigation: {
            nextEl: '[data-slide-next]',
            prevEl: '[data-slide-prev]',
        },
        a11y: {
            enabled: true,
            prevSlideMessage: 'Mobil sebelumnya',
            nextSlideMessage: 'Mobil berikutnya',
        },
        on: {
            init(sw) {
                window.__showroomSwiper = sw;
                swiperContainer.swiper = sw;
                updateCounter(sw);
                runHeroTimeline(sw);
                syncToggleState(Boolean(sw.autoplay && sw.autoplay.running));
            },
            slideChangeTransitionStart(sw) {
                updateCounter(sw);
                runHeroTimeline(sw);
            },
            autoplayStop() {
                syncToggleState(false);
            },
            autoplayStart() {
                syncToggleState(true);
            },
        },
    });

    if (toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            if (!swiper.autoplay || prefersReducedMotion) return;
            if (swiper.autoplay.running) {
                swiper.autoplay.stop();
                syncToggleState(false);
            } else {
                swiper.autoplay.start();
                syncToggleState(true);
            }
        });
    }

    window.__showroomSwiper = swiper;
    swiperContainer.swiper = swiper;

    window.addEventListener('beforeunload', () => {
        if (activeTimeline) activeTimeline.kill();
        if (swiper) swiper.destroy(true, true);
    });
});
