import { Card } from '@/Components/Admin/shared/ui/card';
import { FileUploader, useFileUploadState } from '@/Components/shared/FileUploader';
import { cn } from '@/lib/utils';
import { UserRound } from 'lucide-react';
import { useEffect } from 'react';

export const USER_AVATAR_MAX_BYTES = 2 * 1024 * 1024;

export type ExistingAvatar = {
    url: string;
    alt?: string | null;
    title?: string | null;
};

type Props = {
    files: ReturnType<typeof useFileUploadState>[0];
    onFilesChange: ReturnType<typeof useFileUploadState>[1];
    existing?: ExistingAvatar | null;
    removeExisting?: boolean;
    onRemoveExistingChange?: (remove: boolean) => void;
    disabled?: boolean;
    error?: string;
    showTitle?: boolean;
    showAlt?: boolean;
};

export function UserAvatarField({
    files,
    onFilesChange,
    existing,
    removeExisting = false,
    onRemoveExistingChange,
    disabled,
    error,
    showTitle = false,
    showAlt = true,
}: Props) {
    const showCurrent = existing?.url && !removeExisting && files.length === 0;

    useEffect(() => {
        if (files.length > 0 && onRemoveExistingChange) {
            onRemoveExistingChange(false);
        }
    }, [files.length, onRemoveExistingChange]);

    return (
        <Card className="space-y-4">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">آواتار</h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    تصویر مربعی؛ حداکثر ۲ مگابایت (JPG، PNG، WebP، GIF)
                </p>
            </div>

            {showCurrent ? (
                <div className="flex items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-900/40">
                    <img
                        src={existing.url}
                        alt={existing.alt ?? 'آواتار فعلی'}
                        className="h-16 w-16 rounded-full object-cover ring-2 ring-white dark:ring-slate-800"
                    />
                    <div className="min-w-0 flex-1 text-sm">
                        <p className="font-medium text-slate-900 dark:text-slate-100">آواتار فعلی</p>
                        {existing.alt ? (
                            <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">alt: {existing.alt}</p>
                        ) : null}
                    </div>
                    {onRemoveExistingChange ? (
                        <button
                            type="button"
                            disabled={disabled}
                            onClick={() => onRemoveExistingChange(true)}
                            className={cn(
                                'text-xs font-medium text-rose-600 hover:underline',
                                disabled && 'opacity-50',
                            )}
                        >
                            حذف آواتار
                        </button>
                    ) : null}
                </div>
            ) : (
                <div className="flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                    <UserRound className="h-8 w-8" />
                </div>
            )}

            {removeExisting && files.length === 0 ? (
                <p className="text-xs text-amber-600 dark:text-amber-400">
                    با ذخیره فرم، آواتار فعلی حذف می‌شود. برای لغو، تصویر جدید انتخاب کنید یا صفحه را رفرش کنید.
                </p>
            ) : null}

            <FileUploader
                value={files}
                onChange={onFilesChange}
                multiple={false}
                showTitle={showTitle}
                showAlt={showAlt}
                accept="image/jpeg,image/png,image/webp,image/gif"
                maxSizeBytes={USER_AVATAR_MAX_BYTES}
                disabled={disabled}
                error={error}
                hint="یک تصویر انتخاب کنید یا بکشید و رها کنید."
                labels={{
                    dropzoneTitle: 'تصویر آواتار را اینجا بکشید',
                    dropzoneHint: 'یا کلیک کنید — حداکثر ۲ مگابایت',
                }}
            />
        </Card>
    );
}
