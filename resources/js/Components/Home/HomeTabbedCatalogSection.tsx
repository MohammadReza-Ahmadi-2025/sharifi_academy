import { HomeCatalogCard } from '@/Components/Home/HomeCatalogCard';
import { HomeCatalogSwiper } from '@/Components/Home/HomeCatalogSwiper';
import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import { HomeSectionTabs } from '@/Components/Home/HomeSectionTabs';
import { resolveCatalogSlidesPerViewDesktop } from '@/Components/Home/home-catalog-slides';
import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import { useState } from 'react';

type Props = {
    section: HomeSection;
    tabs: HomeSectionTab[];
    hrefPrefixForType: (contentType: string) => string;
    emptyForType: (contentType: string) => string;
    showPriceForType: (contentType: string) => boolean;
    sectionId?: string;
    catalogLayout?: 'grid' | 'carousel';
};

export function HomeTabbedCatalogSection({
    section,
    tabs,
    hrefPrefixForType,
    emptyForType,
    showPriceForType,
    sectionId,
    catalogLayout = 'grid',
}: Props) {
    const SectionIcon = resolveHomeSectionIcon(section);
    const [activeTabId, setActiveTabId] = useState(tabs[0]?.id ?? 0);
    const activeTab = tabs.find((t) => t.id === activeTabId) ?? tabs[0];
    const items = activeTab?.items ?? [];
    const viewAll = activeTab?.view_all_url;
    const contentType = activeTab?.content_type ?? 'product';
    const hrefPrefix = hrefPrefixForType(contentType);
    const empty = tabs.length === 0 ? 'تب فعالی برای این سکشن تعریف نشده است.' : emptyForType(contentType);
    const showPrice = showPriceForType(contentType);
    const slidesPerViewDesktop = resolveCatalogSlidesPerViewDesktop(section.settings);

    return (
        <HomeSectionShell id={sectionId} title={section.title} viewAllHref={viewAll} icon={SectionIcon}>
            <HomeSectionTabs tabs={tabs} activeId={activeTabId} onChange={setActiveTabId} />

            {items.length === 0 ? (
                <p className="text-center text-sm text-neutral-black/55 md:py-6">{empty}</p>
            ) : catalogLayout === 'carousel' ? (
                <div className="min-w-0">
                    <HomeCatalogSwiper
                        key={`tab-${activeTab?.id ?? 0}-${items.length}-${slidesPerViewDesktop}`}
                        items={items}
                        hrefPrefix={hrefPrefix}
                        showPrice={showPrice}
                        contentType={contentType}
                        slidesPerViewDesktop={slidesPerViewDesktop}
                    />
                </div>
            ) : (
                <div className="grid gap-4 sm:grid-cols-2 sm:gap-5 lg:grid-cols-3 lg:gap-6">
                    {items.map((item) => (
                        <HomeCatalogCard
                            key={item.id}
                            item={item}
                            hrefPrefix={hrefPrefix}
                            showPrice={showPrice}
                            contentType={contentType}
                        />
                    ))}
                </div>
            )}
        </HomeSectionShell>
    );
}
