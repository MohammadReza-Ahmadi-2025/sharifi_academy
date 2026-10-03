import { Card } from '@/Components/Admin/shared/ui/card';
import { FileUploader, useFileUploadState } from '@/Components/shared/FileUploader';
import {
    createExistingFileUploadItems,
    type ExistingFilePayload,
    type FileUploadItem,
} from '@/Components/shared/file-upload';

export const GALLERY_IMAGE_MAX_BYTES = 5 * 1024 * 1024;

type Props = {
    title: string;
    description?: string;
    items: FileUploadItem[];
    onItemsChange: (items: FileUploadItem[]) => void;
    multiple?: boolean;
    maxFiles?: number;
    disabled?: boolean;
    error?: string;
};

export function useGalleryUploadState(initial: ExistingFilePayload[] = []) {
    const [items, setItems] = useFileUploadState(createExistingFileUploadItems(initial));

    return [items, setItems] as const;
}

export function GalleryImagesField({
    title,
    description,
    items,
    onItemsChange,
    multiple = true,
    maxFiles,
    disabled,
    error,
}: Props) {
    return (
        <Card className="space-y-4 p-6">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">{title}</h2>
                {description ? (
                    <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>
                ) : null}
            </div>
            <FileUploader
                value={items}
                onChange={onItemsChange}
                multiple={multiple}
                sortable={multiple}
                minItems={1}
                maxFiles={maxFiles ?? (multiple ? 20 : 1)}
                showTitle
                showAlt
                accept="image/jpeg,image/png,image/webp,image/gif"
                maxSizeBytes={GALLERY_IMAGE_MAX_BYTES}
                disabled={disabled}
                error={error}
                hint={
                    multiple
                        ? 'حداقل یک تصویر الزامی است. JPG، PNG، WebP یا GIF — حداکثر ۵ مگابایت.'
                        : 'حداقل یک تصویر الزامی است. JPG، PNG، WebP یا GIF — حداکثر ۵ مگابایت.'
                }
                labels={{
                    title: 'عنوان تصویر',
                    titlePlaceholder: 'مثلاً نمای جلو محصول',
                }}
            />
        </Card>
    );
}
