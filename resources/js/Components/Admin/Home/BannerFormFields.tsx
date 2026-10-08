import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { toGregorianDateOnly } from '@/Components/Admin/shared/lib/datetime-local-value';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
import { Select } from '@/Components/Admin/shared/ui/select';
import type { FormDataErrors } from '@inertiajs/core';

export type BannerFormData = {
    title: string;
    subtitle: string;
    button_text: string;
    button_link: string;
    location: string;
    starts_at: string;
    ends_at: string;
};

type Props = {
    data: BannerFormData;
    errors: FormDataErrors<BannerFormData>;
    locationOptions: Record<string, string>;
    processing?: boolean;
    imageItems: ReturnType<typeof useGalleryUploadState>[0];
    onImageChange: ReturnType<typeof useGalleryUploadState>[1];
    onChange: (patch: Partial<BannerFormData>) => void;
    errorImage?: string;
};

export function BannerFormFields({
    data,
    errors,
    locationOptions,
    processing,
    imageItems,
    onImageChange,
    onChange,
    errorImage,
}: Props) {
    const locationSelectOptions = mapToSelectOptions(
        Object.entries(locationOptions).map(([value, label]) => ({ value, label })),
    );

    return (
        <Card className="w-full space-y-6 p-6">
            <GalleryImagesField
                title="تصویر بنر"
                description="تصویر اصلی اسلایدر یا بنر سکشن."
                items={imageItems}
                onItemsChange={onImageChange}
                multiple={false}
                maxFiles={1}
                disabled={processing}
                error={errorImage}
            />
            <div className="grid gap-4 md:grid-cols-2">
                <FormField label="عنوان" className="md:col-span-2">
                    <AdminInput value={data.title} onChange={(e) => onChange({ title: e.target.value })} disabled={processing} />
                </FormField>
                <FormField label="زیرعنوان" className="md:col-span-2">
                    <AdminInput value={data.subtitle} onChange={(e) => onChange({ subtitle: e.target.value })} disabled={processing} />
                </FormField>
                <FormField label="متن دکمه">
                    <AdminInput value={data.button_text} onChange={(e) => onChange({ button_text: e.target.value })} disabled={processing} />
                </FormField>
                <FormField label="لینک دکمه">
                    <AdminInput dir="ltr" value={data.button_link} onChange={(e) => onChange({ button_link: e.target.value })} disabled={processing} />
                </FormField>
                <FormField label="محل نمایش" className="md:col-span-2" error={errors.location}>
                    <Select
                        options={locationSelectOptions}
                        value={findSelectOption(locationSelectOptions, data.location)}
                        onChange={(opt) => onChange({ location: opt?.value ?? 'section' })}
                        isDisabled={processing}
                        isSearchable={false}
                    />
                </FormField>
                <FormField label="شروع نمایش">
                    <PersianDatePicker
                        includeTime
                        value={data.starts_at}
                        disabled={processing}
                        placeholder="تاریخ و زمان شروع"
                        clearable
                        onChange={(v) => onChange({ starts_at: v })}
                    />
                </FormField>
                <FormField label="پایان نمایش">
                    <PersianDatePicker
                        includeTime
                        value={data.ends_at}
                        disabled={processing}
                        min={data.starts_at ? toGregorianDateOnly(data.starts_at) : undefined}
                        placeholder="تاریخ و زمان پایان"
                        clearable
                        onChange={(v) => onChange({ ends_at: v })}
                    />
                </FormField>
            </div>
        </Card>
    );
}
