import { SiteFormField } from '@/Components/Account/SiteFormField';
import { SiteInput } from '@/Components/Account/SiteInput';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import type { LocationOptions, ProfileExtendedFormData } from '@/types/profile-extended';
import { useMemo, useState } from 'react';

function localIsoDate(date: Date): string {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

function yesterdayIsoDate(): string {
    const date = new Date();
    date.setDate(date.getDate() - 1);

    return localIsoDate(date);
}

type Errors = Partial<Record<string, string>>;

type Props = {
    variant: 'site' | 'admin';
    data: ProfileExtendedFormData;
    errors: Errors;
    locations: LocationOptions;
    initialProvinceId: number | null;
    processing: boolean;
    isVerified?: boolean;
    onChange: <K extends keyof ProfileExtendedFormData>(key: K, value: ProfileExtendedFormData[K]) => void;
};

export function ProfileExtendedFields({
    variant,
    data,
    errors,
    locations,
    initialProvinceId,
    processing,
    isVerified = false,
    onChange,
}: Props) {
    const [provinceId, setProvinceId] = useState<string>(
        initialProvinceId ? String(initialProvinceId) : '',
    );

    const citiesForProvince = useMemo(() => {
        if (!provinceId) {
            return [];
        }

        return locations.cities.filter((city) => city.provinceId === Number(provinceId));
    }, [locations.cities, provinceId]);

    const Field = variant === 'site' ? SiteFormField : FormField;
    const Input = variant === 'site' ? SiteInput : AdminInput;

    const selectClass =
        variant === 'site'
            ? 'flex h-11 w-full rounded-xl border border-secondary bg-surface px-3 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20'
            : 'flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    const textareaClass =
        variant === 'site'
            ? 'min-h-[120px] w-full rounded-xl border border-secondary bg-surface px-3 py-2 text-sm focus:border-primary focus:outline-none focus:ring-2 focus:ring-primary/20'
            : 'min-h-[120px] w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100';

    const maxBirthDate = useMemo(() => yesterdayIsoDate(), []);

    const birthDatePickerClass =
        variant === 'site'
            ? 'h-11 rounded-xl border-secondary bg-surface text-text hover:border-secondary focus-visible:border-primary focus-visible:ring-primary/20'
            : undefined;

    return (
        <div className="space-y-4">
            <Field label="تیتر حرفه‌ای" htmlFor="headline" error={errors.headline} hint="مثال: مدرس لاراول / دانشجوی دوره آیلتس">
                <Input
                    id="headline"
                    value={data.headline}
                    onChange={(e) => onChange('headline', e.target.value)}
                    disabled={processing}
                />
            </Field>

            <Field label="بیوگرافی" htmlFor="bio" error={errors.bio}>
                <textarea
                    id="bio"
                    value={data.bio}
                    onChange={(e) => onChange('bio', e.target.value)}
                    disabled={processing}
                    className={textareaClass}
                />
            </Field>

            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="تاریخ تولد" htmlFor="birth_date" error={errors.birth_date}>
                    <PersianDatePicker
                        value={data.birth_date}
                        onChange={(value) => onChange('birth_date', value)}
                        disabled={processing}
                        placeholder="انتخاب تاریخ تولد"
                        max={maxBirthDate}
                        clearable
                        className={birthDatePickerClass}
                    />
                </Field>
                <Field label="کد ملی" htmlFor="national_code" error={errors.national_code}>
                    <Input
                        id="national_code"
                        inputMode="numeric"
                        dir="ltr"
                        className="text-left font-mono"
                        maxLength={10}
                        value={data.national_code}
                        onChange={(e) => onChange('national_code', e.target.value.replace(/\D/g, '').slice(0, 10))}
                        disabled={processing}
                    />
                </Field>
            </div>

            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="استان" htmlFor="province_id">
                    <select
                        id="province_id"
                        value={provinceId}
                        onChange={(e) => {
                            setProvinceId(e.target.value);
                            onChange('province_city_id', '');
                        }}
                        disabled={processing || locations.provinces.length === 0}
                        className={selectClass}
                    >
                        <option value="">انتخاب استان</option>
                        {locations.provinces.map((province) => (
                            <option key={province.id} value={province.id}>{province.title}</option>
                        ))}
                    </select>
                </Field>
                <Field label="شهر" htmlFor="province_city_id" error={errors.province_city_id}>
                    <select
                        id="province_city_id"
                        value={data.province_city_id}
                        onChange={(e) => onChange('province_city_id', e.target.value)}
                        disabled={processing || !provinceId}
                        className={selectClass}
                    >
                        <option value="">انتخاب شهر</option>
                        {citiesForProvince.map((city) => (
                            <option key={city.id} value={city.id}>{city.title}</option>
                        ))}
                    </select>
                </Field>
            </div>

            <label className="flex cursor-pointer items-center gap-2 text-sm text-slate-700 dark:text-slate-300">
                <input
                    type="checkbox"
                    checked={data.is_public}
                    onChange={(e) => onChange('is_public', e.target.checked)}
                    disabled={processing}
                    className="rounded border-slate-300"
                />
                نمایش عمومی پروفایل و رزومه
            </label>

            {isVerified ? (
                <p className="text-xs text-emerald-600 dark:text-emerald-400">پروفایل شما توسط آموزشگاه تأیید شده است.</p>
            ) : null}
        </div>
    );
}
