import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { useEffect, useMemo, useState } from 'react';

export type StoryFormData = {
    title: string;
    type: string;
    content: string;
    background_color: string;
    visibility: string;
    display_starts_at: string;
    display_ends_at: string;
    publish_now: boolean;
    media: File | null;
    thumbnail: File | null;
};

type Props = {
    data: StoryFormData;
    errors: Record<string, string>;
    typeOptions: { value: string; label: string }[];
    visibilityOptions: { value: string; label: string }[];
    processing: boolean;
    existingFileUrl?: string | null;
    existingThumbnailUrl?: string | null;
    statusLabel?: string | null;
    publishedAt?: string | null;
    expiresAt?: string | null;
    mediaLimits?: { max_video_mb: number; max_image_mb: number };
    onChange: (key: keyof StoryFormData, value: StoryFormData[keyof StoryFormData]) => void;
};

export function StoryFormFields({
    data,
    errors,
    typeOptions,
    visibilityOptions,
    processing,
    existingFileUrl,
    existingThumbnailUrl,
    statusLabel,
    publishedAt,
    expiresAt,
    mediaLimits = { max_video_mb: 5, max_image_mb: 10 },
    onChange,
}: Props) {
    const [localMediaError, setLocalMediaError] = useState<string | null>(null);
    const typeSelectOptions = mapToSelectOptions(typeOptions);
    const visibilitySelectOptions = mapToSelectOptions(visibilityOptions);

    const showTextFields = data.type === 'text';
    const showMedia = data.type === 'image' || data.type === 'video';

    const localMediaPreview = useMemo(
        () => (data.media ? URL.createObjectURL(data.media) : null),
        [data.media],
    );

    const localThumbnailPreview = useMemo(
        () => (data.thumbnail ? URL.createObjectURL(data.thumbnail) : null),
        [data.thumbnail],
    );

    useEffect(() => {
        return () => {
            if (localMediaPreview) {
                URL.revokeObjectURL(localMediaPreview);
            }
            if (localThumbnailPreview) {
                URL.revokeObjectURL(localThumbnailPreview);
            }
        };
    }, [localMediaPreview, localThumbnailPreview]);

    const mediaPreviewUrl = localMediaPreview ?? existingFileUrl;
    const thumbnailPreviewUrl = localThumbnailPreview ?? existingThumbnailUrl;

    return (
        <Card className="space-y-4 p-6">
            {statusLabel && (
                <div className="flex flex-wrap items-center gap-2 text-sm text-slate-600">
                    <span>وضعیت فعلی:</span>
                    <Badge variant="secondary">{statusLabel}</Badge>
                    {publishedAt && <span>انتشار: {publishedAt}</span>}
                    {expiresAt && <span>انقضا: {expiresAt}</span>}
                </div>
            )}

            <FormField label="عنوان استوری" required error={errors.title}>
                <AdminInput
                    value={data.title}
                    disabled={processing}
                    onChange={(e) => onChange('title', e.target.value)}
                    placeholder="مثلاً تخفیف ویژه نوروز"
                />
            </FormField>

            <FormField
                label="تصویر پیش‌نمایش (thumbnail)"
                error={errors.thumbnail}
                hint="در نوار استوری‌های صفحهٔ اصلی داخل حلقه نمایش داده می‌شود. برای ویدیو توصیه می‌شود."
            >
                <input
                    type="file"
                    accept="image/*"
                    disabled={processing}
                    onChange={(e) => onChange('thumbnail', e.target.files?.[0] ?? null)}
                    className="block w-full text-sm"
                />
            </FormField>

            {thumbnailPreviewUrl && (
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                    <img src={thumbnailPreviewUrl} alt="" className="mx-auto max-h-40 object-contain" />
                </div>
            )}

            <div className="grid gap-4 md:grid-cols-2">
                <FormField label="نوع استوری" required error={errors.type}>
                    <Select
                        isDisabled={processing}
                        options={typeSelectOptions}
                        value={findSelectOption(typeSelectOptions, data.type)}
                        onChange={(option) => onChange('type', option?.value ?? 'text')}
                    />
                </FormField>

                <FormField label="نمایش برای" required error={errors.visibility}>
                    <Select
                        isDisabled={processing}
                        options={visibilitySelectOptions}
                        value={findSelectOption(visibilitySelectOptions, data.visibility)}
                        onChange={(option) => onChange('visibility', option?.value ?? 'authenticated')}
                    />
                </FormField>

                <FormField label="شروع نمایش" required error={errors.display_starts_at}>
                    <PersianDatePicker
                        value={data.display_starts_at}
                        includeTime
                        disabled={processing}
                        placeholder="تاریخ و ساعت شروع"
                        onChange={(v) => onChange('display_starts_at', v)}
                    />
                </FormField>

                <FormField
                    label="پایان نمایش"
                    error={errors.display_ends_at}
                    hint="خالی = نامحدود"
                >
                    <PersianDatePicker
                        value={data.display_ends_at}
                        includeTime
                        disabled={processing}
                        clearable
                        placeholder="اختیاری — نامحدود"
                        onChange={(v) => onChange('display_ends_at', v)}
                    />
                </FormField>

                <FormField className="md:col-span-2" error={errors.publish_now}>
                    <Switch
                        checked={data.publish_now}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('publish_now', v)}
                        label="منتشرشده"
                        description="در بازهٔ زمانی بالا در سایت دیده می‌شود"
                    />
                </FormField>
            </div>

            {showTextFields && (
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField className="md:col-span-2" label="متن استوری" required error={errors.content}>
                        <Textarea
                            plain
                            rows={4}
                            value={data.content}
                            disabled={processing}
                            onChange={(e) => onChange('content', e.target.value)}
                        />
                    </FormField>
                    <FormField label="رنگ پس‌زمینه" error={errors.background_color}>
                        <div className="flex items-center gap-3">
                            <input
                                type="color"
                                value={data.background_color || '#0f766e'}
                                disabled={processing}
                                onChange={(e) => onChange('background_color', e.target.value)}
                                className="size-10 cursor-pointer rounded border border-slate-200"
                            />
                            <AdminInput
                                dir="ltr"
                                value={data.background_color}
                                disabled={processing}
                                onChange={(e) => onChange('background_color', e.target.value)}
                            />
                        </div>
                    </FormField>
                </div>
            )}

            {showMedia && (
                <div className="space-y-3">
                    <FormField
                        label={data.type === 'video' ? 'فایل ویدیو' : 'تصویر'}
                        required={!existingFileUrl}
                        error={errors.media ?? localMediaError ?? undefined}
                    >
                        <input
                            type="file"
                            accept={
                                data.type === 'video'
                                    ? 'video/mp4,video/webm,video/quicktime,.mp4,.webm,.mov,.m4v'
                                    : 'image/*'
                            }
                            disabled={processing}
                            onChange={(e) => {
                                const file = e.target.files?.[0] ?? null;
                                const maxMb =
                                    data.type === 'video' ? mediaLimits.max_video_mb : mediaLimits.max_image_mb;
                                const maxBytes = maxMb * 1024 * 1024;

                                if (file !== null && file.size > maxBytes) {
                                    setLocalMediaError(
                                        data.type === 'video'
                                            ? `حداکثر حجم ویدیو ${mediaLimits.max_video_mb} مگابایت است.`
                                            : `حداکثر حجم تصویر ${mediaLimits.max_image_mb} مگابایت است.`,
                                    );
                                    onChange('media', null);
                                    e.target.value = '';

                                    return;
                                }

                                setLocalMediaError(null);
                                onChange('media', file);
                            }}
                            className="block w-full text-sm"
                        />
                        {data.type === 'video' && (
                            <p className="mt-1 text-xs text-slate-500">
                                MP4 یا WebM — حداکثر {mediaLimits.max_video_mb} مگابایت.
                            </p>
                        )}
                    </FormField>
                    <FormField label="کپشن (اختیاری)" error={errors.content}>
                        <Textarea
                            plain
                            rows={2}
                            value={data.content}
                            disabled={processing}
                            onChange={(e) => onChange('content', e.target.value)}
                            placeholder="متن کوتاه زیر تصویر یا ویدیو"
                        />
                    </FormField>
                </div>
            )}

            {mediaPreviewUrl && showMedia && (
                <div className="overflow-hidden rounded-xl border border-slate-200 bg-slate-50 p-2">
                    {data.type === 'video' ? (
                        <video src={mediaPreviewUrl} controls className="mx-auto max-h-64 w-full object-contain" />
                    ) : (
                        <img src={mediaPreviewUrl} alt="" className="mx-auto max-h-64 object-contain" />
                    )}
                </div>
            )}
        </Card>
    );
}
