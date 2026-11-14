import Swiper from 'swiper';
import { EffectFade, Keyboard, Navigation, A11y, Autoplay } from 'swiper/modules';
import { gsap } from 'gsap';

document.addEventListener('DOMContentLoaded', () => {
    const swiperContainer = document.querySelector('.showroom-swiper');
    if (!swiperContainer) return;

    // Tandai bahwa native script Showroom telah aktif
    window.__showroom_has_autoplay = true;

    // Reduced motion preference (Design §3.4)
    const motionQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
    let prefersReducedMotion = motionQuery.matches;

    if (prefersReducedMotion) {
        gsap.defaults({ duration: 0.01, ease: 'none' });
    }

    let activeTimeline = null;
    let prevSlideElement = null;
    let isPaused = prefersReducedMotion; // Reduced motion pauses autoplay by default

    window.__showroom_autoplay_paused = isPaused;

    const counterCurrent = document.getElementById('slide-counter-current');
    const counterTotal = document.getElementById('slide-counter-total');
    const ariaLiveRegion = document.getElementById('showroom-aria-live');
    const pausePlayBtn = document.querySelector('[data-slide-pause-play]');
    const iconPause = pausePlayBtn?.querySelector('[data-icon-pause]');
    const iconPlay = pausePlayBtn?.querySelector('[data-icon-play]');

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

        // If reduced motion is requested, set values immediately without tweening
        if (prefersReducedMotion) {
            if (prevSlideElement && prevSlideElement !== slide) {
                const prevCar = prevSlideElement.querySelector('[data-car-img]');
                if (prevCar) {
                    gsap.set(prevCar, { opacity: 0, x: 0, scale: 1 });
                }
            }
            prevSlideElement = slide;

            gsap.set(car, {
                x: 0,
                opacity: 1,
                scale: 1,
                filter: 'drop-shadow(0 25px 25px rgba(0,0,0,.25))',
            });
            if (bgText) gsap.set(bgText, { y: 0, opacity: 1 });
            if (meta) gsap.set(meta, { y: 0, opacity: 1 });
            if (specs && specs.length) gsap.set(specs, { y: 0, opacity: 1 });

            return null;
        }

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
        speed: prefersReducedMotion ? 0 : 800,
        autoplay: {
            delay: 2000,
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

    // Autoplay Pause / Play Control
    function setAutoplayState(paused, announce = true) {
        isPaused = paused;
        window.__showroom_autoplay_paused = paused;

        if (paused) {
            if (swiper.autoplay && typeof swiper.autoplay.stop === 'function') {
                swiper.autoplay.stop();
            }
            if (pausePlayBtn) {
                pausePlayBtn.setAttribute('aria-pressed', 'true');
                pausePlayBtn.setAttribute('aria-label', 'Mulai putar otomatis carousel');
                pausePlayBtn.setAttribute('title', 'Mulai putar otomatis carousel');
            }
            if (iconPause) iconPause.classList.add('hidden');
            if (iconPlay) iconPlay.classList.remove('hidden');
            if (ariaLiveRegion && announce) {
                ariaLiveRegion.textContent = 'Putar otomatis carousel dijeda';
            }
        } else {
            if (swiper.autoplay && typeof swiper.autoplay.start === 'function') {
                swiper.autoplay.start();
            }
            if (pausePlayBtn) {
                pausePlayBtn.setAttribute('aria-pressed', 'false');
                pausePlayBtn.setAttribute('aria-label', 'Jeda putar otomatis carousel');
                pausePlayBtn.setAttribute('title', 'Jeda putar otomatis carousel');
            }
            if (iconPause) iconPause.classList.remove('hidden');
            if (iconPlay) iconPlay.classList.add('hidden');
            if (ariaLiveRegion && announce) {
                ariaLiveRegion.textContent = 'Putar otomatis carousel dilanjutkan';
            }
        }
    }

    if (pausePlayBtn) {
        // Initial button state
        setAutoplayState(isPaused, false);

        pausePlayBtn.addEventListener('click', (e) => {
            e.preventDefault();
            setAutoplayState(!isPaused, true);
        });
    }

    // Dynamic OS reduced-motion listener
    const handleMotionChange = (e) => {
        prefersReducedMotion = e.matches;
        if (prefersReducedMotion) {
            gsap.defaults({ duration: 0.01, ease: 'none' });
            swiper.params.speed = 0;
            setAutoplayState(true, true);
        } else {
            gsap.defaults({ duration: 0.5, ease: 'power2.out' });
            swiper.params.speed = 800;
        }
    };

    if (motionQuery.addEventListener) {
        motionQuery.addEventListener('change', handleMotionChange);
    } else if (motionQuery.addListener) {
        motionQuery.addListener(handleMotionChange);
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        if (activeTimeline) activeTimeline.kill();
        if (swiper) swiper.destroy(true, true);
    });
});
