import type { HomeSectionFormData } from '@/Components/Admin/Home/HomeSectionFormFields';
import type { FeatureGridRow } from '@/Components/Admin/Home/SectionTabFeaturesGridField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { FileUploadItem } from '@/Components/shared/file-upload';

export function prepareHomeSectionFormPayload(data: HomeSectionFormData): Record<string, unknown> {
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

        if (tab.content_type === 'features') {
            const rows = (tab.settings.features ?? []) as FeatureGridRow[];
            const features = rows.map((row) => {
                const { image, ...fields } = row;

                return withGalleryPayload(
                    {
                        label: fields.label ?? '',
                        text: fields.text ?? '',
                        image_url: fields.image_url ?? '',
                    },
                    (image ?? []) as FileUploadItem[],
                    { field: 'image' },
                );
            });

            return {
                ...tab,
                settings: {
                    ...tab.settings,
                    features,
                },
            };
        }

        const { banners, ...rest } = tab;

        return rest;
    });

    return {
        title: data.title,
        status: data.status,
        settings: data.settings,
        tabs: tabs.map((tab, index) => ({
            ...tab,
            sort_order: index,
        })),
    };
}
