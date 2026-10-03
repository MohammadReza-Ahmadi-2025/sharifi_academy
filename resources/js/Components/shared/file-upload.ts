export type ExistingFilePayload = {
    id: number;
    url: string;
    name?: string | null;
    size?: number | null;
    title?: string | null;
    alt?: string | null;
};

export type FileUploadItem = {
    /** شناسه یکتا برای رندر لیست (کلاینت) */
    id: string;
    /** فایل جدید؛ برای رکوردهای ذخیره‌شده خالی است */
    file?: File;
    existingFileId?: number;
    /** برای پیش‌نمایش تصویر؛ با `revokeFileUploadPreviews` آزاد شود */
    previewUrl?: string;
    existingName?: string;
    existingSize?: number;
    title?: string;
    alt?: string;
};

export type FileUploaderLabels = {
    dropzoneTitle?: string;
    dropzoneHint?: string;
    browse?: string;
    remove?: string;
    title?: string;
    alt?: string;
    titlePlaceholder?: string;
    altPlaceholder?: string;
};

export const defaultFileUploaderLabels: FileUploaderLabels = {
    dropzoneTitle: 'فایل را اینجا بکشید و رها کنید',
    dropzoneHint: 'یا برای انتخاب از سیستم کلیک کنید',
    browse: 'انتخاب فایل',
    remove: 'حذف',
    title: 'عنوان',
    alt: 'متن جایگزین (alt)',
    titlePlaceholder: 'عنوان تصویر',
    altPlaceholder: 'توضیح تصویر برای دسترس‌پذیری',
};

export function createFileUploadId(): string {
    if (typeof crypto !== 'undefined' && typeof crypto.randomUUID === 'function') {
        return crypto.randomUUID();
    }

    return `file-${Date.now()}-${Math.random().toString(36).slice(2, 9)}`;
}

export function isImageFile(file: File): boolean {
    return file.type.startsWith('image/');
}

export function createExistingFileUploadItem(payload: ExistingFilePayload): FileUploadItem {
    return {
        id: `existing-${payload.id}`,
        existingFileId: payload.id,
        previewUrl: payload.url,
        existingName: payload.name ?? 'تصویر',
        existingSize: payload.size ?? 0,
        title: payload.title ?? '',
        alt: payload.alt ?? '',
    };
}

export function createExistingFileUploadItems(payloads: ExistingFilePayload[]): FileUploadItem[] {
    return payloads.map((payload) => createExistingFileUploadItem(payload));
}

export function createFileUploadItem(file: File): FileUploadItem {
    const item: FileUploadItem = {
        id: createFileUploadId(),
        file,
    };

    if (isImageFile(file)) {
        item.previewUrl = URL.createObjectURL(file);
    }

    return item;
}

export function createFileUploadItems(files: FileList | File[]): FileUploadItem[] {
    return Array.from(files).map((file) => createFileUploadItem(file));
}

export function revokeFileUploadPreviews(items: FileUploadItem[]): void {
    for (const item of items) {
        if (item.previewUrl) {
            URL.revokeObjectURL(item.previewUrl);
        }
    }
}

export function formatFileSize(bytes: number): string {
    if (bytes < 1024) {
        return `${bytes} B`;
    }

    if (bytes < 1024 * 1024) {
        return `${(bytes / 1024).toFixed(1)} KB`;
    }

    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}
