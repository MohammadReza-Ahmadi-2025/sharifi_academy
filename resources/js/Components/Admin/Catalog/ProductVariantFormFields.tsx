import { ProductVariantAttributeFields } from '@/Components/Admin/Catalog/ProductVariantAttributeFields';
import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MoneyInput } from '@/Components/Admin/shared/ui/money-input';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Switch } from '@/Components/Admin/shared/ui/switch';

export type VariantFormValues = {
    title: string;
    sku: string;
    barcode: string;
    status: boolean;
    display_mode: string;
    price: number | '';
    final_price: number | '';
    stock: number | '';
    attribute_value_ids: number[];
};

type Option = { value: string; label: string };

type Props = {
    data: VariantFormValues;
    errors: Record<string, string>;
    displayModeOptions: Option[];
    variantAttributes?: CatalogVariantAttribute[];
    processing?: boolean;
    onChange: (key: keyof VariantFormValues, value: unknown) => void;
};

function fieldError(errors: Record<string, string>, key: string): string | undefined {
    return errors[`variant.${key}`] ?? errors[key];
}

export function ProductVariantFormFields({
    data,
    errors,
    displayModeOptions,
    variantAttributes = [],
    processing = false,
    onChange,
}: Props) {
    const displaySelectOptions = mapToSelectOptions(displayModeOptions);

    return (
        <div className="space-y-6">
            <div className="space-y-4">
                <p className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    قیمت و موجودی
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label="قیمت (ریال)" required error={fieldError(errors, 'price')}>
                        <MoneyInput
                            disabled={processing}
                            value={data.price === '' ? '' : String(data.price)}
                            onChange={(value) => onChange('price', value === '' ? '' : Number(value))}
                        />
                    </FormField>
                    <FormField
                        label="قیمت نهایی"
                        hint="خالی = همان قیمت"
                        error={fieldError(errors, 'final_price')}
                    >
                        <MoneyInput
                            disabled={processing}
                            value={data.final_price === '' ? '' : String(data.final_price)}
                            onChange={(value) => onChange('final_price', value === '' ? '' : Number(value))}
                        />
                    </FormField>
                    <FormField label="موجودی" required error={fieldError(errors, 'stock')}>
                        <AdminInput
                            type="number"
                            min={0}
                            value={data.stock === '' ? '' : String(data.stock)}
                            disabled={processing}
                            onChange={(e) =>
                                onChange('stock', e.target.value === '' ? '' : Number(e.target.value))
                            }
                        />
                    </FormField>
                    <FormField label="نحوه نمایش در فروشگاه" required error={fieldError(errors, 'display_mode')}>
                        <Select
                            isDisabled={processing}
                            options={displaySelectOptions}
                            value={findSelectOption(displaySelectOptions, data.display_mode)}
                            onChange={(option) => onChange('display_mode', option?.value ?? 'normal')}
                        />
                    </FormField>
                </div>
            </div>

            <ProductVariantAttributeFields
                attributes={variantAttributes}
                valueIds={data.attribute_value_ids}
                disabled={processing}
                error={fieldError(errors, 'attribute_value_ids')}
                onChange={(valueIds) => onChange('attribute_value_ids', valueIds)}
            />

            <div className="space-y-4">
                <p className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                    مشخصات تنوع
                </p>
                <div className="grid gap-4 sm:grid-cols-2">
                    <FormField label="عنوان تنوع" error={fieldError(errors, 'title')}>
                        <AdminInput
                            value={data.title}
                            disabled={processing}
                            placeholder="مثلاً: یک‌نفره، چاپی، …"
                            onChange={(e) => onChange('title', e.target.value)}
                        />
                    </FormField>
                    <FormField label="SKU" error={fieldError(errors, 'sku')}>
                        <AdminInput
                            dir="ltr"
                            className="font-mono text-sm"
                            value={data.sku}
                            disabled={processing}
                            onChange={(e) => onChange('sku', e.target.value)}
                        />
                    </FormField>
                    <FormField label="بارکد" className="sm:col-span-2" error={fieldError(errors, 'barcode')}>
                        <AdminInput
                            dir="ltr"
                            className="font-mono text-sm"
                            value={data.barcode}
                            disabled={processing}
                            onChange={(e) => onChange('barcode', e.target.value)}
                        />
                    </FormField>
                </div>
            </div>

            <Switch
                label="تنوع فعال"
                description="تنوع غیرفعال در فروشگاه نمایش داده نمی‌شود."
                checked={data.status}
                disabled={processing}
                onCheckedChange={(v) => onChange('status', v)}
            />
        </div>
    );
}

export const emptyVariantFormValues: VariantFormValues = {
    title: '',
    sku: '',
    barcode: '',
    status: true,
    display_mode: 'normal',
    price: '',
    final_price: '',
    stock: '',
    attribute_value_ids: [],
};
