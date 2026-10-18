import { HomeCatalogCard } from '@/Components/Home/HomeCatalogCard';
import type { HomeCatalogItem } from '@/Components/Home/types';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

const DESKTOP_NAV_MEDIA = '(min-width: 1024px)';

function useDesktopSwiperNav(): boolean {
    const [enabled, setEnabled] = useState(() => {
        if (typeof window === 'undefined') {
            return false;
        }

        return window.matchMedia(DESKTOP_NAV_MEDIA).matches;
    });

    useEffect(() => {
        const media = window.matchMedia(DESKTOP_NAV_MEDIA);
        const onChange = () => setEnabled(media.matches);
        onChange();
        media.addEventListener('change', onChange);

        return () => media.removeEventListener('change', onChange);
    }, []);

    return enabled;
}
import { Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import type { Swiper as SwiperInstance } from 'swiper';
import 'swiper/css';
import 'swiper/css/pagination';

type Props = {
    items: HomeCatalogItem[];
    hrefPrefix: string;
    showPrice: boolean;
    contentType: string;
    slidesPerViewDesktop: number;
};

type NavState = {
    show: boolean;
    prev: boolean;
    next: boolean;
};

function readNavState(swiper: SwiperInstance): NavState {
    return {
        show: !swiper.isLocked,
        prev: !swiper.isBeginning,
        next: !swiper.isEnd,
    };
}

export function HomeCatalogSwiper({
    items,
    hrefPrefix,
    showPrice,
    contentType,
    slidesPerViewDesktop,
}: Props) {
    const swiperRef = useRef<SwiperInstance | null>(null);
    const showDesktopNav = useDesktopSwiperNav();
    const [nav, setNav] = useState<NavState>({ show: false, prev: false, next: false });

    const syncNav = useCallback((swiper: SwiperInstance) => {
        setNav(readNavState(swiper));
    }, []);

    const breakpoints = useMemo(
        () => ({
            480: { slidesPerView: 1.65, spaceBetween: 16 },
            768: { slidesPerView: Math.min(2.35, slidesPerViewDesktop), spaceBetween: 18 },
            1024: {
                slidesPerView: slidesPerViewDesktop,
                spaceBetween: 18,
            },
        }),
        [slidesPerViewDesktop],
    );

    if (items.length === 0) {
        return null;
    }

    const navButtonClass =
        'home-catalog-swiper__nav absolute top-[32%] z-40 flex size-10 -translate-y-1/2 items-center justify-center rounded-full border border-secondary/60 bg-neutral-white text-primary shadow-sm transition hover:border-primary/30 hover:shadow-md';

    return (
        <div className="home-catalog-swiper relative min-w-0">
            {showDesktopNav && nav.show && nav.prev ? (
                <button
                    type="button"
                    aria-label="قبلی"
                    onClick={() => swiperRef.current?.slidePrev()}
                    className={cn(navButtonClass, 'ltr:left-0 rtl:right-0')}
                >
                    <ChevronRight className="size-5" />
                </button>
            ) : null}
            {showDesktopNav && nav.show && nav.next ? (
                <button
                    type="button"
                    aria-label="بعدی"
                    onClick={() => swiperRef.current?.slideNext()}
                    className={cn(navButtonClass, 'ltr:right-0 rtl:left-0')}
                >
                    <ChevronLeft className="size-5" />
                </button>
            ) : null}

            <Swiper
                key={`${items.map((item) => item.id).join('-')}-${slidesPerViewDesktop}`}
                modules={[Pagination]}
                onSwiper={(swiper) => {
                    swiperRef.current = swiper;
                    syncNav(swiper);
                }}
                onSlideChange={syncNav}
                onResize={syncNav}
                onBreakpoint={syncNav}
                onReachBeginning={syncNav}
                onReachEnd={syncNav}
                onFromEdge={syncNav}
                dir="rtl"
                grabCursor
                watchOverflow
                spaceBetween={16}
                slidesPerView={1.08}
                breakpoints={breakpoints}
                pagination={{
                    clickable: true,
                    dynamicBullets: items.length > 5,
                }}
                className="home-catalog-swiper__root w-full pb-10"
            >
                {items.map((item) => (
                    <SwiperSlide key={item.id} className="home-catalog-swiper__slide !h-auto">
                        <HomeCatalogCard
                            item={item}
                            hrefPrefix={hrefPrefix}
                            showPrice={showPrice}
                            contentType={contentType}
                            className="home-catalog-swiper__card h-full"
                        />
                    </SwiperSlide>
                ))}
            </Swiper>
        </div>
    );
}
