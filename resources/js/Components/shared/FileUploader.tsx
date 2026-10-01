import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import {
    createFileUploadItem,
    createFileUploadItems,
    defaultFileUploaderLabels,
    formatFileSize,
    isImageFile,
    revokeFileUploadPreviews,
    type FileUploadItem,
    type FileUploaderLabels,
} from '@/Components/shared/file-upload';
import { cn } from '@/lib/utils';
import { FileUp, ImageIcon, Trash2, Upload } from 'lucide-react';
import { useCallback, useEffect, useId, useRef, useState, type Dispatch, type SetStateAction } from 'react';

export function useFileUploadState(
    initialItems: FileUploadItem[] = [],
): [FileUploadItem[], Dispatch<SetStateAction<FileUploadItem[]>>] {
    const [items, setItems] = useState(initialItems);
    const itemsRef = useRef(items);
    itemsRef.current = items;

    useEffect(() => {
        return () => revokeFileUploadPreviews(itemsRef.current);
    }, []);

    return [items, setItems];
}

export type FileUploaderProps = {
    value: FileUploadItem[];
    onChange: (items: FileUploadItem[]) => void;
    /** چند فایل همزمان */
    multiple?: boolean;
    /** فیلد عنوان برای هر فایل */
    showTitle?: boolean;
    /** فیلد alt (معمولاً برای تصویر) */
    showAlt?: boolean;
    accept?: string;
    maxFiles?: number;
    maxSizeBytes?: number;
    disabled?: boolean;
    className?: string;
    labels?: Partial<FileUploaderLabels>;
    error?: string;
    hint?: string;
};

function mergeIncomingFiles(
    current: FileUploadItem[],
    incoming: File[],
    options: {
        multiple: boolean;
        maxFiles?: number;
        maxSizeBytes?: number;
    },
): { items: FileUploadItem[]; rejected: string[] } {
    const rejected: string[] = [];
    let allowed = incoming;

    if (options.maxSizeBytes) {
        const tooLarge = allowed.filter((f) => f.size > options.maxSizeBytes!);
        for (const file of tooLarge) {
            rejected.push(`«${file.name}» بزرگ‌تر از حد مجاز است.`);
        }
        allowed = allowed.filter((f) => f.size <= options.maxSizeBytes!);
    }

    if (!options.multiple) {
        revokeFileUploadPreviews(current);
        const first = allowed[0];
        if (!first) {
            return { items: [], rejected };
        }
        if (allowed.length > 1) {
            rejected.push('در حالت تک‌فایل فقط اولین فایل پذیرفته شد.');
        }

        return { items: [createFileUploadItem(first)], rejected };
    }

    const merged = [...current, ...createFileUploadItems(allowed)];
    const max = options.maxFiles ?? Infinity;

    if (merged.length > max) {
        const extra = merged.splice(max);
        revokeFileUploadPreviews(extra);
        rejected.push(`حداکثر ${max.toLocaleString('fa-IR')} فایل مجاز است.`);
    }

    return { items: merged, rejected };
}

export function FileUploader({
    value,
    onChange,
    multiple = false,
    showTitle = false,
    showAlt = false,
    accept,
    maxFiles,
    maxSizeBytes,
    disabled = false,
    className,
    labels: labelsOverride,
    error,
    hint,
}: FileUploaderProps) {
    const labels = { ...defaultFileUploaderLabels, ...labelsOverride };
    const inputId = useId();
    const inputRef = useRef<HTMLInputElement>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [localMessage, setLocalMessage] = useState<string | null>(null);

    const valueRef = useRef(value);
    valueRef.current = value;

    useEffect(() => {
        return () => revokeFileUploadPreviews(valueRef.current);
    }, []);

    const applyFiles = useCallback(
        (files: File[]) => {
            if (disabled || files.length === 0) {
                return;
            }

            const { items, rejected } = mergeIncomingFiles(value, files, {
                multiple,
                maxFiles,
                maxSizeBytes,
            });

            onChange(items);
            setLocalMessage(rejected.length > 0 ? rejected.join(' ') : null);
        },
        [disabled, maxFiles, maxSizeBytes, multiple, onChange, value],
    );

    const onInputChange = (event: React.ChangeEvent<HTMLInputElement>) => {
        const files = event.target.files;
        if (files) {
            applyFiles(Array.from(files));
        }
        event.target.value = '';
    };

    const onDrop = (event: React.DragEvent) => {
        event.preventDefault();
        setIsDragging(false);
        if (disabled) {
            return;
        }
        applyFiles(Array.from(event.dataTransfer.files));
    };

    const removeItem = (id: string) => {
        const removed = value.find((item) => item.id === id);
        if (removed) {
            revokeFileUploadPreviews([removed]);
        }
        onChange(value.filter((item) => item.id !== id));
    };

    const updateMeta = (id: string, patch: Partial<Pick<FileUploadItem, 'title' | 'alt'>>) => {
        onChange(value.map((item) => (item.id === id ? { ...item, ...patch } : item)));
    };

    const openPicker = () => {
        if (!disabled) {
            inputRef.current?.click();
        }
    };

    const showMetaFields = showTitle || showAlt;

    return (
        <div className={cn('space-y-3', className)}>
            <input
                ref={inputRef}
                id={inputId}
                type="file"
                className="sr-only"
                tabIndex={-1}
                accept={accept}
                multiple={multiple}
                disabled={disabled}
                onChange={onInputChange}
            />

            <div
                role="button"
                tabIndex={disabled ? -1 : 0}
                onKeyDown={(e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        openPicker();
                    }
                }}
                onClick={openPicker}
                onDragEnter={(e) => {
                    e.preventDefault();
                    if (!disabled) {
                        setIsDragging(true);
                    }
                }}
                onDragOver={(e) => {
                    e.preventDefault();
                    if (!disabled) {
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
                onDrop={onDrop}
                className={cn(
                    'flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed px-4 py-8 text-center transition-colors',
                    disabled && 'cursor-not-allowed opacity-60',
                    isDragging
                        ? 'border-slate-900 bg-slate-100 dark:border-slate-200 dark:bg-slate-900'
                        : 'border-slate-300 bg-slate-50 hover:border-slate-400 hover:bg-white dark:border-slate-700 dark:bg-slate-900/50 dark:hover:border-slate-500',
                    error && 'border-rose-400 dark:border-rose-500',
                )}
                aria-disabled={disabled}
                aria-describedby={hint ? `${inputId}-hint` : undefined}
            >
                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-white shadow-sm ring-1 ring-slate-200 dark:bg-slate-950 dark:ring-slate-800">
                    {isDragging ? (
                        <FileUp className="h-6 w-6 text-slate-700 dark:text-slate-200" />
                    ) : (
                        <Upload className="h-6 w-6 text-slate-700 dark:text-slate-200" />
                    )}
                </div>
                <p className="mt-3 text-sm font-medium text-slate-900 dark:text-slate-100">{labels.dropzoneTitle}</p>
                <p className="mt-1 text-xs text-slate-500 dark:text-slate-400">{labels.dropzoneHint}</p>
                <span
                    className="mt-4 inline-flex h-9 items-center rounded-md border border-slate-300 bg-white px-3 text-xs font-medium text-slate-700 dark:border-slate-600 dark:bg-slate-950 dark:text-slate-200"
                >
                    {labels.browse}
                    {multiple ? ' (چند فایل)' : ' (تک فایل)'}
                </span>
            </div>

            {hint ? (
                <p id={`${inputId}-hint`} className="text-xs text-slate-500 dark:text-slate-400">{hint}</p>
            ) : null}
            {localMessage ? (
                <p className="text-xs text-amber-600 dark:text-amber-400" role="status">{localMessage}</p>
            ) : null}
            {error ? <p className="text-xs text-rose-500 dark:text-rose-400">{error}</p> : null}

            {value.length > 0 ? (
                <ul className="space-y-3">
                    {value.map((item) => (
                        <li
                            key={item.id}
                            className="rounded-lg border border-slate-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-950"
                        >
                            <div className="flex gap-3">
                                <div className="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-md bg-slate-100 dark:bg-slate-900">
                                    {item.previewUrl ? (
                                        <img src={item.previewUrl} alt="" className="h-full w-full object-cover" />
                                    ) : isImageFile(item.file) ? (
                                        <ImageIcon className="h-6 w-6 text-slate-400" />
                                    ) : (
                                        <FileUp className="h-6 w-6 text-slate-400" />
                                    )}
                                </div>
                                <div className="min-w-0 flex-1">
                                    <p className="truncate text-sm font-medium text-slate-900 dark:text-slate-100" title={item.file.name}>
                                        {item.file.name}
                                    </p>
                                    <p className="text-xs text-slate-500 dark:text-slate-400">
                                        {formatFileSize(item.file.size)}
                                        {item.file.type ? ` · ${item.file.type}` : ''}
                                    </p>
                                </div>
                                <button
                                    type="button"
                                    disabled={disabled}
                                    onClick={() => removeItem(item.id)}
                                    className="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-md text-rose-600 hover:bg-rose-50 disabled:opacity-50 dark:hover:bg-rose-500/10"
                                    title={labels.remove}
                                    aria-label={`${labels.remove} ${item.file.name}`}
                                >
                                    <Trash2 className="h-4 w-4" />
                                </button>
                            </div>

                            {showMetaFields ? (
                                <div className="mt-3 grid gap-3 sm:grid-cols-2">
                                    {showTitle ? (
                                        <FormField label={labels.title!} htmlFor={`${item.id}-title`}>
                                            <AdminInput
                                                id={`${item.id}-title`}
                                                value={item.title ?? ''}
                                                onChange={(e) => updateMeta(item.id, { title: e.target.value })}
                                                placeholder={labels.titlePlaceholder}
                                                disabled={disabled}
                                                onClick={(e) => e.stopPropagation()}
                                            />
                                        </FormField>
                                    ) : null}
                                    {showAlt ? (
                                        <FormField label={labels.alt!} htmlFor={`${item.id}-alt`}>
                                            <AdminInput
                                                id={`${item.id}-alt`}
                                                value={item.alt ?? ''}
                                                onChange={(e) => updateMeta(item.id, { alt: e.target.value })}
                                                placeholder={labels.altPlaceholder}
                                                disabled={disabled}
                                                onClick={(e) => e.stopPropagation()}
                                            />
                                        </FormField>
                                    ) : null}
                                </div>
                            ) : null}
                        </li>
                    ))}
                </ul>
            ) : null}
        </div>
    );
}

export type { FileUploadItem, FileUploaderLabels } from '@/Components/shared/file-upload';
