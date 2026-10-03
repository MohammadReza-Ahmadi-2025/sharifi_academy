import { CatalogSeoFields, type CatalogSeoFormData } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, findSelectOptions, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MultiSelect } from '@/Components/Admin/shared/ui/multi-select';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type Option = { value: number; label: string };

type ProductFormData = CatalogSeoFormData & {
    title: string;
    slug: string;
    sku: string;
    short_description: string;
    description: string;
    features: string;
    brand_id: number | null;
    model: string;
    status: boolean;
    is_visible: boolean;
    category_ids: number[];
};

type Props = {
    data: ProductFormData;
    errors: Record<string, string>;
    categoryOptions: Option[];
    brandOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof ProductFormData, value: unknown) => void;
};

export function ProductFormFields({
    data,
    errors,
    categoryOptions,
    brandOptions,
    processing = false,
    onChange,
}: Props) {
    const categorySelectOptions = mapToSelectOptions(categoryOptions);
    const brandSelectOptions = mapToSelectOptions(brandOptions);

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات محصول</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان" required error={errors.title}>
                        <AdminInput
                            value={data.title}
                            disabled={processing}
                            onChange={(e) => onChange('title', e.target.value)}
                        />
                    </FormField>
                    <FormField label="اسلاگ" error={errors.slug} hint="خالی = از عنوان">
                        <AdminInput
                            dir="ltr"
                            className="text-left font-mono"
                            value={data.slug}
                            disabled={processing}
                            onChange={(e) => onChange('slug', e.target.value)}
                        />
                    </FormField>
                    <FormField label="SKU مرجع محصول" error={errors.sku} hint="اختیاری — برای تنوع پیش‌فرض کپی می‌شود">
                        <AdminInput
                            dir="ltr"
                            value={data.sku}
                            disabled={processing}
                            onChange={(e) => onChange('sku', e.target.value)}
                        />
                    </FormField>
                    <FormField label="مدل" error={errors.model}>
                        <AdminInput
                            value={data.model}
                            disabled={processing}
                            onChange={(e) => onChange('model', e.target.value)}
                        />
                    </FormField>
                    <FormField label="برند" error={errors.brand_id}>
                        <Select
                            isClearable
                            isDisabled={processing}
                            options={brandSelectOptions}
                            value={findSelectOption(brandSelectOptions, data.brand_id ?? '')}
                            onChange={(option) => onChange('brand_id', option?.value ? Number(option.value) : null)}
                        />
                    </FormField>
                    <FormField label="دسته‌بندی‌ها" className="md:col-span-2" error={errors.category_ids}>
                        <MultiSelect
                            isDisabled={processing}
                            options={categorySelectOptions}
                            value={findSelectOptions(categorySelectOptions, data.category_ids)}
                            onChange={(options) => onChange('category_ids', options.map((o) => Number(o.value)))}
                        />
                    </FormField>
                    <Switch
                        label="فعال"
                        checked={data.status}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('status', v)}
                    />
                    <Switch
                        label="قابل نمایش در فروشگاه"
                        checked={data.is_visible}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('is_visible', v)}
                    />
                </div>
            </Card>

            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">محتوا</h2>
                <FormField label="خلاصه" error={errors.short_description}>
                    <Textarea
                        rows={2}
                        value={data.short_description}
                        disabled={processing}
                        onChange={(e) => onChange('short_description', e.target.value)}
                    />
                </FormField>
                <FormField label="توضیحات" error={errors.description}>
                    <Textarea
                        rows={5}
                        value={data.description}
                        disabled={processing}
                        onChange={(e) => onChange('description', e.target.value)}
                    />
                </FormField>
                <FormField label="ویژگی‌ها" error={errors.features} hint="متن آزاد یا لیست ویژگی‌های محصول">
                    <Textarea
                        rows={4}
                        value={data.features}
                        disabled={processing}
                        onChange={(e) => onChange('features', e.target.value)}
                    />
                </FormField>
            </Card>

            <CatalogSeoFields
                data={data}
                errors={errors}
                processing={processing}
                onChange={(key, value) => onChange(key, value)}
            />
        </div>
    );
}

export type { ProductFormData };
