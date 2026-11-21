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
        'group flex h-full w-full flex-col items-center rounded-3xl border border-secondary/40 bg-neutral-white/90 px-4 pb-5 pt-6 text-center',
        'shadow-[0_4px_20px_-8px_rgba(0,72,158,0.12)] backdrop-blur-sm transition duration-300',
        'hover:-translate-y-1 hover:border-primary/30 hover:shadow-[0_16px_36px_-14px_rgba(0,72,158,0.28)]',
    );

    const inner = (
        <>
            <div className="relative">
                <div
                    className="absolute -inset-1 rounded-full bg-gradient-to-br from-primary/35 via-secondary/40 to-primary/10 opacity-80 transition duration-500 group-hover:opacity-100 group-hover:blur-[1px]"
                    aria-hidden
                />
                <div className="relative size-[7.25rem] overflow-hidden rounded-full bg-secondary/30 ring-4 ring-neutral-white sm:size-[8rem]">
                    {item.image_url ? (
                        <img
                            src={item.image_url}
                            alt={item.title}
                            className="size-full object-cover transition duration-500 group-hover:scale-[1.06]"
                        />
                    ) : (
                        <span
                            className="flex size-full items-center justify-center bg-primary/8 text-2xl font-extrabold tracking-tight text-primary"
                            aria-hidden
                        >
                            {initials(item.title)}
                        </span>
                    )}
                </div>
            </div>

            <div className="mt-5 flex min-w-0 flex-1 flex-col items-center">
                <p className="line-clamp-2 text-[0.95rem] font-extrabold leading-snug text-neutral-black transition group-hover:text-primary sm:text-base">
                    {item.title}
                </p>
                {item.subtitle ? (
                    <p className="mt-1.5 line-clamp-2 max-w-[14rem] text-xs leading-relaxed text-neutral-black/55 sm:text-[0.8rem]">
                        {item.subtitle}
                    </p>
                ) : null}
                <span className="mt-4 inline-flex items-center gap-1 rounded-full bg-primary/8 px-3 py-1 text-[0.7rem] font-bold text-primary transition group-hover:bg-primary group-hover:text-on-primary">
                    مشاهده پروفایل
                </span>
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
            768: {
                slidesPerView: Math.min(2.35, slidesPerView),
                spaceBetween: 18,
                slidesOffsetBefore: 0,
                slidesOffsetAfter: 0,
            },
            1024: {
                slidesPerView: slidesPerView,
                spaceBetween: 18,
                slidesOffsetBefore: 0,
                slidesOffsetAfter: 0,
            },
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
                        slidesPerView={1.2}
                        slidesOffsetBefore={6}
                        slidesOffsetAfter={6}
                        breakpoints={breakpoints}
                        pagination={{ clickable: true, dynamicBullets: items.length > 5 }}
                        className="home-instructors-swiper__root w-full pb-10"
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
