import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import type { HomeSection } from '@/Components/Home/types';
import { HomeSectionTabs } from '@/Components/Home/HomeSectionTabs';
import type { HomeCatalogItem } from '@/Components/Home/types';
import { Link } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    section: Pick<HomeSection, 'id' | 'title' | 'settings' | 'tabs'>;
    tabs: { id: number; title: string; items: HomeCatalogItem[] }[];
    sectionId?: string;
};

export function HomeBannerCarousel({ section, tabs, sectionId }: Props) {
    const SectionIcon = resolveHomeSectionIcon(section as HomeSection);
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const banners = activeTab?.items ?? [];
    const [slide, setSlide] = useState(0);

    if (!activeTab) {
        return null;
    }

    if (banners.length === 0) {
        return (
            <HomeSectionShell id={sectionId} title={section.title} icon={SectionIcon}>
                <p className="text-center text-sm text-neutral-black/55 md:py-6">بنری برای نمایش ثبت نشده است.</p>
            </HomeSectionShell>
        );
    }

    const current = banners[slide % banners.length];

    return (
        <HomeSectionShell id={sectionId} title={section.title} icon={SectionIcon}>
            <HomeSectionTabs
                tabs={tabs}
                activeId={activeTabId}
                onChange={(id) => {
                    setActiveTabId(id);
                    setSlide(0);
                }}
                resolveLabel={(tab) => (tab.title === 'default' ? section.title : tab.title)}
            />

            <div className="relative overflow-hidden rounded-3xl">
                <div className="relative aspect-[21/9] min-h-[200px] w-full bg-neutral-black/5 md:min-h-[280px]">
                    {current.image_url ? (
                        <img src={current.image_url} alt={current.title ?? ''} className="size-full object-cover" />
                    ) : (
                        <div className="flex size-full items-center justify-center text-neutral-black/35">بدون تصویر</div>
                    )}
                    <div className="absolute inset-0 bg-gradient-to-t from-neutral-black/80 via-neutral-black/25 to-transparent" />
                    <div className="absolute inset-x-0 bottom-0 p-6 md:p-10">
                        {current.title && (
                            <h3 className="text-2xl font-black text-neutral-white md:text-3xl">{current.title}</h3>
                        )}
                        {(current.subtitle || current.description) && (
                            <p className="mt-2 max-w-lg text-sm text-neutral-white/85 md:text-base">
                                {current.subtitle ?? current.description}
                            </p>
                        )}
                        {current.button_link && (
                            <Link
                                href={current.button_link}
                                className="mt-5 inline-flex rounded-full bg-neutral-white px-6 py-2.5 text-sm font-extrabold text-primary transition hover:bg-neutral-white/90"
                            >
                                {current.button_text ?? 'مشاهده'}
                            </Link>
                        )}
                    </div>
                </div>
                {banners.length > 1 && (
                    <div className="absolute bottom-4 left-1/2 flex -translate-x-1/2 gap-2 md:bottom-6">
                        {banners.map((_, i) => (
                            <button
                                key={i}
                                type="button"
                                aria-label={`اسلاید ${i + 1}`}
                                onClick={() => setSlide(i)}
                                className={`h-1.5 rounded-full transition-all ${
                                    i === slide % banners.length ? 'w-8 bg-neutral-white' : 'w-1.5 bg-neutral-white/50'
                                }`}
                            />
                        ))}
                    </div>
                )}
            </div>
        </HomeSectionShell>
    );
}
