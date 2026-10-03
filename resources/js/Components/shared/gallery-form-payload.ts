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
        [field]: items.map((item, index) => ({
            id: item.existingFileId ?? null,
            sort: index + 1,
            alt: item.alt ?? '',
            title: item.title ?? '',
            file: item.file ?? null,
        })),
    };
}
