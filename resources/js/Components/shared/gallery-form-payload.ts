import type { FileUploadItem } from '@/Components/shared/file-upload';

type FormPayload = Record<string, unknown>;

type GalleryPayloadOptions = {
    field?: string;
};

export function withGalleryPayload(
    data: FormPayload,
    items: FileUploadItem[],
    options: GalleryPayloadOptions = {},
): FormPayload {
    const field = options.field ?? 'gallery';

    if (items.length === 0) {
        return { ...data, [field]: [] };
    }

    return {
        ...data,
        [field]: items.map((item, index) => {
            const entry: Record<string, unknown> = {
                sort: index + 1,
                alt: item.alt ?? '',
                title: item.title ?? '',
            };

            if (item.existingFileId != null) {
                entry.id = item.existingFileId;
            }

            if (item.file) {
                entry.file = item.file;
            }

            return entry;
        }),
    };
}
