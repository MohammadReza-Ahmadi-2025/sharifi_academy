import { Card } from '@/Components/Admin/shared/ui/card';
import { Switch } from '@/Components/Admin/shared/ui/switch';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
import { reorderByIndex } from '@/Components/shared/reorder-list';
import { cn } from '@/lib/utils';
import { GripVertical, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';

type Option = { value: string; label: string };

export type MenuItemFormData = {
    id?: number;
    title: string;
    type: string;
    url: string;
    reference_id: number | null;
    sort: number;
    status: boolean;
    target_blank: boolean;
};

export type MenuFormData = {
    title: string;
    url: string;
    icon: string;
    location: string;
    status: boolean;
    items: MenuItemFormData[];
};

type Props = {
    data: MenuFormData;
    errors: Record<string, string>;
    locationOptions: Option[];
    itemTypeOptions: Option[];
    processing?: boolean;
    onChange: (key: keyof MenuFormData, value: unknown) => void;
    onItemsChange: (items: MenuItemFormData[]) => void;
};

export function MenuFormFields({
    data,
    errors,
    locationOptions,
    itemTypeOptions,
    processing = false,
    onChange,
    onItemsChange,
}: Props) {
    const [itemDragIndex, setItemDragIndex] = useState<number | null>(null);
    const [itemDropIndex, setItemDropIndex] = useState<number | null>(null);
    const locationSelectOptions = mapToSelectOptions(locationOptions);
    const typeSelectOptions = mapToSelectOptions(itemTypeOptions);

    const updateItem = (index: number, patch: Partial<MenuItemFormData>) => {
        const next = data.items.map((item, i) => (i === index ? { ...item, ...patch } : item));
        onItemsChange(next);
    };

    const addItem = () => {
        onItemsChange([
            ...data.items,
            {
                title: '',
                type: itemTypeOptions[0]?.value ?? 'external',
                url: '',
                reference_id: null,
                sort: data.items.length,
                status: true,
                target_blank: false,
            },
        ]);
    };

    const removeItem = (index: number) => {
        onItemsChange(data.items.filter((_, i) => i !== index));
    };

    return (
        <div className="space-y-6">
            <Card className="space-y-4 p-6">
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">گروه منو</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    <FormField label="عنوان" required error={errors.title}>
                        <AdminInput
                            value={data.title}
                            disabled={processing}
                            onChange={(e) => onChange('title', e.target.value)}
                        />
                    </FormField>
                    <FormField label="محل نمایش" required error={errors.location}>
                        <Select
                            isDisabled={processing}
                            options={locationSelectOptions}
                            value={findSelectOption(locationSelectOptions, data.location)}
                            onChange={(option) => onChange('location', option?.value ?? '')}
                        />
                    </FormField>
                    <FormField label="لینک عنوان (اختیاری)" error={errors.url}>
                        <AdminInput
                            dir="ltr"
                            className="text-left"
                            value={data.url}
                            disabled={processing}
                            onChange={(e) => onChange('url', e.target.value)}
                        />
                    </FormField>
                    <FormField label="آیکن (کلاس یا نام)" error={errors.icon}>
                        <AdminInput
                            value={data.icon}
                            disabled={processing}
                            onChange={(e) => onChange('icon', e.target.value)}
                        />
                    </FormField>
                </div>
                <Switch
                    label="فعال"
                    checked={data.status}
                    disabled={processing}
                    onCheckedChange={(v) => onChange('status', v)}
                />
            </Card>

            <Card className="space-y-4 p-6">
                <div className="flex items-center justify-between gap-4">
                    <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">آیتم‌های منو</h2>
                    <AdminButton type="button" variant="outline" size="sm" disabled={processing} onClick={addItem}>
                        <Plus className="size-4" />
                        آیتم
                    </AdminButton>
                </div>
                {data.items.length === 0 ? (
                    <p className="text-sm text-slate-500">هنوز آیتمی اضافه نشده است.</p>
                ) : (
                    <div className="space-y-4">
                        {data.items.length > 1 && (
                            <p className="text-sm text-slate-500">برای تغییر ترتیب آیتم‌ها، با دستگیره بکشید.</p>
                        )}
                        {data.items.map((item, index) => {
                            const canDragItems = data.items.length > 1 && !processing;

                            return (
                            <div
                                key={item.id ?? `new-${index}`}
                                className={cn(
                                    'rounded-lg border border-slate-200 p-4 dark:border-slate-700',
                                    itemDragIndex === index && 'opacity-50',
                                    itemDropIndex === index &&
                                        itemDragIndex !== index &&
                                        'bg-slate-50 dark:bg-slate-900/40',
                                )}
                                draggable={canDragItems}
                                onDragStart={() => canDragItems && setItemDragIndex(index)}
                                onDragEnd={() => {
                                    setItemDragIndex(null);
                                    setItemDropIndex(null);
                                }}
                                onDragOver={(e) => {
                                    if (!canDragItems || itemDragIndex === null) {
                                        return;
                                    }
                                    e.preventDefault();
                                    setItemDropIndex(index);
                                }}
                                onDrop={() => {
                                    if (itemDragIndex === null || itemDragIndex === index) {
                                        setItemDragIndex(null);
                                        setItemDropIndex(null);

                                        return;
                                    }
                                    onItemsChange(reorderByIndex(data.items, itemDragIndex, index));
                                    setItemDragIndex(null);
                                    setItemDropIndex(null);
                                }}
                            >
                                <div className="mb-3 flex items-center justify-between gap-2">
                                    <div className="flex items-center gap-2">
                                        {canDragItems ? (
                                            <span
                                                className="cursor-grab text-slate-400 active:cursor-grabbing"
                                                aria-hidden
                                            >
                                                <GripVertical className="size-4" />
                                            </span>
                                        ) : null}
                                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">
                                            آیتم {index + 1}
                                        </span>
                                    </div>
                                    <AdminButton
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        disabled={processing}
                                        onClick={() => removeItem(index)}
                                    >
                                        <Trash2 className="size-4 text-red-600" />
                                    </AdminButton>
                                </div>
                                <div className="grid gap-4 md:grid-cols-2">
                                    <FormField label="عنوان" required error={errors[`items.${index}.title`]}>
                                        <AdminInput
                                            value={item.title}
                                            disabled={processing}
                                            onChange={(e) => updateItem(index, { title: e.target.value })}
                                        />
                                    </FormField>
                                    <FormField label="نوع" required error={errors[`items.${index}.type`]}>
                                        <Select
                                            isDisabled={processing}
                                            options={typeSelectOptions}
                                            value={findSelectOption(typeSelectOptions, item.type)}
                                            onChange={(option) => updateItem(index, { type: option?.value ?? '' })}
                                        />
                                    </FormField>
                                    {(item.type === 'external' || item.type === 'internal') && (
                                        <FormField
                                            label={item.type === 'external' ? 'آدرس URL' : 'مسیر داخلی'}
                                            className="md:col-span-2"
                                            error={errors[`items.${index}.url`]}
                                        >
                                            <AdminInput
                                                dir="ltr"
                                                className="text-left"
                                                value={item.url}
                                                disabled={processing}
                                                onChange={(e) => updateItem(index, { url: e.target.value })}
                                            />
                                        </FormField>
                                    )}
                                    {(item.type === 'category_product' || item.type === 'category_course') && (
                                        <FormField label="شناسه دسته" error={errors[`items.${index}.reference_id`]}>
                                            <AdminInput
                                                type="number"
                                                min={1}
                                                value={item.reference_id ?? ''}
                                                disabled={processing}
                                                onChange={(e) =>
                                                    updateItem(index, {
                                                        reference_id: e.target.value ? Number(e.target.value) : null,
                                                    })
                                                }
                                            />
                                        </FormField>
                                    )}
                                </div>
                                <div className="mt-3 flex flex-wrap gap-6">
                                    <Switch
                                        label="فعال"
                                        checked={item.status}
                                        disabled={processing}
                                        onCheckedChange={(v) => updateItem(index, { status: v })}
                                    />
                                    <Switch
                                        label="تب جدید"
                                        checked={item.target_blank}
                                        disabled={processing}
                                        onCheckedChange={(v) => updateItem(index, { target_blank: v })}
                                    />
                                </div>
                            </div>
                            );
                        })}
                    </div>
                )}
            </Card>
        </div>
    );
}
