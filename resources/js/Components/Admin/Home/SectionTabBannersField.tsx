import { GALLERY_IMAGE_MAX_BYTES } from '@/Components/Admin/shared/GalleryImagesField';
import { toGregorianDateOnly } from '@/Components/Admin/shared/lib/datetime-local-value';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import type { HomeSectionBannerFormRow } from '@/Components/Admin/Home/HomeSectionFormFields';
import {
    createExistingFileUploadItems,
    createFileUploadItem,
    formatFileSize,
    isImageFile,
    revokeFileUploadPreviews,
    type FileUploadItem,
} from '@/Components/shared/file-upload';
import { reorderByIndex } from '@/Components/shared/reorder-list';
import { cn } from '@/lib/utils';
import { FileUp, GripVertical, ImageIcon, Trash2, Upload } from 'lucide-react';
import { useCallback, useEffect, useId, useRef, useState } from 'react';

export function emptySectionBannerRow(): HomeSectionBannerFormRow {
    return {
        title: '',
        subtitle: '',
        button_text: 'مشاهده',
        button_link: '',
        starts_at: '',
        ends_at: '',
        image: [],
    };
}

function rowImage(row: HomeSectionBannerFormRow): FileUploadItem | undefined {
    return row.image[0];
}

function rowKey(row: HomeSectionBannerFormRow, index: number): string {
    const img = rowImage(row);

    return row.id != null ? `banner-${row.id}` : img?.id ?? `new-${index}`;
}

function rowsWithImages(rows: HomeSectionBannerFormRow[]): HomeSectionBannerFormRow[] {
    return rows.filter((row) => row.image.length > 0);
}

type Props = {
    banners: HomeSectionBannerFormRow[];
    tabIndex: number;
    disabled?: boolean;
    error?: string;
    onChange: (banners: HomeSectionBannerFormRow[]) => void;
};

const MAX_SLIDES = 20;
const ACCEPT = 'image/jpeg,image/png,image/webp,image/gif';

export function SectionTabBannersField({ banners, disabled, error, onChange }: Props) {
    const rows = rowsWithImages(banners);
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [localMessage, setLocalMessage] = useState<string | null>(null);
    const [dragIndex, setDragIndex] = useState<number | null>(null);
    const [dropIndex, setDropIndex] = useState<number | null>(null);
    const canDrag = rows.length > 1 && !disabled;
    const canAddMore = rows.length < MAX_SLIDES && !disabled;

    const rowsRef = useRef(rows);
    rowsRef.current = rows;

    useEffect(() => {
        return () => {
            for (const row of rowsRef.current) {
                revokeFileUploadPreviews(row.image);
            }
        };
    }, []);

    const updateRow = (index: number, patch: Partial<HomeSectionBannerFormRow>) => {
        const next = [...rows];
        next[index] = { ...next[index], ...patch };
        onChange(next);
    };

    const updateImageMeta = (index: number, patch: Partial<Pick<FileUploadItem, 'title' | 'alt'>>) => {
        const img = rowImage(rows[index]);
        if (!img) {
            return;
        }
        updateRow(index, { image: [{ ...img, ...patch }] });
    };

    const appendFiles = useCallback(
        (files: File[]) => {
            if (disabled || files.length === 0) {
                return;
            }

            const rejected: string[] = [];
            const newRows: HomeSectionBannerFormRow[] = [];
            let slots = MAX_SLIDES - rows.length;

            for (const file of files) {
                if (slots <= 0) {
                    rejected.push(`حداکثر ${MAX_SLIDES.toLocaleString('fa-IR')} اسلاید مجاز است.`);
                    break;
                }
                if (file.size > GALLERY_IMAGE_MAX_BYTES) {
                    rejected.push(`«${file.name}» بزرگ‌تر از ۵ مگابایت است.`);
                    continue;
                }
                if (!file.type.startsWith('image/')) {
                    rejected.push(`«${file.name}» تصویر نیست.`);
                    continue;
                }
                newRows.push({
                    ...emptySectionBannerRow(),
                    image: [createFileUploadItem(file)],
                });
                slots -= 1;
            }

            if (newRows.length > 0) {
                onChange([...rows, ...newRows]);
            }
            setLocalMessage(rejected.length > 0 ? rejected.join(' ') : null);
        },
        [disabled, onChange, rows],
    );

    const removeRow = (index: number) => {
        const removed = rows[index];
        if (removed) {
            revokeFileUploadPreviews(removed.image);
        }
        onChange(rows.filter((_, i) => i !== index));
        setLocalMessage(null);
    };

    const openPicker = () => {
        if (canAddMore) {
            inputRef.current?.click();
        }
    };

    return (
        <div className="md:col-span-2 space-y-3">
            <p className="text-sm font-medium text-slate-700 dark:text-slate-200">اسلایدهای بنر</p>

            <input
                ref={inputRef}
                id={inputId}
                type="file"
                className="sr-only"
                tabIndex={-1}
                accept={ACCEPT}
                multiple
                disabled={!canAddMore}
                onChange={(e) => {
                    const list = e.target.files;
                    if (list) {
                        appendFiles(Array.from(list));
                    }
                    e.target.value = '';
                }}
            />

            <div
                role="button"
                tabIndex={canAddMore ? 0 : -1}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openPicker();
                    }
                }}
                onClick={openPicker}
                onDragEnter={(e) => {
                    e.preventDefault();
                    if (canAddMore) {
                        setIsDragging(true);
                    }
                }}
                onDragOver={(e) => {
                    e.preventDefault();
                    if (canAddMore) {
                        setIsDragging(true);
                    }
                }}
                onDragLeave={(e) => {
                    e.preventDefault();
                    if (e.currentTarget.contains(e.relatedTarget as Node)) {
                        return;
                    }
                    setIsDragging(false);
                }}
                onDrop={(e) => {
                    e.preventDefault();
                    setIsDragging(false);
                    if (!canAddMore) {
                        return;
                    }
                    appendFiles(Array.from(e.dataTransfer.files));
                }}
                className={cn(
                    'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-6 text-center transition-colors',
                    !canAddMore && 'cursor-not-allowed opacity-60',
                    isDragging
                        ? 'border-slate-900 bg-slate-100 dark:border-slate-200 dark:bg-slate-900'
                        : 'border-slate-300 bg-slate-50 hover:border-slate-400 hover:bg-white dark:border-slate-700 dark:bg-slate-900/50',
                    error && 'border-rose-400 dark:border-rose-500',
                )}
                aria-disabled={!canAddMore}
            >
                <div className="flex h-11 w-11 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-800">
                    {isDragging ? (
                        <FileUp className="h-5 w-5 text-slate-700 dark:text-slate-200" />
                    ) : (
                        <Upload className="h-5 w-5 text-slate-700 dark:text-slate-200" />
                    )}
                </div>
                <p className="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">
                    تصاویر اسلاید را بکشید یا انتخاب کنید
                </p>
                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    چند فایل همزمان — JPG، PNG، WebP یا GIF، حداکثر ۵ مگابایت
                </p>
                <span className="mt-3 inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">
                    انتخاب فایل (چند فایل)
                </span>
            </div>

            {canDrag && (
                <p className="text-xs text-slate-500">برای تغییر ترتیب، هر ردیف را با دستگیره بکشید.</p>
            )}
            {localMessage ? (
                <p className="text-xs text-amber-600 dark:text-amber-400" role="status">{localMessage}</p>
            ) : null}
            {error ? <p className="text-sm text-red-600">{error}</p> : null}

            {rows.length > 0 ? (
                <ul className="space-y-3">
                    {rows.map((row, index) => {
                        const img = rowImage(row);
                        if (!img) {
                            return null;
                        }

                        return (
                            <li
                                key={rowKey(row, index)}
                                className={cn(
                                    'rounded-lg border bg-white p-3 dark:bg-slate-950',
                                    dragIndex === index && 'opacity-50',
                                    dropIndex === index && dragIndex !== index
                                        ? 'border-slate-900 ring-2 ring-slate-400 dark:border-slate-200'
                                        : 'border-slate-200 dark:border-slate-800',
                                )}
                                onDragOver={(e) => {
                                    if (!canDrag || dragIndex === null) {
                                        return;
                                    }
                                    e.preventDefault();
                                    setDropIndex(index);
                                }}
                                onDragLeave={() => {
                                    if (dropIndex === index) {
                                        setDropIndex(null);
                                    }
                                }}
                                onDrop={(e) => {
                                    e.preventDefault();
                                    if (dragIndex === null || dragIndex === index) {
                                        setDragIndex(null);
                                        setDropIndex(null);

                                        return;
                                    }
                                    onChange(reorderByIndex(rows, dragIndex, index));
                                    setDragIndex(null);
                                    setDropIndex(null);
                                }}
                            >
                                <div className="flex gap-3">
                                    {canDrag ? (
                                        <div
                                            draggable
                                            onDragStart={(e) => {
                                                e.stopPropagation();
                                                e.dataTransfer.effectAllowed = 'move';
                                                setDragIndex(index);
                                            }}
                                            onDragEnd={() => {
                                                setDragIndex(null);
                                                setDropIndex(null);
                                            }}
                                            className="inline-flex h-14 w-8 shrink-0 cursor-grab items-center justify-center text-slate-400 active:cursor-grabbing"
                                            aria-label={`جابه‌جایی اسلاید ${index + 1}`}
                                        >
                                            <GripVertical className="h-5 w-5" />
                                        </div>
                                    ) : null}
                                    <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md bg-slate-100 dark:bg-slate-900">
                                        {img.previewUrl ? (
                                            <img src={img.previewUrl} alt="" className="h-full w-full object-cover" />
                                        ) : img.file && isImageFile(img.file) ? (
                                            <ImageIcon className="h-6 w-6 text-slate-400" />
                                        ) : (
                                            <FileUp className="h-6 w-6 text-slate-400" />
                                        )}
                                    </div>
                                    <div className="min-w-0 flex-1">
                                        <p
                                            className="truncate text-sm font-medium text-slate-900 dark:text-slate-100"
                                            title={img.file?.name ?? img.existingName ?? ''}
                                        >
                                            {img.file?.name ?? img.existingName ?? 'تصویر اسلاید'}
                                        </p>
                                        <p className="text-xs text-slate-500 dark:text-slate-400">
                                            {formatFileSize(img.file?.size ?? img.existingSize ?? 0)}
                                            {img.existingFileId ? ' · ذخیره‌شده' : ''}
                                            {canDrag ? ` · اسلاید ${index + 1}` : ''}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        disabled={disabled}
                                        onClick={() => removeRow(index)}
                                        className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-rose-600 hover:bg-rose-50 disabled:opacity-50 dark:hover:bg-rose-500/10"
                                        title="حذف اسلاید"
                                        aria-label={`حذف اسلاید ${index + 1}`}
                                    >
                                        <Trash2 className="h-4 w-4" />
                                    </button>
                                </div>

                                <div className="mt-3 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                    <FormField label="عنوان اسلاید" className="sm:col-span-1">
                                        <AdminInput
                                            value={row.title}
                                            disabled={disabled}
                                            placeholder="عنوان روی بنر"
                                            onChange={(e) => updateRow(index, { title: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="زیرعنوان">
                                        <AdminInput
                                            value={row.subtitle}
                                            disabled={disabled}
                                            placeholder="توضیح کوتاه"
                                            onChange={(e) => updateRow(index, { subtitle: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="متن دکمه">
                                        <AdminInput
                                            value={row.button_text}
                                            disabled={disabled}
                                            placeholder="مشاهده"
                                            onChange={(e) => updateRow(index, { button_text: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="لینک دکمه">
                                        <AdminInput
                                            dir="ltr"
                                            value={row.button_link}
                                            disabled={disabled}
                                            placeholder="/courses"
                                            onChange={(e) => updateRow(index, { button_link: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="عنوان تصویر">
                                        <AdminInput
                                            value={img.title ?? ''}
                                            disabled={disabled}
                                            placeholder="عنوان فایل / SEO"
                                            onChange={(e) => updateImageMeta(index, { title: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="متن جایگزین (alt)">
                                        <AdminInput
                                            value={img.alt ?? ''}
                                            disabled={disabled}
                                            placeholder="توضیح تصویر"
                                            onChange={(e) => updateImageMeta(index, { alt: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="شروع نمایش">
                                        <PersianDatePicker
                                            includeTime
                                            value={row.starts_at}
                                            disabled={disabled}
                                            placeholder="اختیاری"
                                            placement="top"
                                            clearable
                                            onChange={(v) => updateRow(index, { starts_at: v })}
                                        />
                                    </FormField>
                                    <FormField label="پایان نمایش">
                                        <PersianDatePicker
                                            includeTime
                                            value={row.ends_at}
                                            disabled={disabled}
                                            min={row.starts_at ? toGregorianDateOnly(row.starts_at) : undefined}
                                            placeholder="اختیاری"
                                            placement="top"
                                            clearable
                                            onChange={(v) => updateRow(index, { ends_at: v })}
                                        />
                                    </FormField>
                                </div>
                            </li>
                        );
                    })}
                </ul>
            ) : null}

            <p className="text-xs text-slate-500">
                بنرهای این سکشن فقط در صفحه اصلی استفاده می‌شوند. جزئیات هر اسلاید را در همان ردیف تصویر وارد کنید.
            </p>
        </div>
    );
}

export function bannerRowsFromPresenter(
    banners: Array<{
        id?: number;
        title?: string;
        subtitle?: string;
        button_text?: string;
        button_link?: string;
        starts_at?: string;
        ends_at?: string;
        image?: { id: number; url: string; name?: string; size?: number; title?: string | null; alt?: string | null } | null;
    }>,
): HomeSectionBannerFormRow[] {
    if (!banners.length) {
        return [];
    }

    return banners
        .map((banner) => ({
            id: banner.id,
            title: banner.title ?? '',
            subtitle: banner.subtitle ?? '',
            button_text: banner.button_text ?? 'مشاهده',
            button_link: banner.button_link ?? '',
            starts_at: banner.starts_at ?? '',
            ends_at: banner.ends_at ?? '',
            image: banner.image
                ? createExistingFileUploadItems([
                      {
                          id: banner.image.id,
                          url: banner.image.url,
                          name: banner.image.name ?? 'image',
                          size: banner.image.size ?? 0,
                          title: banner.image.title,
                          alt: banner.image.alt,
                      },
                  ])
                : [],
        }))
        .filter((row) => row.image.length > 0);
}
