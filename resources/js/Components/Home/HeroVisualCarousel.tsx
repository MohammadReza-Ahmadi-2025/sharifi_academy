import { useCallback, useEffect, useRef, useState } from 'react';

type Slide = {
    src: string;
    alt: string;
    title: string;
    subtitle: string;
};

const slides: Slide[] = [
    {
        src: '/coursetest.png',
        alt: 'آموزش جامع کتیا',
        title: 'آموزش پروژه‌محور با مدرس همراه',
        subtitle: 'CATIA، شبیه‌سازی و مسیر شغلی مهندسی',
    },
    {
        src: '/coursetest2.png',
        alt: 'آموزش جامع سالیدورکس',
        title: 'آموزش جامع سالیدورکس',
        subtitle: 'طراحی، مدل‌سازی و شبیه‌سازی در محیط صنعتی',
    },
];

const AUTOPLAY_MS = 5500;
const SHUFFLE_MS = 1000;
const SHUFFLE_MID_MS = 480;
const SWIPE_THRESHOLD = 48;

export function HeroVisualCarousel() {
    const [frontIndex, setFrontIndex] = useState(0);
    const [shufflePhase, setShufflePhase] = useState<'idle' | 'active' | 'behind'>('idle');
    const [outgoingIndex, setOutgoingIndex] = useState<number | null>(null);
    const [incomingIndex, setIncomingIndex] = useState<number | null>(null);

    const frontIndexRef = useRef(0);
    const busyRef = useRef(false);
    const pausedRef = useRef(false);
    const touchStartX = useRef<number | null>(null);
    const shuffleTimersRef = useRef<number[]>([]);

    frontIndexRef.current = frontIndex;

    const clearShuffleTimers = () => {
        shuffleTimersRef.current.forEach((id) => window.clearTimeout(id));
        shuffleTimersRef.current = [];
    };

    const runShuffle = useCallback((nextFront: number) => {
        if (busyRef.current || slides.length < 2) {
            return;
        }

        const leaving = frontIndexRef.current;
        if (leaving === nextFront) {
            return;
        }

        busyRef.current = true;
        clearShuffleTimers();
        setOutgoingIndex(leaving);
        setIncomingIndex(nextFront);
        setShufflePhase('active');

        const midTimer = window.setTimeout(() => {
            frontIndexRef.current = nextFront;
            setFrontIndex(nextFront);
            setShufflePhase('behind');
        }, SHUFFLE_MID_MS);

        const endTimer = window.setTimeout(() => {
            setShufflePhase('idle');
            setOutgoingIndex(null);
            setIncomingIndex(null);
            busyRef.current = false;
        }, SHUFFLE_MS);

        shuffleTimersRef.current.push(midTimer, endTimer);
    }, []);

    const advance = useCallback(() => {
        const nextFront = frontIndexRef.current === 0 ? 1 : 0;
        runShuffle(nextFront);
    }, [runShuffle]);

    const goTo = useCallback(
        (targetIndex: number) => {
            runShuffle(targetIndex);
        },
        [runShuffle],
    );

    useEffect(() => {
        const interval = window.setInterval(() => {
            if (pausedRef.current || busyRef.current) {
                return;
            }
            advance();
        }, AUTOPLAY_MS);

        return () => window.clearInterval(interval);
    }, [advance]);

    useEffect(() => {
        return () => clearShuffleTimers();
    }, []);

    const onTouchStart = (clientX: number) => {
        touchStartX.current = clientX;
        pausedRef.current = true;
    };

    const onTouchEnd = (clientX: number) => {
        if (touchStartX.current === null) {
            return;
        }

        const delta = clientX - touchStartX.current;
        if (Math.abs(delta) > SWIPE_THRESHOLD) {
            advance();
        }

        touchStartX.current = null;
        window.setTimeout(() => {
            pausedRef.current = false;
        }, AUTOPLAY_MS);
    };

    const getLayerState = (slideIndex: number) => {
        const isFront = slideIndex === frontIndex;

        if (shufflePhase === 'idle') {
            return {
                className: isFront ? 'hero-card-layer--stack-0' : 'hero-card-layer--stack-1',
                zIndex: isFront ? 20 : 10,
                showCaption: isFront,
            };
        }

        if (slideIndex === outgoingIndex) {
            const behind = shufflePhase === 'behind';
            return {
                className: behind ? 'hero-card-layer--to-back' : 'hero-card-layer--shuffle-out',
                zIndex: behind ? 8 : 30,
                showCaption: shufflePhase === 'active',
            };
        }

        if (slideIndex === incomingIndex) {
            return {
                className: 'hero-card-layer--promote-in',
                zIndex: shufflePhase === 'behind' ? 25 : 18,
                showCaption: shufflePhase === 'behind',
            };
        }

        return {
            className: 'hero-card-layer--stack-1',
            zIndex: 5,
            showCaption: false,
        };
    };

    return (
        <div className="relative mx-auto max-w-lg touch-pan-y lg:max-w-none">
            <div
                className="relative aspect-4/3 w-full"
                onTouchStart={(e) => onTouchStart(e.touches[0].clientX)}
                onTouchEnd={(e) => onTouchEnd(e.changedTouches[0].clientX)}
            >
                {slides.map((slide, slideIndex) => {
                    const layer = getLayerState(slideIndex);

                    return (
                        <article
                            key={slide.src}
                            className={`hero-card-layer absolute inset-0 overflow-hidden rounded-3xl border-4 border-neutral-white bg-primary shadow-2xl ${layer.className}`}
                            style={{ zIndex: layer.zIndex }}
                        >
                            <img
                                src={slide.src}
                                alt={slide.alt}
                                className="size-full object-cover"
                                draggable={false}
                            />

                            {layer.showCaption && (
                                <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-linear-to-t from-neutral-black/80 to-transparent p-4 pt-16">
                                    <p className="text-sm font-semibold text-neutral-white">{slide.title}</p>
                                    <p className="text-xs text-neutral-white/80">{slide.subtitle}</p>
                                </div>
                            )}
                        </article>
                    );
                })}
            </div>

            <div className="absolute inset-x-0 -bottom-3 z-40 flex justify-center gap-2">
                {slides.map((slide, i) => (
                    <button
                        key={slide.src}
                        type="button"
                        className={`h-2 rounded-full transition-all duration-300 ${
                            i === frontIndex ? 'w-6 bg-primary' : 'w-2 bg-primary/35 hover:bg-primary/55'
                        }`}
                        aria-label={`اسلاید ${i + 1}`}
                        onClick={() => goTo(i)}
                        disabled={shufflePhase !== 'idle'}
                    />
                ))}
            </div>
        </div>
    );
}
