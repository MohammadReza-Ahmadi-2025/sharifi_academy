import { HomeTestimonialStars, clampTestimonialRating } from '@/Components/Home/HomeTestimonialStars';
import type { TestimonialItem } from '@/Components/Home/HomeTestimonialsSection';
import { Autoplay, Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/pagination';

function initials(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '؟';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 1);
    }

    return `${parts[0].slice(0, 1)}${parts[1].slice(0, 1)}`;
}

function TestimonialSlideCard({ item }: { item: TestimonialItem }) {
    const rating = clampTestimonialRating(item.rating);

    return (
        <article className="flex h-full min-h-[14rem] flex-col rounded-2xl border border-secondary/55 bg-neutral-white p-5 shadow-[0_2px_16px_-6px_rgba(0,72,158,0.12)]">
            <div className="flex items-start gap-3 border-b border-secondary/40 pb-4">
                <span
                    className="flex size-12 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-bold text-primary"
                    aria-hidden
                >
                    {initials(item.name)}
                </span>
                <div className="min-w-0 flex-1">
                    <p className="truncate font-bold text-neutral-black">{item.name}</p>
                    {item.role ? <p className="truncate text-xs text-neutral-black/55">{item.role}</p> : null}
                    <HomeTestimonialStars rating={rating} className="mt-2" sizeClass="size-3.5" />
                </div>
                <span className="shrink-0 rounded-full bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700">
                    {rating.toLocaleString('fa-IR')}/۵
                </span>
            </div>
            <p className="mt-4 flex-1 text-sm leading-relaxed text-neutral-black/75">{item.quote}</p>
        </article>
    );
}

type Props = {
    items: TestimonialItem[];
};

export function HomeTestimonialsSwiper({ items }: Props) {
    if (items.length === 0) {
        return <p className="text-center text-sm text-neutral-black/55 md:py-6">نظری برای نمایش ثبت نشده است.</p>;
    }

    const canAutoplay = items.length > 1;

    return (
        <div className="home-testimonials-swiper relative min-w-0">
            <Swiper
                modules={[Pagination, Autoplay]}
                dir="rtl"
                grabCursor
                watchOverflow
                loop={canAutoplay}
                autoplay={
                    canAutoplay
                        ? {
                              delay: 4500,
                              disableOnInteraction: false,
                              pauseOnMouseEnter: true,
                          }
                        : false
                }
                spaceBetween={16}
                slidesPerView={1.08}
                breakpoints={{
                    640: { slidesPerView: 1.35, spaceBetween: 18 },
                    1024: { slidesPerView: 2.15, spaceBetween: 20 },
                    1280: { slidesPerView: 2.65, spaceBetween: 22 },
                }}
                pagination={{
                    clickable: true,
                    dynamicBullets: items.length > 4,
                }}
                className="home-testimonials-swiper__root w-full pb-10"
            >
                {items.map((item, index) => (
                    <SwiperSlide key={`${item.name}-${index}`} className="!h-auto">
                        <TestimonialSlideCard item={item} />
                    </SwiperSlide>
                ))}
            </Swiper>
        </div>
    );
}
