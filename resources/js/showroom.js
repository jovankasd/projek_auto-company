import Swiper from 'swiper';
import { EffectFade, Keyboard, Navigation, A11y, Autoplay } from 'swiper/modules';
import { gsap } from 'gsap';

document.addEventListener('DOMContentLoaded', () => {
    const swiperContainer = document.querySelector('.showroom-swiper');
    if (!swiperContainer) return;

    // Tandai bahwa native autoplay Swiper telah aktif
    window.__showroom_has_autoplay = true;

    // Reduced motion fallback (Design §3.4)
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (prefersReducedMotion) {
        gsap.defaults({ duration: 0.01, ease: 'none' });
    }

    let activeTimeline = null;
    let prevSlideElement = null;

    const counterCurrent = document.getElementById('slide-counter-current');
    const counterTotal = document.getElementById('slide-counter-total');
    const ariaLiveRegion = document.getElementById('showroom-aria-live');

    function updateCounter(swiper) {
        // Real index accounting for loop mode
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
        // Kill existing timeline to avoid overlaps / memory leaks (Design §3.5)
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

        // Animate previous car exit
        if (prevSlideElement && prevSlideElement !== slide) {
            const prevCar = prevSlideElement.querySelector('[data-car-img]');
            if (prevCar) {
                gsap.to(prevCar, {
                    x: -120,
                    opacity: 0,
                    scale: 0.95,
                    duration: 0.5,
                    ease: 'power2.in',
                    overwrite: 'auto',
                });
            }
        }
        prevSlideElement = slide;

        const tl = gsap.timeline({ defaults: { overwrite: 'auto' } });

        tl.fromTo(
            car,
            { x: 150, opacity: 0, scale: 0.90, filter: 'drop-shadow(0 15px 15px rgba(0,0,0,.15))' },
            {
                x: 0,
                opacity: 1,
                scale: 1,
                filter: 'drop-shadow(0 25px 25px rgba(0,0,0,.25))',
                duration: 0.85,
                ease: 'back.out(1.2)',
                delay: 0.15,
            }
        )
        .fromTo(bgText, { y: 40, opacity: 0 }, { y: 0, opacity: 1, duration: 0.70, ease: 'power3.out' }, '-=0.70')
        .fromTo(meta, { y: 24, opacity: 0 }, { y: 0, opacity: 1, duration: 0.50, ease: 'power2.out' }, '-=0.50')
        .fromTo(specs, { y: 20, opacity: 0 }, { y: 0, opacity: 1, stagger: 0.08, duration: 0.50, ease: 'power2.out' }, '-=0.35');

        activeTimeline = tl;
        return tl;
    }

    const swiper = new Swiper(swiperContainer, {
        modules: [EffectFade, Keyboard, Navigation, A11y, Autoplay],
        effect: 'fade',
        fadeEffect: { crossFade: true },
        speed: 800,
        autoplay: {
            delay: 2000, // Otomatis bergeser setiap 2 detik
            disableOnInteraction: false,
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
            },
            slideChangeTransitionStart(sw) {
                updateCounter(sw);
                runHeroTimeline(sw);
            },
        },
    });

    window.__showroomSwiper = swiper;
    swiperContainer.swiper = swiper;

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (activeTimeline) activeTimeline.kill();
        if (swiper) swiper.destroy(true, true);
    });
});
