import { HomeBannerCarousel } from '@/Components/Home/HomeBannerCarousel';
import { HomeCmsHeroSection } from '@/Components/Home/HomeCmsHeroSection';
import { HomeFeaturesSection } from '@/Components/Home/HomeFeaturesSection';
import { HomeTabbedCatalogSection } from '@/Components/Home/HomeTabbedCatalogSection';
import {
    homeCatalogEmptyMessage,
    homeCatalogHrefPrefix,
    homeCatalogShowPrice,
} from '@/Components/Home/home-section-config';
import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';

type Props = {
    sections: HomeSection[];
};

function primaryContentType(tabs: HomeSectionTab[]): string {
    return tabs[0]?.content_type ?? 'product';
}

function sectionCatalogLayout(section: HomeSection): 'grid' | 'carousel' {
    const settings = section.settings ?? {};

    if (settings.section_template === 'catalog' || settings.layout === 'carousel') {
        return 'carousel';
    }

    return 'grid';
}

export function HomeDynamicSections({ sections }: Props) {
    if (!sections.length) {
        return (
            <section className="mx-auto max-w-6xl px-4 py-16">
                <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-12 text-center text-sm text-neutral-black/70">
                    سکشنی برای صفحه اصلی فعال نیست. از پنل مدیریت → صفحه اصلی، سکشن‌ها را تنظیم کنید.
                </p>
            </section>
        );
    }

    return (
        <>
            {sections.map((section) => {
                const tabs = section.tabs ?? [];
                const sectionId = `home-section-${section.id}`;

                if (tabs.length === 0) {
                    return (
                        <section key={section.id} id={sectionId} className="mx-auto max-w-6xl scroll-mt-24 px-4 py-10">
                            <h2 className="text-2xl font-extrabold text-neutral-black">{section.title}</h2>
                            <p className="mt-6 rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-10 text-center text-sm text-neutral-black/70">
                                تب فعالی برای این سکشن تعریف نشده است.
                            </p>
                        </section>
                    );
                }

                if (section.settings?.section_template === 'hero_split') {
                    return <HomeCmsHeroSection key={section.id} section={section} sectionId={sectionId} />;
                }

                if (section.settings?.section_template === 'features') {
                    return <HomeFeaturesSection key={section.id} section={section} sectionId={sectionId} />;
                }

                const contentType = primaryContentType(tabs);

                switch (contentType) {
                    case 'features':
                        return <HomeFeaturesSection key={section.id} section={section} sectionId={sectionId} />;
                    case 'banner':
                        return <HomeBannerCarousel key={section.id} section={section} tabs={tabs} sectionId={sectionId} />;
                    default:
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefixForType={homeCatalogHrefPrefix}
                                emptyForType={homeCatalogEmptyMessage}
                                showPriceForType={homeCatalogShowPrice}
                                sectionId={sectionId}
                                catalogLayout={sectionCatalogLayout(section)}
                            />
                        );
                }
            })}
        </>
    );
}
