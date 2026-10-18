import type { HomeSectionTabForm } from '@/Components/Admin/Home/HomeSectionFormFields';
import { GALLERY_IMAGE_MAX_BYTES } from '@/Components/Admin/shared/GalleryImagesField';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import {
    createExistingFileUploadItems,
    createFileUploadItem,
    formatFileSize,
    isImageFile,
    revokeFileUploadPreviews,
    type FileUploadItem,
} from '@/Components/shared/file-upload';
import { cn } from '@/lib/utils';
import { FileUp, ImageIcon, Trash2, Upload } from 'lucide-react';
import { useCallback, useEffect, useId, useRef, useState } from 'react';
import { FEATURES_GRID_COLUMNS } from '@/Components/Admin/Home/home-section-templates';

export type FeatureGridRow = {
    label: string;
    text: string;
    image_url?: string | null;
    image_alt?: string | null;
    image_title?: string | null;
    image?: FileUploadItem[];
};

export function defaultFeatureGridRows(): FeatureGridRow[] {
    return Array.from({ length: FEATURES_GRID_COLUMNS }, () => ({
        label: '',
        text: '',
        image_url: '',
        image: [],
    }));
}

export function featureGridRowsFromSettings(rows: Partial<FeatureGridRow>[] | undefined): FeatureGridRow[] {
    const padded = [...(rows ?? [])];
    while (padded.length < FEATURES_GRID_COLUMNS) {
        padded.push({ label: '', text: '', image_url: '' });
    }

    return padded.slice(0, FEATURES_GRID_COLUMNS).map((row, index) => {
        const pendingUpload = Array.isArray(row.image) && row.image.length > 0 ? row.image : null;

        return {
            label: row.label ?? '',
            text: row.text ?? '',
            image_url: row.image_url ?? '',
            image:
                pendingUpload ??
                (row.image_url
                    ? createExistingFileUploadItems([
                          {
                              id: 900_000 + index,
                              url: row.image_url,
                              name: 'image',
                              title: row.image_title ?? '',
                              alt: row.image_alt ?? '',
                          },
                      ])
                    : []),
        };
    });
}

function rowImage(row: FeatureGridRow): FileUploadItem | undefined {
    return row.image?.[0];
}

function filledImageCount(rows: FeatureGridRow[]): number {
    return rows.filter((row) => (row.image ?? []).length > 0).length;
}

type Props = {
    tab: HomeSectionTabForm;
    disabled?: boolean;
    error?: string;
    onChange: (settings: HomeSectionTabForm['settings']) => void;
};

const ACCEPT = 'image/jpeg,image/png,image/webp,image/gif';

export function SectionTabFeaturesGridField({ tab, disabled, error, onChange }: Props) {
    const features = featureGridRowsFromSettings(tab.settings.features as FeatureGridRow[] | undefined);
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [localMessage, setLocalMessage] = useState<string | null>(null);

    const featuresRef = useRef(features);
    featuresRef.current = features;

    useEffect(() => {
        return () => {
            for (const row of featuresRef.current) {
                revokeFileUploadPreviews(row.image ?? []);
            }
        };
    }, []);

    const updateRows = (next: FeatureGridRow[]) => {
        onChange({ ...tab.settings, features: next });
    };

    const updateRow = (index: number, patch: Partial<FeatureGridRow>) => {
        const next = [...features];
        next[index] = { ...next[index], ...patch };
        updateRows(next);
    };

    const updateImageMeta = (index: number, patch: Partial<Pick<FileUploadItem, 'title' | 'alt'>>) => {
        const img = rowImage(features[index]);
        if (!img) {
            return;
        }
        updateRow(index, { image: [{ ...img, ...patch }] });
    };

    const canAddMore = filledImageCount(features) < FEATURES_GRID_COLUMNS && !disabled;

    const appendFiles = useCallback(
        (files: File[]) => {
            if (disabled || files.length === 0) {
                return;
            }

            const rejected: string[] = [];
            const next = [...features];
            let slots = FEATURES_GRID_COLUMNS - filledImageCount(next);
            let changed = false;

            for (const file of files) {
                if (slots <= 0) {
                    rejected.push(`حداکثر ${FEATURES_GRID_COLUMNS.toLocaleString('fa-IR')} تصویر مجاز است.`);
                    break;
                }

                const emptyIndex = next.findIndex((row) => !(row.image ?? []).length);
                if (emptyIndex === -1) {
                    rejected.push(`حداکثر ${FEATURES_GRID_COLUMNS.toLocaleString('fa-IR')} تصویر مجاز است.`);
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

                next[emptyIndex] = {
                    ...next[emptyIndex],
                    image: [createFileUploadItem(file)],
                    image_url: '',
                };
                slots -= 1;
                changed = true;
            }

            if (changed) {
                updateRows(next);
            }
            setLocalMessage(rejected.length > 0 ? rejected.join(' ') : null);
        },
        [disabled, features],
    );

    const clearImage = (index: number) => {
        const row = features[index];
        if (row?.image?.length) {
            revokeFileUploadPreviews(row.image);
        }
        updateRow(index, { image: [], image_url: '', image_alt: '', image_title: '' });
        setLocalMessage(null);
    };

    const openPicker = () => {
        if (canAddMore) {
            inputRef.current?.click();
        }
    };

    return (
        <div className="md:col-span-2 space-y-4">
            <p className="text-sm font-medium text-slate-700 dark:text-slate-200">
                گرید سه‌تایی — حداکثر {FEATURES_GRID_COLUMNS} تصویر (یک‌بار چند فایل انتخاب کنید)
            </p>
            <p className="text-xs text-slate-500 dark:text-slate-400">
                این قالب با «اسلایدر بنر» یا «هیرو» متفاوت است؛ تصاویر را از باکس زیر بارگذاری کنید، نه از بخش اسلاید بنر.
            </p>

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
                    'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-5 text-center transition-colors',
                    !canAddMore && 'cursor-not-allowed opacity-60',
                    isDragging
                        ? 'border-slate-900 bg-slate-100 dark:border-slate-200 dark:bg-slate-900'
                        : 'border-slate-300 bg-slate-50 hover:border-slate-400 hover:bg-white dark:border-slate-700 dark:bg-slate-900/50',
                )}
                aria-disabled={!canAddMore}
            >
                <div className="flex h-10 w-10 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-800">
                    {isDragging ? (
                        <FileUp className="h-5 w-5 text-slate-700 dark:text-slate-200" />
                    ) : (
                        <Upload className="h-5 w-5 text-slate-700 dark:text-slate-200" />
                    )}
                </div>
                <p className="mt-2 text-sm font-medium text-slate-900 dark:text-slate-100">
                    تصاویر ستون‌ها را بکشید یا انتخاب کنید
                </p>
                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">
                    تا {FEATURES_GRID_COLUMNS.toLocaleString('fa-IR')} فایل همزمان — JPG، PNG، WebP یا GIF، حداکثر ۵
                    مگابایت
                </p>
                <span className="mt-2 inline-flex h-8 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200">
                    انتخاب فایل (چند فایل)
                </span>
            </div>

            {localMessage ? (
                <p className="text-xs text-amber-600 dark:text-amber-400" role="status">{localMessage}</p>
            ) : null}
            {error ? <p className="text-sm text-red-600 dark:text-red-400" role="alert">{error}</p> : null}

            <div className="grid gap-3 md:grid-cols-3">
                {features.map((feature, featureIndex) => {
                    const img = rowImage(feature);

                    return (
                        <div
                            key={featureIndex}
                            className="rounded-lg border border-slate-200 p-3 dark:border-slate-700"
                        >
                            <div className="mb-2 flex items-center justify-between gap-2">
                                <p className="text-xs font-medium text-slate-500">ستون {featureIndex + 1}</p>
                                {img ? (
                                    <button
                                        type="button"
                                        disabled={disabled}
                                        onClick={() => clearImage(featureIndex)}
                                        className="inline-flex h-7 w-7 items-center justify-center rounded-md text-rose-600 hover:bg-rose-50 disabled:opacity-50 dark:hover:bg-rose-500/10"
                                        title="حذف تصویر"
                                        aria-label={`حذف تصویر ستون ${featureIndex + 1}`}
                                    >
                                        <Trash2 className="h-3.5 w-3.5" />
                                    </button>
                                ) : null}
                            </div>
                            <div className="mb-3 flex aspect-4/3 items-center justify-center overflow-hidden rounded-md bg-slate-100 dark:bg-slate-900">
                                {img?.previewUrl ? (
                                    <img src={img.previewUrl} alt="" className="size-full object-cover" />
                                ) : img?.file && isImageFile(img.file) ? (
                                    <ImageIcon className="h-8 w-8 text-slate-400" />
                                ) : (
                                    <p className="px-2 text-center text-[11px] text-slate-400">بدون تصویر</p>
                                )}
                            </div>
                            {img ? (
                                <p className="mb-2 truncate text-[10px] text-slate-500" title={img.file?.name ?? ''}>
                                    {img.file?.name ?? img.existingName ?? 'تصویر'}
                                    {img.file?.size || img.existingSize
                                        ? ` · ${formatFileSize(img.file?.size ?? img.existingSize ?? 0)}`
                                        : ''}
                                </p>
                            ) : null}
                            {img ? (
                                <div className="mb-2 grid gap-2">
                                    <FormField label="عنوان تصویر">
                                        <AdminInput
                                            value={img.title ?? ''}
                                            disabled={disabled}
                                            placeholder="عنوان فایل / SEO"
                                            onChange={(e) => updateImageMeta(featureIndex, { title: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="متن جایگزین (alt)">
                                        <AdminInput
                                            value={img.alt ?? ''}
                                            disabled={disabled}
                                            placeholder="توضیح تصویر"
                                            onChange={(e) => updateImageMeta(featureIndex, { alt: e.target.value })}
                                        />
                                    </FormField>
                                </div>
                            ) : null}
                            <div className="grid gap-2">
                                <FormField label="عنوان ستون">
                                    <AdminInput
                                        value={feature.label}
                                        disabled={disabled}
                                        onChange={(e) => updateRow(featureIndex, { label: e.target.value })}
                                    />
                                </FormField>
                                <FormField label="توضیح">
                                    <AdminInput
                                        value={feature.text}
                                        disabled={disabled}
                                        onChange={(e) => updateRow(featureIndex, { text: e.target.value })}
                                    />
                                </FormField>
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
