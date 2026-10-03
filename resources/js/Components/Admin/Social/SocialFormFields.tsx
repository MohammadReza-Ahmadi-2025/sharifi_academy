import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';

type Option = { value: string; label: string };

type SocialFormData = {
    type: string;
    value: string;
    label: string;
    target_blank: boolean;
    is_active: boolean;
};

type Props = {
    data: SocialFormData;
    errors: Record<string, string>;
    typeOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof SocialFormData, value: unknown) => void;
};

export function SocialFormFields({ data, errors, typeOptions, processing = false, onChange }: Props) {
    const typeSelectOptions = mapToSelectOptions(typeOptions);

    return (
        <Card className="space-y-4 p-6">
            <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">اطلاعات شبکه اجتماعی</h2>
            <div className="grid gap-4 md:grid-cols-2">
                <FormField label="نوع" required error={errors.type}>
                    <Select
                        isDisabled={processing}
                        options={typeSelectOptions}
                        value={findSelectOption(typeSelectOptions, data.type)}
                        onChange={(option) => onChange('type', option?.value ?? '')}
                    />
                </FormField>
                <FormField label="مقدار (لینک / شماره / ایمیل)" required error={errors.value}>
                    <AdminInput
                        dir="ltr"
                        className="text-left font-mono"
                        value={data.value}
                        disabled={processing}
                        onChange={(e) => onChange('value', e.target.value)}
                    />
                </FormField>
                <FormField label="برچسب نمایشی" error={errors.label}>
                    <AdminInput
                        value={data.label}
                        disabled={processing}
                        onChange={(e) => onChange('label', e.target.value)}
                    />
                </FormField>
            </div>
            <div className="flex flex-wrap gap-6">
                <Switch
                    label="باز شدن در تب جدید"
                    checked={data.target_blank}
                    disabled={processing}
                    onCheckedChange={(v) => onChange('target_blank', v)}
                />
                <Switch
                    label="فعال"
                    checked={data.is_active}
                    disabled={processing}
                    onCheckedChange={(v) => onChange('is_active', v)}
                />
            </div>
        </Card>
    );
}
