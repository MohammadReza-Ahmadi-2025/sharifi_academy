import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';

type Props = {
    type: string;
    data: { value: string | boolean | null };
    errors: Record<string, string>;
    imageUrl: string | null;
    processing: boolean;
    onChange: (key: 'value', value: string | boolean) => void;
    onImageChange: (file: File | null) => void;
};

export function SettingFormFields({
    type,
    data,
    errors,
    imageUrl,
    processing,
    onChange,
    onImageChange,
}: Props) {
    if (type === 'textarea') {
        return (
            <FormField label="مقدار" error={errors.value}>
                <Textarea
                    rows={5}
                    value={typeof data.value === 'string' ? data.value : ''}
                    disabled={processing}
                    onChange={(e) => onChange('value', e.target.value)}
                />
            </FormField>
        );
    }

    if (type === 'switch') {
        return (
            <FormField label="وضعیت" error={errors.value}>
                <Switch
                    checked={Boolean(data.value)}
                    disabled={processing}
                    onCheckedChange={(checked) => onChange('value', checked)}
                />
            </FormField>
        );
    }

    if (type === 'color') {
        return (
            <FormField label="رنگ" error={errors.value}>
                <div className="flex items-center gap-3">
                    <input
                        type="color"
                        value={typeof data.value === 'string' && data.value ? data.value : '#00489e'}
                        disabled={processing}
                        onChange={(e) => onChange('value', e.target.value)}
                        className="h-10 w-14 cursor-pointer rounded border border-slate-300 bg-white p-1 dark:border-slate-700"
                    />
                    <AdminInput
                        value={typeof data.value === 'string' ? data.value : ''}
                        disabled={processing}
                        onChange={(e) => onChange('value', e.target.value)}
                        className="max-w-[10rem] font-mono"
                        dir="ltr"
                    />
                </div>
            </FormField>
        );
    }

    if (type === 'number') {
        return (
            <FormField label="مقدار عددی" error={errors.value}>
                <AdminInput
                    type="number"
                    value={data.value === null || data.value === '' ? '' : String(data.value)}
                    disabled={processing}
                    onChange={(e) => onChange('value', e.target.value)}
                    dir="ltr"
                />
            </FormField>
        );
    }

    if (type === 'image') {
        return (
            <FormField label="تصویر" error={errors.image}>
                {imageUrl ? (
                    <img
                        src={imageUrl}
                        alt=""
                        className="mb-3 max-h-32 rounded-lg border border-slate-200 object-contain dark:border-slate-700"
                    />
                ) : null}
                <input
                    type="file"
                    accept="image/*"
                    disabled={processing}
                    onChange={(e) => onImageChange(e.target.files?.[0] ?? null)}
                    className="block w-full text-sm text-slate-600 file:me-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-2 file:text-sm dark:text-slate-300 dark:file:bg-slate-800"
                />
            </FormField>
        );
    }

    return (
        <FormField label="مقدار" error={errors.value}>
            <AdminInput
                value={typeof data.value === 'string' ? data.value : ''}
                disabled={processing}
                onChange={(e) => onChange('value', e.target.value)}
            />
        </FormField>
    );
}
