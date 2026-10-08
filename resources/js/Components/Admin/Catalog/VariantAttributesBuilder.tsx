import {
    MAX_VARIANT_ATTRIBUTES,
    type CatalogVariantAttribute,
    type CatalogVariantAttributeValue,
    type VariantAttributeSelection,
    countAttributeCombinations,
    swatchColor,
    variantValueChipClass,
} from '@/Components/Admin/Catalog/variant-attributes';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Select } from '@/Components/Admin/shared/ui/select';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { Sparkles, X } from 'lucide-react';
import { useMemo, useState } from 'react';

type Props = {
    productId: number;
    attributes: CatalogVariantAttribute[];
    disabled?: boolean;
    /** داخل کارت تنوع‌ها؛ بدون Card جدا */
    embedded?: boolean;
};

function toFa(value: number): string {
    return value.toLocaleString('fa-IR');
}

function AttributeValuePicker({
    selection,
    attribute,
    disabled,
    onToggleValue,
    onSelectAll,
    onClear,
    onRemove,
}: {
    selection: VariantAttributeSelection;
    attribute: CatalogVariantAttribute;
    disabled: boolean;
    onToggleValue: (attributeId: number, valueId: number) => void;
    onSelectAll: (attributeId: number) => void;
    onClear: (attributeId: number) => void;
    onRemove: (attributeId: number) => void;
}) {
    const isColor = attribute.type === 'color' || attribute.slug === 'color';

    return (
        <div className="rounded-xl border border-slate-200 p-4 dark:border-slate-800">
            <div className="mb-3 flex flex-wrap items-center justify-between gap-2">
                <div className="flex items-baseline gap-2">
                    <h3 className="text-sm font-semibold text-slate-800 dark:text-slate-200">{selection.name}</h3>
                    <span className="font-mono text-xs text-slate-400" dir="ltr">{selection.slug}</span>
                </div>

                <div className="flex items-center gap-1">
                    {attribute.values.length > 0 && (
                        <AdminButton
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="h-8 px-2 text-xs"
                            disabled={disabled}
                            onClick={() => onSelectAll(selection.attributeId)}
                        >
                            انتخاب همه
                        </AdminButton>
                    )}
                    {selection.selectedValueIds.length > 0 && (
                        <AdminButton
                            type="button"
                            variant="ghost"
                            size="sm"
                            className="h-8 px-2 text-xs"
                            disabled={disabled}
                            onClick={() => onClear(selection.attributeId)}
                        >
                            پاک کردن
                        </AdminButton>
                    )}
                    <AdminButton
                        type="button"
                        variant="ghost"
                        size="sm"
                        className="h-8 px-2 text-rose-600 hover:bg-rose-50 hover:text-rose-700 dark:text-rose-400 dark:hover:bg-rose-950/20"
                        disabled={disabled}
                        onClick={() => onRemove(selection.attributeId)}
                        aria-label={`حذف ویژگی ${selection.name}`}
                    >
                        <X className="size-4" />
                    </AdminButton>
                </div>
            </div>

            {attribute.values.length === 0 && (
                <p className="mb-3 text-xs text-slate-500 dark:text-slate-400">
                    برای این ویژگی مقداری در کاتالوگ ثبت نشده است.
                </p>
            )}

            <div className="flex flex-wrap gap-2">
                {attribute.values.map((row) => {
                    const active = selection.selectedValueIds.includes(row.id);
                    const color = isColor ? swatchColor(row) : null;

                    return (
                        <button
                            key={row.id}
                            type="button"
                            disabled={disabled}
                            onClick={() => onToggleValue(selection.attributeId, row.id)}
                            className={cn(
                                variantValueChipClass(active),
                                'inline-flex items-center gap-2',
                            )}
                        >
                            {color ? (
                                <span
                                    className="size-3.5 shrink-0 rounded-full border border-slate-300/80 dark:border-slate-600"
                                    style={{ backgroundColor: color }}
                                />
                            ) : null}
                            {row.value}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}

export function VariantAttributesBuilder({
    productId,
    attributes,
    disabled = false,
    embedded = false,
}: Props) {
    const [selections, setSelections] = useState<VariantAttributeSelection[]>([]);
    const [processing, setProcessing] = useState(false);

    const usedIds = new Set(selections.map((row) => row.attributeId));
    const available = attributes.filter((row) => !usedIds.has(row.id));
    const canAddMore = selections.length < MAX_VARIANT_ATTRIBUTES;

    const combinationCount = countAttributeCombinations(selections);
    const readyCount = selections.filter((row) => row.selectedValueIds.length > 0).length;
    const canGenerate = readyCount > 0 && combinationCount > 0 && !disabled && !processing;

    const summary = selections
        .filter((row) => row.selectedValueIds.length > 0)
        .map((row) => `${toFa(row.selectedValueIds.length)} ${row.name}`)
        .join(' × ');

    const attributeSelectOptions = useMemo(
        () => available.map((row) => ({ value: String(row.id), label: row.name })),
        [available],
    );

    const addAttribute = (attributeId: number) => {
        const attribute = attributes.find((row) => row.id === attributeId);

        if (!attribute || !canAddMore) {
            return;
        }

        setSelections((current) => [
            ...current,
            {
                attributeId: attribute.id,
                slug: attribute.slug,
                name: attribute.name,
                type: attribute.type,
                selectedValueIds: [],
            },
        ]);
    };

    const removeSelection = (attributeId: number) => {
        setSelections((current) => current.filter((row) => row.attributeId !== attributeId));
    };

    const toggleValue = (attributeId: number, valueId: number) => {
        setSelections((current) =>
            current.map((row) => {
                if (row.attributeId !== attributeId) {
                    return row;
                }

                const exists = row.selectedValueIds.includes(valueId);

                return {
                    ...row,
                    selectedValueIds: exists
                        ? row.selectedValueIds.filter((id) => id !== valueId)
                        : [...row.selectedValueIds, valueId],
                };
            }),
        );
    };

    const selectAllValues = (attributeId: number) => {
        const attribute = attributes.find((row) => row.id === attributeId);

        if (!attribute) {
            return;
        }

        const ids = attribute.values.map((row: CatalogVariantAttributeValue) => row.id);

        setSelections((current) =>
            current.map((row) => (row.attributeId === attributeId ? { ...row, selectedValueIds: ids } : row)),
        );
    };

    const clearValues = (attributeId: number) => {
        setSelections((current) =>
            current.map((row) => (row.attributeId === attributeId ? { ...row, selectedValueIds: [] } : row)),
        );
    };

    const generate = () => {
        if (!canGenerate) {
            return;
        }

        setProcessing(true);

        router.post(
            `/admin/catalog/products/${productId}/variants/generate`,
            {
                selections: selections
                    .filter((row) => row.selectedValueIds.length > 0)
                    .map((row) => ({
                        attribute_id: row.attributeId,
                        attribute_value_ids: row.selectedValueIds,
                    })),
            },
            {
                preserveScroll: true,
                onFinish: () => setProcessing(false),
            },
        );
    };

    const content = (
        <div className="space-y-5">
            <div>
                <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">
                    ساخت واریانت از ویژگی‌ها
                </h2>
                <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    هر محصول حداقل یک واریانت دارد (معمولاً «پیش‌فرض»). اینجا با انتخاب رنگ، سایز و … همه ترکیب‌ها
                    ساخته می‌شوند — حداکثر {toFa(MAX_VARIANT_ATTRIBUTES)} ویژگی همزمان.
                </p>
            </div>

            {attributes.length === 0 ? (
                <p className="rounded-lg bg-slate-50 px-3 py-3 text-sm text-slate-600 dark:bg-slate-950 dark:text-slate-400">
                    ویژگی واریانتی در کاتالوگ نیست؛ می‌توانید از جدول پایین واریانت دستی با قیمت و موجودی اضافه کنید.
                </p>
            ) : (
                <>
                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <Select
                            className="w-full sm:w-72"
                            isSearchable
                            isDisabled={disabled || !canAddMore || attributeSelectOptions.length === 0}
                            options={attributeSelectOptions}
                            value={null}
                            placeholder={
                                canAddMore
                                    ? 'افزودن ویژگی (مثلاً رنگ)'
                                    : `حداکثر ${toFa(MAX_VARIANT_ATTRIBUTES)} ویژگی`
                            }
                            onChange={(option) => {
                                if (option?.value) {
                                    addAttribute(Number(option.value));
                                }
                            }}
                        />

                        {selections.length === 0 && (
                            <p className="text-xs text-slate-500 dark:text-slate-400">
                                اگر فقط یک قیمت دارید، واریانت پیش‌فرض را در جدول پایین ویرایش کنید.
                            </p>
                        )}
                    </div>

                    {selections.length > 0 && (
                        <div className="space-y-3">
                            {selections.map((selection) => {
                                const attribute = attributes.find((row) => row.id === selection.attributeId);

                                if (!attribute) {
                                    return null;
                                }

                                return (
                                    <AttributeValuePicker
                                        key={selection.attributeId}
                                        selection={selection}
                                        attribute={attribute}
                                        disabled={disabled}
                                        onToggleValue={toggleValue}
                                        onSelectAll={selectAllValues}
                                        onClear={clearValues}
                                        onRemove={removeSelection}
                                    />
                                );
                            })}
                        </div>
                    )}

                    {selections.length > 0 && (
                        <div className="flex flex-col gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4 sm:flex-row sm:items-center sm:justify-between dark:border-slate-800 dark:bg-slate-950">
                            <p className="text-sm text-slate-600 dark:text-slate-400">
                                {readyCount === 0 ? (
                                    'برای هر ویژگی حداقل یک مقدار انتخاب کنید'
                                ) : (
                                    <>
                                        <span className="font-medium text-slate-800 dark:text-slate-200">
                                            {summary}
                                        </span>
                                        {' = '}
                                        <span className="font-semibold text-slate-900 dark:text-slate-100">
                                            {toFa(combinationCount)} واریانت
                                        </span>
                                    </>
                                )}
                            </p>

                            <AdminButton
                                type="button"
                                className="w-full sm:w-auto"
                                disabled={!canGenerate}
                                onClick={generate}
                            >
                                <Sparkles className="ms-2 size-4" />
                                ساخت ترکیب‌ها
                            </AdminButton>
                        </div>
                    )}
                </>
            )}
        </div>
    );

    if (embedded) {
        return <div className="border-b border-slate-100 p-6 dark:border-slate-800">{content}</div>;
    }

    return <Card className="mt-8 space-y-0 p-6">{content}</Card>;
}
