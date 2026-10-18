import { CatalogSeoFields, type CatalogSeoFormData } from '@/Components/Admin/Catalog/CatalogSeoFields';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

export type PageFormData = CatalogSeoFormData & {
    title: string;
    slug: string;
    content: string;
    is_active: boolean;
};

type Props = {
    data: PageFormData;
    errors: Record<string, string>;
    processing?: boolean;
    onChange: (key: keyof PageFormData, value: unknown) => void;
};

export function PageFormFields({ data, errors, processing = false, onChange }: Props) {
    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">صفحه</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان" required error={errors.title}>
                        <AdminInput
                            value={data.title}
                            disabled={processing}
                            onChange={(e) => onChange('title', e.target.value)}
                            placeholder="مثلاً: درباره ما"
                        />
                    </FormField>
                    <FormField label="اسلاگ (URL)" error={errors.slug} hint="خالی = از عنوان">
                        <AdminInput
                            dir="ltr"
                            className="text-left font-mono"
                            value={data.slug}
                            disabled={processing}
                            onChange={(e) => onChange('slug', e.target.value)}
                            placeholder="about-us"
                        />
                    </FormField>
                    <FormField className="md:col-span-2" error={errors.is_active}>
                        <Switch
                            checked={data.is_active}
                            disabled={processing}
                            onCheckedChange={(v) => onChange('is_active', v)}
                            label="فعال (قابل مشاهده در سایت)"
                        />
                    </FormField>
                </div>
                <FormField label="محتوا" error={errors.content}>
                    <Textarea
                        rows={14}
                        value={data.content}
                        disabled={processing}
                        onChange={(e) => onChange('content', e.target.value)}
                    />
                </FormField>
            </Card>
            <CatalogSeoFields data={data} errors={errors} processing={processing} onChange={onChange} />
        </div>
    );
}
