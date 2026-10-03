import {
    ProductVariantFormFields,
    type VariantFormValues,
} from '@/Components/Admin/Catalog/ProductVariantFormFields';
import { Card } from '@/Components/Admin/shared/ui/card';

type Option = { value: string; label: string };

type Props = {
    data: VariantFormValues;
    errors: Record<string, string>;
    displayModeOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof VariantFormValues, value: unknown) => void;
};

export function ProductInitialVariantFields({
    data,
    errors,
    displayModeOptions,
    processing = false,
    onChange,
}: Props) {
    return (
        <Card className="space-y-4 p-6">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">تنوع فروش (اختیاری)</h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    اگر خالی بماند، تنوع «پیش‌فرض» با قیمت و موجودی صفر ساخته می‌شود و بعداً از همین بخش در ویرایش محصول
                    تکمیل می‌کنید.
                </p>
            </div>
            <ProductVariantFormFields
                data={data}
                errors={errors}
                displayModeOptions={displayModeOptions}
                processing={processing}
                onChange={onChange}
            />
        </Card>
    );
}

export type { VariantFormValues };
