import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MoneyInput } from '@/Components/Admin/shared/ui/money-input';
import { Select } from '@/Components/Admin/shared/ui/select';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { toGregorianDateOnly } from '@/Components/Admin/shared/lib/datetime-local-value';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { Plus, Trash2 } from 'lucide-react';

type InstructorRow = { user_id: number; role: string; is_primary: boolean; share_percentage: string };

const WEEKDAY_OPTIONS = [
    { value: 6, label: 'شنبه' },
    { value: 0, label: 'یکشنبه' },
    { value: 1, label: 'دوشنبه' },
    { value: 2, label: 'سه‌شنبه' },
    { value: 3, label: 'چهارشنبه' },
    { value: 4, label: 'پنجشنبه' },
    { value: 5, label: 'جمعه' },
] as const;

type ClassFormData = {
    name: string;
    code: string;
    status: string;
    delivery_type: string;
    online_platform: string;
    capacity: number | '';
    price: number;
    registration_starts_at: string;
    registration_ends_at: string;
    starts_at: string;
    ends_at: string;
    meeting_weekdays: number[];
    meeting_starts_time: string;
    meeting_ends_time: string;
    location: string;
    description: string;
    is_public: boolean;
    instructors: InstructorRow[];
};

type Props = {
    data: ClassFormData;
    errors: Record<string, string>;
    statusOptions: { value: string; label: string }[];
    deliveryTypeOptions: { value: string; label: string }[];
    onlinePlatformOptions: { value: string; label: string }[];
    instructorRoleOptions: { value: string; label: string }[];
    userOptions: { value: number; label: string }[];
    processing?: boolean;
    onChange: (key: keyof ClassFormData, value: unknown) => void;
};

export function ClassFormFields({
    data,
    errors,
    statusOptions,
    deliveryTypeOptions,
    onlinePlatformOptions,
    instructorRoleOptions,
    userOptions,
    processing = false,
    onChange,
}: Props) {
    const statusSelectOptions = mapToSelectOptions(statusOptions);
    const deliverySelectOptions = mapToSelectOptions(deliveryTypeOptions);
    const onlinePlatformSelectOptions = mapToSelectOptions(onlinePlatformOptions);
    const showOnlinePlatform =
        !data.delivery_type || data.delivery_type === 'online' || data.delivery_type === 'hybrid';
    const userSelectOptions = mapToSelectOptions(userOptions);
    const roleSelectOptions = mapToSelectOptions(instructorRoleOptions);

    const addInstructor = () => {
        onChange('instructors', [
            ...data.instructors,
            {
                user_id: userOptions[0]?.value ?? 0,
                role: instructorRoleOptions[0]?.value ?? 'lead',
                is_primary: data.instructors.length === 0,
                share_percentage: '',
            },
        ]);
    };

    const updateInstructor = (index: number, patch: Partial<InstructorRow>) => {
        const next = [...data.instructors];
        next[index] = { ...next[index], ...patch };
        onChange('instructors', next);
    };

    const removeInstructor = (index: number) => {
        onChange('instructors', data.instructors.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">مشخصات کلاس</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="نام" htmlFor="class-name" required error={errors.name}>
                        <AdminInput
                            id="class-name"
                            value={data.name}
                            disabled={processing}
                            onChange={(e) => onChange('name', e.target.value)}
                        />
                    </FormField>
                    <FormField label="کد" htmlFor="class-code" required error={errors.code}>
                        <AdminInput
                            id="class-code"
                            dir="ltr"
                            className="text-left font-mono"
                            value={data.code}
                            disabled={processing}
                            onChange={(e) => onChange('code', e.target.value)}
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
                    <FormField label="نوع برگزاری" error={errors.delivery_type}>
                        <Select
                            isDisabled={processing}
                            isClearable
                            placeholder="انتخاب..."
                            options={deliverySelectOptions}
                            value={findSelectOption(deliverySelectOptions, data.delivery_type)}
                            onChange={(option) => onChange('delivery_type', option?.value ?? '')}
                        />
                    </FormField>
                    {showOnlinePlatform ? (
                        <FormField label="پلتفرم آنلاین" error={errors.online_platform}>
                            <Select
                                isDisabled={processing}
                                options={onlinePlatformSelectOptions}
                                value={findSelectOption(
                                    onlinePlatformSelectOptions,
                                    data.online_platform || 'adobe_connect',
                                )}
                                onChange={(option) => onChange('online_platform', option?.value ?? 'adobe_connect')}
                            />
                        </FormField>
                    ) : null}
                    <FormField label="ظرفیت" htmlFor="class-capacity" error={errors.capacity}>
                        <AdminInput
                            id="class-capacity"
                            type="number"
                            min={0}
                            value={data.capacity}
                            disabled={processing}
                            onChange={(e) => onChange('capacity', e.target.value ? Number(e.target.value) : '')}
                        />
                    </FormField>
                    <FormField label="قیمت (ریال)" error={errors.price}>
                        <MoneyInput
                            value={String(data.price ?? '')}
                            disabled={processing}
                            onChange={(value) => onChange('price', value ? Number(value) : 0)}
                        />
                    </FormField>
                    <FormField label="محل برگزاری" htmlFor="class-location" className="md:col-span-2" error={errors.location}>
                        <AdminInput
                            id="class-location"
                            value={data.location}
                            disabled={processing}
                            onChange={(e) => onChange('location', e.target.value)}
                        />
                    </FormField>
                    <FormField label="توضیحات" htmlFor="class-desc" className="md:col-span-2" error={errors.description}>
                        <Textarea
                            id="class-desc"
                            rows={3}
                            value={data.description}
                            disabled={processing}
                            onChange={(e) => onChange('description', e.target.value)}
                        />
                    </FormField>
                    <Switch
                        label="قابل مشاهده برای عموم"
                        checked={data.is_public}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('is_public', v)}
                    />
                </div>
            </Card>

            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">زمان‌بندی</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="شروع ثبت‌نام" error={errors.registration_starts_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.registration_starts_at}
                            disabled={processing}
                            placeholder="انتخاب تاریخ و زمان"
                            clearable
                            onChange={(v) => onChange('registration_starts_at', v)}
                        />
                    </FormField>
                    <FormField label="پایان ثبت‌نام" error={errors.registration_ends_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.registration_ends_at}
                            disabled={processing}
                            min={data.registration_starts_at ? toGregorianDateOnly(data.registration_starts_at) : undefined}
                            placeholder="انتخاب تاریخ و زمان"
                            clearable
                            onChange={(v) => onChange('registration_ends_at', v)}
                        />
                    </FormField>
                    <FormField label="شروع دوره کلاس" error={errors.starts_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.starts_at}
                            disabled={processing}
                            placeholder="انتخاب تاریخ و زمان"
                            clearable
                            onChange={(v) => onChange('starts_at', v)}
                        />
                    </FormField>
                    <FormField label="پایان دوره کلاس" error={errors.ends_at}>
                        <PersianDatePicker
                            includeTime
                            value={data.ends_at}
                            disabled={processing}
                            min={data.starts_at ? toGregorianDateOnly(data.starts_at) : undefined}
                            placeholder="انتخاب تاریخ و زمان"
                            clearable
                            onChange={(v) => onChange('ends_at', v)}
                        />
                    </FormField>
                </div>

                <div className="space-y-3 border-t border-slate-200 pt-4 dark:border-slate-800">
                    <div>
                        <h3 className="text-sm font-semibold text-slate-900 dark:text-slate-100">برنامه هفتگی جلسات</h3>
                        <p className="mt-1 text-xs text-slate-500">
                            مثلاً شنبه‌ها و دوشنبه‌ها، ساعت ۱۲ تا ۱۴ — هر روز انتخاب‌شده یک جلسه در همان بازه.
                        </p>
                    </div>
                    <FormField label="روزهای هفته" error={errors.meeting_weekdays || errors['meeting_weekdays.0']}>
                        <div className="flex flex-wrap gap-2">
                            {WEEKDAY_OPTIONS.map((day) => {
                                const selected = (data.meeting_weekdays ?? []).includes(day.value);
                                return (
                                    <button
                                        key={day.value}
                                        type="button"
                                        disabled={processing}
                                        onClick={() => {
                                            const current = data.meeting_weekdays ?? [];
                                            onChange(
                                                'meeting_weekdays',
                                                selected
                                                    ? current.filter((value) => value !== day.value)
                                                    : [...current, day.value],
                                            );
                                        }}
                                        className={`rounded-xl border px-3 py-2 text-sm font-medium transition ${
                                            selected
                                                ? 'border-primary bg-primary/10 text-primary'
                                                : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'
                                        }`}
                                    >
                                        {day.label}
                                    </button>
                                );
                            })}
                        </div>
                    </FormField>
                    <div className="grid gap-4 md:grid-cols-2">
                        <FormField label="ساعت شروع جلسه" htmlFor="meeting-starts-time" error={errors.meeting_starts_time}>
                            <AdminInput
                                id="meeting-starts-time"
                                type="time"
                                dir="ltr"
                                className="text-left"
                                value={data.meeting_starts_time ?? ''}
                                disabled={processing}
                                onChange={(e) => onChange('meeting_starts_time', e.target.value)}
                            />
                        </FormField>
                        <FormField label="ساعت پایان جلسه" htmlFor="meeting-ends-time" error={errors.meeting_ends_time}>
                            <AdminInput
                                id="meeting-ends-time"
                                type="time"
                                dir="ltr"
                                className="text-left"
                                value={data.meeting_ends_time ?? ''}
                                disabled={processing}
                                onChange={(e) => onChange('meeting_ends_time', e.target.value)}
                            />
                        </FormField>
                    </div>
                </div>
            </Card>

            <Card className="space-y-4 p-6">
                <p className="text-sm text-slate-600 dark:text-slate-400">
                    برای حضور و غیاب و مدیریت جلسه در پنل استاد، استاد را اینجا به کلاس اضافه کنید (مالک دوره به‌تنهایی
                    دسترسی نمی‌گیرد).
                </p>
                <div className="flex items-center justify-between gap-2">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اساتید کلاس</h2>
                    <AdminButton type="button" variant="outline" size="sm" disabled={processing || userOptions.length === 0} onClick={addInstructor}>
                        <Plus className="ml-1 h-4 w-4" />
                        افزودن استاد
                    </AdminButton>
                </div>
                {userOptions.length === 0 ? (
                    <p className="text-sm text-amber-700 dark:text-amber-400">
                        کاربری با نقش استاد و وضعیت فعال پیدا نشد. ابتدا در بخش کاربران نقش «استاد» بدهید.
                    </p>
                ) : null}
                {data.instructors.length === 0 ? (
                    <p className="text-sm text-slate-500">هنوز استادی اضافه نشده.</p>
                ) : (
                    <div className="space-y-3">
                        {data.instructors.map((row, index) => (
                            <div key={index} className="grid gap-3 rounded-lg border border-slate-200 p-4 md:grid-cols-[1fr_1fr_auto_auto] dark:border-slate-800">
                                <Select
                                    isDisabled={processing}
                                    placeholder="استاد..."
                                    options={userSelectOptions}
                                    value={findSelectOption(userSelectOptions, row.user_id)}
                                    onChange={(option) => updateInstructor(index, { user_id: Number(option?.value ?? 0) })}
                                />
                                <Select
                                    isDisabled={processing}
                                    options={roleSelectOptions}
                                    value={findSelectOption(roleSelectOptions, row.role)}
                                    onChange={(option) => updateInstructor(index, { role: option?.value ?? '' })}
                                />
                                <Switch
                                    label="استاد اصلی"
                                    checked={row.is_primary}
                                    disabled={processing}
                                    onCheckedChange={(v) => updateInstructor(index, { is_primary: v })}
                                />
                                <AdminButton
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    disabled={processing}
                                    onClick={() => removeInstructor(index)}
                                    aria-label="حذف استاد"
                                >
                                    <Trash2 className="h-4 w-4 text-rose-600" />
                                </AdminButton>
                            </div>
                        ))}
                    </div>
                )}
            </Card>
        </div>
    );
}
