import { HomeBannerCarousel } from '@/Components/Home/HomeBannerCarousel';
import { HomeTabbedCatalogSection } from '@/Components/Home/HomeTabbedCatalogSection';
import type { HomeSection } from '@/Components/Home/types';

type Props = {
    sections: HomeSection[];
};

export function HomeDynamicSections({ sections }: Props) {
    return (
        <>
            {sections.map((section) => {
                const tabs = section.tabs ?? [];
                if (tabs.length === 0) {
                    return null;
                }

                const contentType = tabs[0]?.content_type ?? 'product';

                switch (contentType) {
                    case 'banner':
                        return <HomeBannerCarousel key={section.id} section={section} tabs={tabs} />;
                    case 'course':
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/courses/"
                                empty="هنوز دوره‌ای منتشر نشده است."
                                sectionId="courses"
                            />
                        );
                    case 'product':
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/books/"
                                empty="هنوز کتابی منتشر نشده است."
                                sectionId="books"
                            />
                        );
                    case 'post':
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/blog/"
                                empty="هنوز پستی منتشر نشده است."
                                showPrice={false}
                            />
                        );
                    case 'brand':
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/brands/"
                                empty="برندی ثبت نشده است."
                                showPrice={false}
                            />
                        );
                    case 'category':
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/books?category="
                                empty="دسته‌بندی‌ای وجود ندارد."
                                showPrice={false}
                            />
                        );
                    default:
                        return (
                            <HomeTabbedCatalogSection
                                key={section.id}
                                section={section}
                                tabs={tabs}
                                hrefPrefix="/books/"
                                empty="موردی برای نمایش نیست."
                            />
                        );
                }
            })}
        </>
    );
}
