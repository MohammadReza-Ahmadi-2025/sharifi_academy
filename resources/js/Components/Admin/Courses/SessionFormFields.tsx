import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { toGregorianDateOnly } from '@/Components/Admin/shared/lib/datetime-local-value';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type SessionData = {
    session_number: number;
    title: string;
    description: string;
    video_url?: string;
    status: string;
    scheduled_start_at: string;
    scheduled_end_at: string;
    timezone?: string;
    actual_start_at?: string;
    actual_end_at?: string;
    cancelled_at?: string;
    cancellation_reason?: string;
};

type Props = {
    data: SessionData;
    errors: Record<string, string>;
    statusOptions: { value: string; label: string }[];
    processing?: boolean;
    showVideoUrl?: boolean;
    onChange: (key: keyof SessionData, value: unknown) => void;
};

export function SessionFormFields({
    data,
    errors,
    statusOptions,
    processing = false,
    showVideoUrl = false,
    onChange,
}: Props) {
    const statusSelectOptions = mapToSelectOptions(statusOptions);
    const showActual = data.actual_start_at !== undefined || data.actual_end_at !== undefined;
    const showCancelled = data.cancelled_at !== undefined;

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">جلسه</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="شماره جلسه" htmlFor="session-number" required error={errors.session_number}>
                        <AdminInput
                            id="session-number"
                            type="number"
                            min={1}
                            value={data.session_number}
                            disabled={processing}
                            onChange={(e) => onChange('session_number', Number(e.target.value))}
                        />
                    </FormField>
                    <FormField label="وضعیت" required error={errors.status}>
                        <Select
                            isDisabled={processing}
                            options={statusSelectOptions}
                            value={findSelectOption(statusSelectOptions, data.status)}
                            onChange={(option) => onChange('status', option?.value ?? '')}
                        />
                    </FormField>
                    <FormField label="عنوان" htmlFor="session-title" className="md:col-span-2" error={errors.title}>
                        <AdminInput
                            id="session-title"
                            value={data.title}
                            disabled={processing}
                            onChange={(e) => onChange('title', e.target.value)}
                        />
                    </FormField>
                    <FormField label="شروع برنامه‌ریزی‌شده" error={errors.scheduled_start_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.scheduled_start_at}
                            disabled={processing}
                            placeholder="تاریخ و زمان شروع"
                            clearable
                            onChange={(v) => onChange('scheduled_start_at', v)}
                        />
                    </FormField>
                    <FormField label="پایان برنامه‌ریزی‌شده" error={errors.scheduled_end_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.scheduled_end_at}
                            disabled={processing}
                            min={data.scheduled_start_at ? toGregorianDateOnly(data.scheduled_start_at) : undefined}
                            placeholder="تاریخ و زمان پایان"
                            clearable
                            onChange={(v) => onChange('scheduled_end_at', v)}
                        />
                    </FormField>
                    {data.timezone !== undefined && (
                        <FormField label="منطقه زمانی" htmlFor="session-tz" className="md:col-span-2" error={errors.timezone}>
                            <AdminInput
                                id="session-tz"
                                dir="ltr"
                                className="text-left font-mono"
                                value={data.timezone ?? ''}
                                disabled={processing}
                                onChange={(e) => onChange('timezone', e.target.value)}
                            />
                        </FormField>
                    )}
                    <FormField label="توضیحات" htmlFor="session-desc" className="md:col-span-2" error={errors.description}>
                        <Textarea
                            id="session-desc"
                            rows={3}
                            value={data.description}
                            disabled={processing}
                            onChange={(e) => onChange('description', e.target.value)}
                        />
                    </FormField>
                    {showVideoUrl ? (
                        <FormField
                            label="لینک ویدیو / پخش زنده"
                            htmlFor="session-video-url"
                            className="md:col-span-2"
                            error={errors.video_url}
                        >
                            <AdminInput
                                id="session-video-url"
                                dir="ltr"
                                className="text-left font-mono"
                                placeholder="https://..."
                                value={data.video_url ?? ''}
                                disabled={processing}
                                onChange={(e) => onChange('video_url', e.target.value)}
                            />
                        </FormField>
                    ) : null}
                </div>
            </Card>

            {showActual && (
                <Card className="space-y-4 p-6">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">زمان واقعی برگزاری</h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="شروع واقعی" error={errors.actual_start_at}>
                            <PersianDatePicker
                                includeTime
                                value={data.actual_start_at ?? ''}
                                disabled={processing}
                                clearable
                                onChange={(v) => onChange('actual_start_at', v)}
                            />
                        </FormField>
                        <FormField label="پایان واقعی" error={errors.actual_end_at}>
                            <PersianDatePicker
                                includeTime
                                value={data.actual_end_at ?? ''}
                                disabled={processing}
                                clearable
                                onChange={(v) => onChange('actual_end_at', v)}
                            />
                        </FormField>
                    </div>
                </Card>
            )}

            {showCancelled && (
                <Card className="space-y-4 p-6">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">لغو جلسه</h2>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="زمان لغو" error={errors.cancelled_at}>
                            <PersianDatePicker
                                includeTime
                                value={data.cancelled_at ?? ''}
                                disabled={processing}
                                clearable
                                onChange={(v) => onChange('cancelled_at', v)}
                            />
                        </FormField>
                        <FormField label="دلیل لغو" className="md:col-span-2" error={errors.cancellation_reason}>
                            <Textarea
                                rows={2}
                                value={data.cancellation_reason ?? ''}
                                disabled={processing}
                                onChange={(e) => onChange('cancellation_reason', e.target.value)}
                            />
                        </FormField>
                    </div>
                </Card>
            )}
        </div>
    );
}
