import { HomeBannerCarousel } from '@/Components/Home/HomeBannerCarousel';
import { HomeCmsHeroSection } from '@/Components/Home/HomeCmsHeroSection';
import { HomeFeaturesSection } from '@/Components/Home/HomeFeaturesSection';
import { HomeInstructorsSection } from '@/Components/Home/HomeInstructorsSection';
import { HomeTestimonialsSection } from '@/Components/Home/HomeTestimonialsSection';
import { resolveHomeSectionIcon } from '@/Components/Home/home-section-icon';
import { HomeSectionShell } from '@/Components/Home/HomeSectionShell';
import { HomeTabbedCatalogSection } from '@/Components/Home/HomeTabbedCatalogSection';
import {
    homeCatalogEmptyMessage,
    homeCatalogHrefPrefix,
    homeCatalogShowPrice,
} from '@/Components/Home/home-section-config';
import type { HomeSection, HomeSectionTab } from '@/Components/Home/types';
import type { ReactNode } from 'react';

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
            <section className="mx-auto max-w-7xl px-4 py-16">
                <p className="text-center text-sm text-neutral-black/65">
                    سکشنی برای صفحه اصلی فعال نیست. از پنل مدیریت → صفحه اصلی، سکشن‌ها را تنظیم کنید.
                </p>
            </section>
        );
    }

    return (
        <div className="home-sections-flow">
            {sections.map((section) => {
                const tabs = section.tabs ?? [];
                const sectionId = `home-section-${section.id}`;

                const wrap = (node: ReactNode) => (
                    <div key={section.id} className="home-section-wrap">
                        {node}
                    </div>
                );

                if (tabs.length === 0) {
                    const Icon = resolveHomeSectionIcon(section);

                    return wrap(
                        <HomeSectionShell id={sectionId} title={section.title} icon={Icon}>
                            <p className="text-center text-sm text-neutral-black/55 md:py-6">
                                تب فعالی برای این سکشن تعریف نشده است.
                            </p>
                        </HomeSectionShell>,
                    );
                }

                if (section.settings?.section_template === 'hero_split') {
                    return wrap(<HomeCmsHeroSection section={section} sectionId={sectionId} />);
                }

                if (section.settings?.section_template === 'features') {
                    return wrap(<HomeFeaturesSection section={section} sectionId={sectionId} />);
                }

                if (section.settings?.section_template === 'testimonials') {
                    return wrap(<HomeTestimonialsSection section={section} sectionId={sectionId} />);
                }

                if (section.settings?.section_template === 'instructors') {
                    return wrap(<HomeInstructorsSection section={section} sectionId={sectionId} />);
                }

                const contentType = primaryContentType(tabs);

                switch (contentType) {
                    case 'features':
                        return wrap(<HomeFeaturesSection section={section} sectionId={sectionId} />);
                    case 'testimonials':
                        return wrap(<HomeTestimonialsSection section={section} sectionId={sectionId} />);
                    case 'banner':
                        return wrap(
                            <HomeBannerCarousel section={section} tabs={tabs} sectionId={sectionId} />,
                        );
                    default:
                        return wrap(
                            <HomeTabbedCatalogSection
                                section={section}
                                tabs={tabs}
                                hrefPrefixForType={homeCatalogHrefPrefix}
                                emptyForType={homeCatalogEmptyMessage}
                                showPriceForType={homeCatalogShowPrice}
                                sectionId={sectionId}
                                catalogLayout={sectionCatalogLayout(section)}
                            />,
                        );
                }
            })}
        </div>
    );
}
