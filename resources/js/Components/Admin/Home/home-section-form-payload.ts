import type { HomeSectionFormData } from '@/Components/Admin/Home/HomeSectionFormFields';
import type { FeatureGridRow } from '@/Components/Admin/Home/SectionTabFeaturesGridField';
import type { HomeSectionTemplateId } from '@/Components/Admin/Home/home-section-templates';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { FileUploadItem } from '@/Components/shared/file-upload';

function sectionTemplateFrom(data: HomeSectionFormData): HomeSectionTemplateId | undefined {
    const raw = data.settings?.section_template;
    if (
        raw === 'hero_split' ||
        raw === 'catalog' ||
        raw === 'features' ||
        raw === 'banners' ||
        raw === 'testimonials' ||
        raw === 'instructors'
    ) {
        return raw;
    }

    return undefined;
}

function uploadItemsHaveFile(items: FileUploadItem[]): boolean {
    return items.some((item) => item.file != null);
}

export function prepareHomeSectionFormPayload(data: HomeSectionFormData): Record<string, unknown> {
    const template = sectionTemplateFrom(data);

    const tabs = data.tabs.map((tab) => {
        if (tab.content_type === 'banner') {
            const banners = (tab.banners ?? [])
                .filter((banner) => (banner.image ?? []).length > 0)
                .map((banner) => {
                const { image, ...fields } = banner;
                const base = { ...fields };

                return withGalleryPayload(base, (image ?? []) as FileUploadItem[], { field: 'image' });
            });

            return {
                ...tab,
                banners,
            };
        }

        if (tab.content_type === 'testimonials') {
            const rows = (tab.settings?.testimonials ?? []) as {
                name?: string;
                role?: string;
                quote?: string;
                rating?: number;
            }[];

            return {
                ...tab,
                limit: 0,
                settings: {
                    ...tab.settings,
                    testimonials: rows.map((row) => ({
                        name: row.name ?? '',
                        role: row.role ?? '',
                        quote: row.quote ?? '',
                        rating: Math.min(5, Math.max(1, Math.round(Number(row.rating) || 5))),
                    })),
                },
            };
        }

        if (tab.content_type === 'features') {
            const rows = (tab.settings?.features ?? []) as FeatureGridRow[];
            const features = rows.map((row) => {
                const { image, ...fields } = row;
                const base = {
                    label: fields.label ?? '',
                    text: fields.text ?? '',
                    image_url: fields.image_url ?? '',
                    image_alt: fields.image_alt ?? '',
                    image_title: fields.image_title ?? '',
                };
                const imageItems = (image ?? []) as FileUploadItem[];
                const hasNewUpload = uploadItemsHaveFile(imageItems);
                const primaryImage = imageItems[0];

                if (!hasNewUpload) {
                    return {
                        ...base,
                        image_alt: primaryImage?.alt ?? base.image_alt,
                        image_title: primaryImage?.title ?? base.image_title,
                    };
                }

                return withGalleryPayload(base, imageItems, { field: 'image' });
            });

            return {
                ...tab,
                limit: 0,
                settings: {
                    ...tab.settings,
                    features,
                },
            };
        }

        const { banners, ...rest } = tab;

        return rest;
    });

    const tabsWithOrder = tabs.map((tab, index) => {
        let next: Record<string, unknown> = { ...tab, sort_order: index };

        if (template === 'features') {
            const { banners: _drop, ...rest } = next;
            next = {
                ...rest,
                content_type: 'features',
                source_type: 'section',
                limit: 0,
            };
        } else if (template === 'testimonials') {
            const { banners: _drop, ...rest } = next;
            next = {
                ...rest,
                content_type: 'testimonials',
                source_type: 'section',
                limit: 0,
            };
        } else if (template === 'hero_split' || template === 'banners') {
            next = {
                ...next,
                content_type: 'banner',
                source_type: 'manual',
            };
        } else if (template === 'instructors') {
            const { banners: _drop, ...rest } = next;
            next = {
                ...rest,
                content_type: 'instructor',
            };
        }

        return next;
    });

    return {
        title: data.title,
        status: data.status,
        settings: data.settings,
        tabs: tabsWithOrder,
    };
}
