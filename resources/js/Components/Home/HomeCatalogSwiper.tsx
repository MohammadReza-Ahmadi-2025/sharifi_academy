import type { HomeCatalogItem } from '@/Components/Home/types';
import { Link } from '@inertiajs/react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useRef } from 'react';
import { Navigation, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import type { Swiper as SwiperInstance } from 'swiper';
import 'swiper/css';
import 'swiper/css/navigation';
import 'swiper/css/pagination';

type Props = {
    items: HomeCatalogItem[];
    hrefPrefix: string;
    showPrice: boolean;
};

export function HomeCatalogSwiper({ items, hrefPrefix, showPrice }: Props) {
    const swiperRef = useRef<SwiperInstance | null>(null);
    if (items.length === 0) {
        return null;
    }

    return (
        <div className="home-catalog-swiper relative min-w-0 md:px-12">
            <button
                type="button"
                aria-label="قبلی"
                onClick={() => swiperRef.current?.slidePrev()}
                className="home-catalog-swiper__nav absolute top-[38%] z-40 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full border border-secondary bg-neutral-white shadow-md transition hover:bg-primary/5 md:flex ltr:left-0 rtl:right-0"
            >
                <ChevronRight className="size-5 text-primary" />
            </button>
            <button
                type="button"
                aria-label="بعدی"
                onClick={() => swiperRef.current?.slideNext()}
                className="home-catalog-swiper__nav absolute top-[38%] z-40 hidden size-10 -translate-y-1/2 items-center justify-center rounded-full border border-secondary bg-neutral-white shadow-md transition hover:bg-primary/5 md:flex ltr:right-0 rtl:left-0"
            >
                <ChevronLeft className="size-5 text-primary" />
            </button>

            <Swiper
                key={items.map((item) => item.id).join('-')}
                modules={[Navigation, Pagination]}
                onSwiper={(swiper) => {
                    swiperRef.current = swiper;
                }}
                dir="rtl"
                grabCursor
                watchOverflow
                spaceBetween={20}
                slidesPerView={1.08}
                breakpoints={{
                    480: { slidesPerView: 1.65, spaceBetween: 20 },
                    768: { slidesPerView: 2.35, spaceBetween: 22 },
                    1024: { slidesPerView: 3.15, spaceBetween: 24 },
                    1280: { slidesPerView: 4, spaceBetween: 24 },
                }}
                pagination={{
                    clickable: true,
                    dynamicBullets: items.length > 5,
                }}
                className="home-catalog-swiper__root w-full pb-10"
            >
                {items.map((item) => (
                    <SwiperSlide key={item.id} className="home-catalog-swiper__slide !h-auto">
                        <CatalogCard item={item} hrefPrefix={hrefPrefix} showPrice={showPrice} />
                    </SwiperSlide>
                ))}
            </Swiper>
        </div>
    );
}

function CatalogCard({
    item,
    hrefPrefix,
    showPrice,
}: {
    item: HomeCatalogItem;
    hrefPrefix: string;
    showPrice: boolean;
}) {
    const href = item.slug ? `${hrefPrefix}${item.slug}` : hrefPrefix;

    return (
        <Link
            href={href}
            className="home-catalog-swiper__card group relative z-0 flex h-full flex-col rounded-xl border border-secondary/80 bg-neutral-white p-2.5 shadow-md ring-1 ring-black/5 transition duration-300 hover:z-20 hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-xl sm:p-3"
        >
            <div className="aspect-[5/4] overflow-hidden rounded-lg bg-primary/10">
                <img
                    src={item.image_url ?? '/banner.png'}
                    alt={item.title}
                    className="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                />
            </div>
            <div className="flex flex-1 flex-col px-1.5 pb-1 pt-3.5 sm:px-2 sm:pt-4">
                <h3 className="line-clamp-2 text-base font-extrabold leading-snug text-neutral-black sm:text-lg">
                    {item.title}
                </h3>
                {item.description && (
                    <p className="mt-2 line-clamp-2 text-sm leading-relaxed text-neutral-black/65">{item.description}</p>
                )}
                {showPrice && typeof item.price === 'number' && (
                    <p className="mt-auto pt-3 text-base font-extrabold text-primary">
                        {item.price.toLocaleString('fa-IR')} ریال
                    </p>
                )}
            </div>
        </Link>
    );
}
