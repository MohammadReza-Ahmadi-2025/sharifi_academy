import { CatalogSeoFields, type CatalogSeoFormData } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

export type BrandFormData = CatalogSeoFormData & {
    title: string;
    slug: string;
    description: string;
    website: string;
    status: boolean;
    sort: number;
};

type Props = {
    data: BrandFormData;
    errors: Partial<Record<string, string>>;
    processing?: boolean;
    onChange: (key: keyof BrandFormData, value: string | boolean | number) => void;
};

export function BrandFormFields({ data, errors, onChange }: Props) {
    return (
        <div className="space-y-6">
            <div className="grid gap-4 lg:grid-cols-2">
                <FormField label="عنوان برند" error={errors.title} required>
                    <Input
                        value={data.title}
                        onChange={(e) => onChange('title', e.target.value)}
                    />
                </FormField>
                <FormField label="نامک (slug)" error={errors.slug} hint="خالی = خودکار از عنوان">
                    <Input
                        value={data.slug}
                        dir="ltr"
                        onChange={(e) => onChange('slug', e.target.value)}
                    />
                </FormField>
            </div>
            <FormField label="توضیحات" error={errors.description}>
                <Textarea
                    rows={4}
                    value={data.description}
                    onChange={(e) => onChange('description', e.target.value)}
                />
            </FormField>
            <div className="grid gap-4 lg:grid-cols-2">
                <FormField label="وب‌سایت" error={errors.website}>
                    <Input
                        value={data.website}
                        dir="ltr"
                        placeholder="https://"
                        onChange={(e) => onChange('website', e.target.value)}
                    />
                </FormField>
                <FormField label="ترتیب نمایش" error={errors.sort}>
                    <Input
                        type="number"
                        min={0}
                        value={data.sort}
                        onChange={(e) => onChange('sort', Number(e.target.value) || 0)}
                    />
                </FormField>
            </div>
            <FormField label="فعال" error={errors.status}>
                <Switch
                    checked={data.status}
                    onCheckedChange={(checked) => onChange('status', checked)}
                />
            </FormField>
            <CatalogSeoFields data={data} errors={errors} onChange={onChange} />
        </div>
    );
}
