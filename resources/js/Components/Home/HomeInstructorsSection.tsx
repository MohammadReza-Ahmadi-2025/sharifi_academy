import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import { CATALOG_SWIPER_SLIDES_PER_VIEW_LG } from '@/Components/Admin/Home/home-section-templates';
import type { HomeCatalogItem, HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { useMemo } from 'react';
import { Pagination } from 'swiper/modules';
import { Swiper, SwiperSlide } from 'swiper/react';
import 'swiper/css';
import 'swiper/css/pagination';

function instructorItemsFromTab(tab: HomeSectionTab | undefined): HomeCatalogItem[] {
    if (!tab?.items?.length) {
        return [];
    }

    return tab.items.filter((item) => Boolean(item.profile_url || item.resume_url || item.id));
}

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

function InstructorCard({ item }: { item: HomeCatalogItem }) {
    const href = item.profile_url ?? item.resume_url ?? '#';
    const shellClass = cn(
        'group flex h-full w-full flex-col overflow-hidden rounded-2xl border border-secondary/50 bg-neutral-white p-3 text-right shadow-[0_2px_12px_-4px_rgba(0,72,158,0.08)] transition duration-300',
        'hover:-translate-y-0.5 hover:border-primary/25 hover:shadow-[0_12px_28px_-10px_rgba(0,72,158,0.2)]',
    );

    const inner = (
        <>
            <div className="relative mx-auto aspect-square w-full max-w-[9.5rem] overflow-hidden rounded-2xl bg-secondary/25">
                {item.image_url ? (
                    <img
                        src={item.image_url}
                        alt={item.title}
                        className="size-full object-cover transition duration-500 group-hover:scale-[1.04]"
                    />
                ) : (
                    <span
                        className="flex size-full items-center justify-center text-2xl font-bold text-primary/80"
                        aria-hidden
                    >
                        {initials(item.title)}
                    </span>
                )}
            </div>
            <div className="mt-3 flex flex-1 flex-col items-center px-1 pb-1 text-center">
                <p className="font-bold text-neutral-black transition group-hover:text-primary">{item.title}</p>
                {item.subtitle ? (
                    <p className="mt-1 line-clamp-2 text-xs text-neutral-black/55">{item.subtitle}</p>
                ) : null}
                <span className="mt-3 text-[0.7rem] font-semibold text-primary/80">مشاهده رزومه</span>
            </div>
        </>
    );

    if (href === '#') {
        return <div className={shellClass}>{inner}</div>;
    }

    return (
        <Link href={href} className={shellClass}>
            {inner}
        </Link>
    );
}

type Props = {
    section: HomeSection;
    sectionId?: string;
};

export function HomeInstructorsSection({ section, sectionId }: Props) {
    const SectionIcon = resolveHomeSectionIcon(section);
    const tab = section.tabs?.[0];
    const items = instructorItemsFromTab(tab);
    const slidesPerView =
        typeof section.settings?.catalog_slides_per_view === 'number'
            ? section.settings.catalog_slides_per_view
            : CATALOG_SWIPER_SLIDES_PER_VIEW_LG;

    const breakpoints = useMemo(
        () => ({
            480: { slidesPerView: 1.65, spaceBetween: 16 },
            768: { slidesPerView: Math.min(2.35, slidesPerView), spaceBetween: 18 },
            1024: { slidesPerView: slidesPerView, spaceBetween: 18 },
        }),
        [slidesPerView],
    );

    return (
        <HomeSectionShell id={sectionId} title={section.title} icon={SectionIcon}>
            {items.length === 0 ? (
                <p className="text-center text-sm text-neutral-black/55 md:py-6">
                    استادی برای نمایش انتخاب نشده است.
                </p>
            ) : (
                <div className="home-instructors-swiper relative min-w-0">
                    <Swiper
                        modules={[Pagination]}
                        dir="rtl"
                        grabCursor
                        watchOverflow
                        spaceBetween={16}
                        slidesPerView={1.08}
                        breakpoints={breakpoints}
                        pagination={{ clickable: true, dynamicBullets: items.length > 5 }}
                        className="w-full pb-10"
                    >
                        {items.map((item) => (
                            <SwiperSlide key={item.id} className="!h-auto">
                                <InstructorCard item={item} />
                            </SwiperSlide>
                        ))}
                    </Swiper>
                </div>
            )}
        </HomeSectionShell>
    );
}
