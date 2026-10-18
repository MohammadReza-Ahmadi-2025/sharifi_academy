import { HomeHeroBackdrop } from '@/Components/Home/HomeHeroBackdrop';
import { HeroVisualCarousel } from '@/Components/Home/HeroVisualCarousel';
import type { HomeCatalogItem, HomeSection } from '@/Components/Home/types';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Sparkles } from 'lucide-react';

type HeroSettings = {
    badge?: string;
    title?: string;
    subtitle?: string;
    cta_primary?: string;
    cta_primary_url?: string;
    cta_secondary?: string;
    cta_secondary_url?: string;
    highlights?: { label: string; text?: string }[];
    show_live_badge?: boolean;
    promo?: { title: string; text: string; link: string; image_url?: string | null };
};

function slidesFromSection(section: HomeSection) {
    const items: HomeCatalogItem[] = section.tabs?.flatMap((tab) => tab.items ?? []) ?? [];

    return items
        .filter((item) => item.image_url)
        .map((item) => ({
            src: item.image_url!,
            alt: item.title,
            title: item.title,
            subtitle: item.subtitle ?? '',
        }));
}

export function HomeCmsHeroSection({ section, sectionId }: { section: HomeSection; sectionId?: string }) {
    const hero = (section.settings?.hero ?? {}) as HeroSettings;
    const slides = slidesFromSection(section);
    const highlights = hero.highlights?.filter((h) => h.label) ?? [];
    const promo = hero.promo;

    return (
        <section id={sectionId} className="relative overflow-hidden border-b border-secondary bg-neutral-white">
            <HomeHeroBackdrop />
            <div className="relative mx-auto grid max-w-7xl items-center gap-12 px-4 py-12 md:py-16 lg:grid-cols-2 lg:gap-16 lg:py-20">
                <div className="order-2 lg:order-1">
                    {hero.badge && (
                        <span className="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-3 py-1 text-xs font-semibold text-primary">
                            <Sparkles className="size-3.5" />
                            {hero.badge}
                        </span>
                    )}
                    <h1 className="mt-5 text-3xl font-extrabold leading-[1.15] text-neutral-black md:text-4xl lg:text-[2.75rem]">
                        {hero.title}
                    </h1>
                    {hero.subtitle && (
                        <p className="mt-5 max-w-md text-base leading-relaxed text-neutral-black/70 md:text-lg">{hero.subtitle}</p>
                    )}
                    {highlights.length > 0 && (
                        <ul className="mt-6 flex flex-col gap-2 sm:flex-row sm:flex-wrap">
                            {highlights.map((item) => (
                                <li
                                    key={item.label}
                                    className="inline-flex items-center rounded-xl bg-surface-muted px-3 py-2 text-sm font-medium text-neutral-black/80"
                                >
                                    {item.label}
                                </li>
                            ))}
                        </ul>
                    )}
                    <div className="mt-8 flex flex-wrap items-center gap-3">
                        {hero.cta_primary && (
                            <Link
                                href={hero.cta_primary_url ?? '/courses'}
                                className="inline-flex items-center gap-2 rounded-xl bg-primary px-6 py-3 text-sm font-bold text-on-primary shadow-md shadow-primary/25 transition hover:bg-primary-hover"
                            >
                                {hero.cta_primary}
                                <ArrowLeft className="size-4" />
                            </Link>
                        )}
                        {hero.cta_secondary && (
                            <Link
                                href={hero.cta_secondary_url ?? '/login'}
                                className="inline-flex items-center rounded-xl border-2 border-primary/15 bg-neutral-white px-6 py-3 text-sm font-semibold text-primary transition hover:border-primary/30 hover:bg-primary/5"
                            >
                                {hero.cta_secondary}
                            </Link>
                        )}
                    </div>
                </div>
                <div className="order-1 pb-6 lg:order-2 lg:pb-0">
                    <div className="relative">
                        <HeroVisualCarousel slides={slides} />
                        {promo?.title && (
                            <Link
                                href={promo.link ?? '#'}
                                className="hero-float-delayed absolute -bottom-6 -left-2 z-40 hidden md:flex max-w-[min(100%,14rem)] items-center gap-3 rounded-2xl border border-secondary bg-neutral-white p-2 shadow-lg transition hover:shadow-xl"
                            >
                                {promo.image_url ? (
                                    <img src={promo.image_url} alt="" className="size-16 shrink-0 rounded-xl object-cover" />
                                ) : null}
                                <div className="min-w-0 pe-2">
                                    <p className="text-xs font-bold text-primary">{promo.title}</p>
                                    {promo.text && (
                                        <p className="text-[11px] leading-snug text-neutral-black/60">{promo.text}</p>
                                    )}
                                </div>
                            </Link>
                        )}
                        {hero.show_live_badge && (
                            <div className="hero-float absolute -top-4 -left-4 z-40 hidden rounded-2xl border border-secondary bg-neutral-white px-4 py-3 shadow-md md:block">
                                <p className="text-2xl font-extrabold text-primary">Live</p>
                                <p className="text-xs text-neutral-black/60">جلسه آنلاین</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </section>
    );
}
