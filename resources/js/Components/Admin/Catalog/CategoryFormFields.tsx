import { CatalogSeoFields, type CatalogSeoFormData } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type Option = { value: number; label: string };

type CategoryFormData = CatalogSeoFormData & {
    parent_id: number | null;
    title: string;
    slug: string;
    description: string;
    status: boolean;
    show_in_menu: boolean;
};

type Props = {
    data: CategoryFormData;
    errors: Record<string, string>;
    parentOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof CategoryFormData, value: unknown) => void;
};

export function CategoryFormFields({ data, errors, parentOptions, processing = false, onChange }: Props) {
    const parentSelectOptions = mapToSelectOptions(parentOptions);

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات دسته</h2>
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
                    <FormField label="دسته والد" error={errors.parent_id}>
                        <Select
                            isClearable
                            isDisabled={processing}
                            options={parentSelectOptions}
                            value={findSelectOption(parentSelectOptions, data.parent_id ?? '')}
                            onChange={(option) => onChange('parent_id', option?.value ? Number(option.value) : null)}
                        />
                    </FormField>
                    <FormField label="توضیحات" className="md:col-span-2" error={errors.description}>
                        <Textarea
                            rows={3}
                            value={data.description}
                            disabled={processing}
                            onChange={(e) => onChange('description', e.target.value)}
                        />
                    </FormField>
                    <Switch
                        label="فعال"
                        checked={data.status}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('status', v)}
                    />
                    <Switch
                        label="نمایش در منو"
                        checked={data.show_in_menu}
                        disabled={processing}
                        onCheckedChange={(v) => onChange('show_in_menu', v)}
                    />
                </div>
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

export type { CategoryFormData };
