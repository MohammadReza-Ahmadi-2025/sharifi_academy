import {
    ProductVariantFormFields,
    type VariantFormValues,
} from '@/Components/Admin/Catalog/ProductVariantFormFields';
import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { Card } from '@/Components/Admin/shared/ui/card';

type Option = { value: string; label: string };

type Props = {
    data: VariantFormValues;
    errors: Record<string, string>;
    displayModeOptions: Option[];
    variantAttributes?: CatalogVariantAttribute[];
    processing?: boolean;
    onChange: (key: keyof VariantFormValues, value: unknown) => void;
};

export function ProductInitialVariantFields({
    data,
    errors,
    displayModeOptions,
    variantAttributes = [],
    processing = false,
    onChange,
}: Props) {
    return (
        <Card className="space-y-4 p-6">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">واریانت فروش</h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    هر محصول همیشه یک واریانت دارد. اگر این بخش را خالی بگذارید، واریانت «پیش‌فرض» ساخته می‌شود؛ می‌توانید
                    رنگ/سایز بگذارید یا بعداً در ویرایش، ترکیب‌ها را بسازید.
                </p>
            </div>
            <ProductVariantFormFields
                data={data}
                errors={errors}
                displayModeOptions={displayModeOptions}
                variantAttributes={variantAttributes}
                processing={processing}
                onChange={onChange}
            />
        </Card>
    );
}

export type { VariantFormValues };
