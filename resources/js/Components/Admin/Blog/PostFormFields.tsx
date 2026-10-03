import { CatalogSeoFields, type CatalogSeoFormData } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, findSelectOptions, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { MultiSelect } from '@/Components/Admin/shared/ui/multi-select';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type Option = { value: number | string; label: string };

type PostFormData = CatalogSeoFormData & {
    title: string;
    slug: string;
    excerpt: string;
    content: string;
    status: string;
    category_ids: number[];
};

type Props = {
    data: PostFormData;
    errors: Record<string, string>;
    categoryOptions: Option[];
    statusOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof PostFormData, value: unknown) => void;
};

export function PostFormFields({
    data,
    errors,
    categoryOptions,
    statusOptions,
    processing = false,
    onChange,
}: Props) {
    const categorySelectOptions = mapToSelectOptions(categoryOptions);
    const statusSelectOptions = mapToSelectOptions(statusOptions);

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">محتوای پست</h2>
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
                    <FormField label="وضعیت انتشار" required error={errors.status}>
                        <Select
                            isDisabled={processing}
                            options={statusSelectOptions}
                            value={findSelectOption(statusSelectOptions, data.status)}
                            onChange={(option) => onChange('status', option?.value ?? 'draft')}
                        />
                    </FormField>
                    <FormField label="دسته‌ها" required error={errors.category_ids}>
                        <MultiSelect
                            isDisabled={processing}
                            options={categorySelectOptions}
                            value={findSelectOptions(categorySelectOptions, data.category_ids)}
                            onChange={(options) =>
                                onChange(
                                    'category_ids',
                                    options.map((o) => Number(o.value)),
                                )
                            }
                        />
                    </FormField>
                </div>
                <FormField label="خلاصه" error={errors.excerpt}>
                    <Textarea
                        rows={3}
                        value={data.excerpt}
                        disabled={processing}
                        onChange={(e) => onChange('excerpt', e.target.value)}
                    />
                </FormField>
                <FormField label="متن" required error={errors.content}>
                    <Textarea
                        rows={12}
                        value={data.content}
                        disabled={processing}
                        onChange={(e) => onChange('content', e.target.value)}
                    />
                </FormField>
            </Card>
            <CatalogSeoFields
                data={data}
                errors={errors}
                processing={processing}
                onChange={onChange}
            />
        </div>
    );
}
