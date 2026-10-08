import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

export type WarehouseFormData = {
    name: string;
    code: string;
    address: string;
    phone: string;
    is_default: boolean;
    is_active: boolean;
};

type Props = {
    data: WarehouseFormData;
    errors: Partial<Record<string, string>>;
    onChange: (key: keyof WarehouseFormData, value: string | boolean) => void;
};

export function WarehouseFormFields({ data, errors, onChange }: Props) {
    return (
        <div className="w-full space-y-4">
            <FormField label="نام انبار" error={errors.name} required>
                <Input value={data.name} onChange={(e) => onChange('name', e.target.value)} />
            </FormField>
            <FormField label="کد" error={errors.code} required>
                <Input dir="ltr" value={data.code} onChange={(e) => onChange('code', e.target.value)} />
            </FormField>
            <FormField label="آدرس" error={errors.address}>
                <Textarea rows={3} value={data.address} onChange={(e) => onChange('address', e.target.value)} />
            </FormField>
            <FormField label="تلفن" error={errors.phone}>
                <Input dir="ltr" value={data.phone} onChange={(e) => onChange('phone', e.target.value)} />
            </FormField>
            <FormField label="انبار پیش‌فرض" error={errors.is_default}>
                <Switch checked={data.is_default} onCheckedChange={(v) => onChange('is_default', v)} />
            </FormField>
            <FormField label="فعال" error={errors.is_active}>
                <Switch checked={data.is_active} onCheckedChange={(v) => onChange('is_active', v)} />
            </FormField>
        </div>
    );
}
