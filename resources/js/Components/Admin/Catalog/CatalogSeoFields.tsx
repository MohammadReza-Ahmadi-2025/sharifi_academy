import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

export type CatalogSeoFormData = {
    meta_title: string;
    meta_description: string;
    meta_keywords: string;
    canonical: string;
};

type Props<T extends CatalogSeoFormData> = {
    data: T;
    errors: Record<string, string>;
    processing?: boolean;
    onChange: (key: keyof CatalogSeoFormData, value: string) => void;
};

export function CatalogSeoFields<T extends CatalogSeoFormData>({
    data,
    errors,
    processing = false,
    onChange,
}: Props<T>) {
    return (
        <Card className="space-y-4 p-6">
            <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">سئو</h2>
            <div className="grid gap-4 md:grid-cols-2">
                <FormField label="عنوان متا (title)" error={errors.meta_title}>
                    <AdminInput
                        value={data.meta_title}
                        disabled={processing}
                        onChange={(e) => onChange('meta_title', e.target.value)}
                    />
                </FormField>
                <FormField
                    label="آدرس canonical"
                    error={errors.canonical}
                    hint="اختیاری — آدرس کامل با https"
                >
                    <AdminInput
                        dir="ltr"
                        className="text-left font-mono text-sm"
                        value={data.canonical}
                        disabled={processing}
                        onChange={(e) => onChange('canonical', e.target.value)}
                    />
                </FormField>
                <FormField label="کلمات کلیدی" error={errors.meta_keywords} hint="با ویرگول جدا کنید">
                    <AdminInput
                        value={data.meta_keywords}
                        disabled={processing}
                        onChange={(e) => onChange('meta_keywords', e.target.value)}
                    />
                </FormField>
                <FormField label="توضیح متا (description)" className="md:col-span-2" error={errors.meta_description}>
                    <Textarea
                        plain
                        rows={3}
                        value={data.meta_description}
                        disabled={processing}
                        onChange={(e) => onChange('meta_description', e.target.value)}
                    />
                </FormField>
            </div>
        </Card>
    );
}
