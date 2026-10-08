import {
    type CatalogVariantAttribute,
    swatchColor,
    variantValueChipClass,
} from '@/Components/Admin/Catalog/variant-attributes';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Select, type SelectOption } from '@/Components/Admin/shared/ui/select';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { cn } from '@/lib/utils';

type Props = {
    attributes: CatalogVariantAttribute[];
    valueIds: number[];
    disabled?: boolean;
    error?: string;
    onChange: (valueIds: number[]) => void;
};

function selectedIdForAttribute(attribute: CatalogVariantAttribute, valueIds: number[]): number | null {
    const match = attribute.values.find((row) => valueIds.includes(row.id));

    return match?.id ?? null;
}

export function ProductVariantAttributeFields({
    attributes,
    valueIds,
    disabled = false,
    error,
    onChange,
}: Props) {
    if (attributes.length === 0) {
        return (
            <p className="text-sm text-slate-500 dark:text-slate-400">
                ویژگی واریانتی در کاتالوگ تعریف نشده (مثلاً رنگ یا سایز).
            </p>
        );
    }

    const setForAttribute = (attributeId: number, nextValueId: number | null) => {
        const attribute = attributes.find((row) => row.id === attributeId);

        if (!attribute) {
            return;
        }

        const withoutAttribute = valueIds.filter(
            (id) => !attribute.values.some((value) => value.id === id),
        );

        onChange(nextValueId ? [...withoutAttribute, nextValueId] : withoutAttribute);
    };

    return (
        <div className="space-y-4">
            <p className="text-xs font-semibold uppercase tracking-wide text-slate-500 dark:text-slate-400">
                ویژگی‌های تنوع
            </p>
            {attributes.map((attribute) => {
                const selectedId = selectedIdForAttribute(attribute, valueIds);
                const isColor = attribute.type === 'color' || attribute.slug === 'color';

                if (isColor) {
                    return (
                        <FormField key={attribute.id} label={attribute.name} error={error}>
                            <div className="flex flex-wrap gap-2">
                                {attribute.values.map((row) => {
                                    const active = selectedId === row.id;
                                    const color = swatchColor(row);

                                    return (
                                        <button
                                            key={row.id}
                                            type="button"
                                            disabled={disabled}
                                            title={row.value}
                                            onClick={() => setForAttribute(attribute.id, active ? null : row.id)}
                                            className={cn(
                                                variantValueChipClass(active),
                                                'inline-flex items-center gap-2',
                                            )}
                                        >
                                            <span
                                                className="size-4 rounded-full border border-slate-300 dark:border-slate-600"
                                                style={{
                                                    backgroundColor: color ?? 'transparent',
                                                }}
                                            />
                                            {row.value}
                                        </button>
                                    );
                                })}
                            </div>
                        </FormField>
                    );
                }

                const options: SelectOption[] = mapToSelectOptions(
                    attribute.values.map((row) => ({ value: String(row.id), label: row.value })),
                );

                return (
                    <FormField key={attribute.id} label={attribute.name} error={error}>
                        <Select
                            isDisabled={disabled}
                            isClearable
                            options={options}
                            value={selectedId ? findSelectOption(options, String(selectedId)) : null}
                            placeholder={`انتخاب ${attribute.name}`}
                            onChange={(option) =>
                                setForAttribute(attribute.id, option?.value ? Number(option.value) : null)
                            }
                        />
                    </FormField>
                );
            })}
        </div>
    );
}
