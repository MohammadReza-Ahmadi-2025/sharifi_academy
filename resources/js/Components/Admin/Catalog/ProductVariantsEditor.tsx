import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { swatchColor } from '@/Components/Admin/Catalog/variant-attributes';
import {
    addEmptyVariantRow,
    applyBulkToRows,
    calculateSalePrice,
    duplicateVariantRow,
    emptyBulkState,
    generateFromColorSelection,
    hasDuplicateCombination,
    type VariantBulkState,
    type VariantEditorRow,
} from '@/Components/Admin/Catalog/product-variants-logic';
import { cn } from '@/lib/utils';
import { Copy, Trash2 } from 'lucide-react';
import { useMemo, useState } from 'react';

type Option = { value: string; label: string };

type Props = {
    mode: 'create' | 'edit';
    variantAttributes: CatalogVariantAttribute[];
    displayModeOptions: Option[];
    rows: VariantEditorRow[];
    onChange: (rows: VariantEditorRow[]) => void;
    disabled?: boolean;
};

const inputClass =
    'w-full min-w-[4.5rem] rounded-lg border border-gray-300 bg-white px-2.5 py-2 text-sm text-gray-800 shadow-sm placeholder:text-gray-400 focus:border-brand-300 focus:outline-none focus:ring-2 focus:ring-brand-500/15 dark:border-gray-600 dark:bg-gray-800 dark:text-white/90 dark:placeholder:text-white/30';

const cellInputClass =
    'w-full min-w-[5rem] rounded border border-gray-300 bg-white px-2 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white/90';

export function ProductVariantsEditor({
    mode,
    variantAttributes,
    displayModeOptions,
    rows,
    onChange,
    disabled = false,
}: Props) {
    const [bulk, setBulk] = useState<VariantBulkState>(emptyBulkState());
    const [duplicateIndex, setDuplicateIndex] = useState<number | null>(null);

    const selectedValueIds = useMemo(() => {
        const ids: number[] = [];
        for (const row of rows) {
            for (const valueId of Object.values(row.attributes)) {
                if (valueId !== '') {
                    ids.push(Number(valueId));
                }
            }
        }
        return ids;
    }, [rows]);

    const updateRow = (index: number, patch: Partial<VariantEditorRow>) => {
        const next = rows.map((row, i) => {
            if (i !== index) {
                return row;
            }
            const merged = { ...row, ...patch };
            if ('attributes' in patch) {
                const nameParts: string[] = [];
                let code: string | null = null;
                for (const attr of variantAttributes) {
                    const valueId = merged.attributes[String(attr.id)];
                    if (!valueId) {
                        continue;
                    }
                    const value = attr.values.find((v) => String(v.id) === valueId);
                    if (value) {
                        nameParts.push(value.value);
                        if (attr.slug === 'color') {
                            code = swatchColor(value);
                        }
                    }
                }
                merged.name = nameParts.length > 0 ? nameParts.join(' / ') : 'واریانت جدید';
                merged.code = code;
            }
            return calculateSalePrice(merged);
        });
        onChange(next);
        if (hasDuplicateCombination(next, index)) {
            setDuplicateIndex(index);
        } else {
            setDuplicateIndex(null);
        }
    };

    const toggleColorValue = (valueId: number, attributeId: number) => {
        const has = selectedValueIds.includes(valueId);
        const nextSelected = has
            ? selectedValueIds.filter((id) => id !== valueId)
            : [...selectedValueIds, valueId];

        const attributeValueIds = variantAttributes.flatMap((attr) =>
            attr.id === attributeId
                ? attr.values.filter((v) => nextSelected.includes(v.id)).map((v) => v.id)
                : attr.values.filter((v) => nextSelected.includes(v.id)).map((v) => v.id),
        );

        onChange(generateFromColorSelection(variantAttributes, attributeValueIds, rows));
    };

    const removeRow = (index: number) => {
        if (rows.length <= 1) {
            return;
        }
        onChange(rows.filter((_, i) => i !== index));
    };

    return (
        <div className="col-span-2 space-y-8">
            {mode === 'create' && variantAttributes.length > 0 && (
                <div
                    className="rounded-xl border border-gray-200 p-5 dark:border-gray-700"
                >
                    <h3 className="mb-4 text-lg font-semibold text-gray-800 dark:text-white/90">
                        مشخصات قابل انتخاب
                    </h3>
                    <div className="space-y-4">
                        {variantAttributes.map((attribute) => (
                            <div key={attribute.id}>
                                <label className="mb-2 block text-sm text-gray-800 dark:text-white/90">
                                    {attribute.name}
                                </label>
                                <div className="flex gap-3 overflow-x-auto pb-2">
                                    {attribute.values.map((value) => {
                                        const checked = selectedValueIds.includes(value.id);
                                        const swatch = attribute.slug === 'color' ? swatchColor(value) : null;

                                        return (
                                            <label
                                                key={value.id}
                                                className={cn(
                                                    'flex shrink-0 cursor-pointer items-center gap-2 rounded-2xl border px-4 py-2 transition',
                                                    checked
                                                        ? 'border-blue-500 bg-blue-50 dark:border-blue-500 dark:bg-blue-900/50'
                                                        : 'border-gray-200 dark:border-gray-700',
                                                    disabled && 'cursor-not-allowed opacity-50',
                                                )}
                                            >
                                                <input
                                                    type="checkbox"
                                                    className="sr-only"
                                                    checked={checked}
                                                    disabled={disabled}
                                                    onChange={() => toggleColorValue(value.id, attribute.id)}
                                                />
                                                {swatch && (
                                                    <span
                                                        className="h-4 w-4 shrink-0 rounded-full border border-gray-200 dark:border-gray-700"
                                                        style={{ background: swatch }}
                                                    />
                                                )}
                                                <span>{value.value}</span>
                                            </label>
                                        );
                                    })}
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            )}

            <div className="overflow-hidden rounded-2xl border border-gray-200 dark:border-gray-700">
                <div className="border-b border-gray-200 bg-white px-5 py-4 dark:border-gray-700 dark:bg-gray-800/80">
                    <div className="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <h3 className="text-lg font-semibold text-gray-800 dark:text-white/90">واریانت‌ها</h3>
                            <p className="mt-0.5 text-sm text-gray-500 dark:text-gray-400">
                                {mode === 'create'
                                    ? 'قیمت، تخفیف و موجودی انبار را برای هر ترکیب مشخص کنید'
                                    : 'ویژگی‌ها، قیمت و موجودی هر تنوع را مدیریت کنید'}
                            </p>
                        </div>
                        <div className="flex flex-wrap items-center gap-2">
                            <span className="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300">
                                {rows.length} واریانت
                            </span>
                            {mode === 'edit' && (
                                <button
                                    type="button"
                                    disabled={disabled}
                                    onClick={() => onChange(addEmptyVariantRow(variantAttributes, rows))}
                                    className="rounded-lg bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50"
                                >
                                    افزودن ردیف
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                <div className="space-y-5 bg-white p-5 dark:bg-gray-800/40">
                    <VariantsBulkPanel
                        bulk={bulk}
                        displayModeOptions={displayModeOptions}
                        disabled={disabled}
                        onBulkChange={setBulk}
                        onApply={() => onChange(applyBulkToRows(rows, bulk))}
                    />

                    {duplicateIndex !== null && (
                        <div className="rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/30 dark:text-red-400">
                            ترکیب ویژگی‌های این ردیف با ردیف دیگر تکراری است.
                        </div>
                    )}

                    <div className="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
                        <table className="min-w-[1100px] w-full text-sm dark:text-gray-300">
                            <thead>
                                <tr className="border-b border-gray-200 bg-gray-100/80 text-xs text-gray-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                    {mode === 'create' ? (
                                        <>
                                            <th className="px-3 py-2 text-right font-semibold">واریانت</th>
                                            <th colSpan={7} className="border-x border-gray-200 px-3 py-2 text-center font-semibold dark:border-gray-700">
                                                قیمت‌گذاری و تخفیف
                                            </th>
                                            <th colSpan={2} className="bg-amber-100/60 px-3 py-2 text-center font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                                                موجودی انبار
                                            </th>
                                            <th className="px-3 py-2" />
                                        </>
                                    ) : (
                                        <>
                                            <th
                                                colSpan={variantAttributes.length + 2}
                                                className="px-3 py-2 text-right font-semibold"
                                            >
                                                شناسه و ویژگی‌ها
                                            </th>
                                            <th colSpan={6} className="border-x border-gray-200 px-3 py-2 text-center font-semibold dark:border-gray-700">
                                                قیمت‌گذاری و تخفیف
                                            </th>
                                            <th colSpan={2} className="bg-amber-100/60 px-3 py-2 text-center font-semibold text-amber-800 dark:bg-amber-950/30 dark:text-amber-300">
                                                موجودی انبار
                                            </th>
                                            <th className="px-3 py-2" />
                                        </>
                                    )}
                                </tr>
                                <tr className="bg-gray-50 text-xs text-gray-600 dark:bg-gray-800/60 dark:text-gray-300">
                                    {mode === 'edit' &&
                                        variantAttributes.map((attr) => (
                                            <th key={attr.id} className="px-2 py-2 text-right">{attr.name}</th>
                                        ))}
                                    {mode === 'create' && <th className="px-3 py-2 text-right">واریانت</th>}
                                    <th className="px-2 py-2 text-right">SKU</th>
                                    <th className="px-2 py-2 text-right">قیمت</th>
                                    <th className="px-2 py-2 text-right">نوع تخفیف</th>
                                    <th className="px-2 py-2 text-right">مقدار تخفیف</th>
                                    {mode === 'create' ? (
                                        <th className="px-2 py-2 text-right">قیمت نهایی</th>
                                    ) : (
                                        <>
                                            <th className="px-2 py-2 text-right">نوع نمایش</th>
                                            <th className="px-2 py-2 text-right">قیمت نهایی</th>
                                        </>
                                    )}
                                    <th className="px-2 py-2 text-right">شروع تخفیف</th>
                                    <th className="px-2 py-2 text-right">پایان تخفیف</th>
                                    {mode === 'create' && <th className="px-2 py-2 text-right">نوع نمایش</th>}
                                    <th className="bg-amber-50/50 px-2 py-2 text-right dark:bg-amber-950/20">موجودی</th>
                                    <th className="bg-amber-50/50 px-2 py-2 text-right dark:bg-amber-950/20">حداقل</th>
                                    <th className="px-2 py-2 text-center">عملیات</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                                {rows.map((row, index) => {
                                    const lowStock =
                                        row.min_stock > 0 &&
                                        Number(row.stock) < Number(row.min_stock);

                                    return (
                                        <tr
                                            key={row.key}
                                            className={cn(
                                                'hover:bg-gray-50/80 dark:hover:bg-gray-800/50',
                                                lowStock && 'bg-amber-50/30 dark:bg-amber-950/10',
                                                duplicateIndex === index && 'ring-1 ring-red-400',
                                            )}
                                        >
                                            {mode === 'edit' &&
                                                variantAttributes.map((attr) => (
                                                    <td key={attr.id} className="px-3 py-2">
                                                        <select
                                                            className={cellInputClass}
                                                            disabled={disabled}
                                                            value={row.attributes[String(attr.id)] ?? ''}
                                                            onChange={(e) =>
                                                                updateRow(index, {
                                                                    attributes: {
                                                                        ...row.attributes,
                                                                        [String(attr.id)]: e.target.value,
                                                                    },
                                                                })
                                                            }
                                                        >
                                                            <option value="">انتخاب</option>
                                                            {attr.values.map((val) => (
                                                                <option key={val.id} value={String(val.id)}>
                                                                    {val.value}
                                                                </option>
                                                            ))}
                                                        </select>
                                                    </td>
                                                ))}

                                            {mode === 'create' && (
                                                <td className="px-4 py-3 text-gray-800 dark:text-white/90">
                                                    <div className="flex items-center gap-2">
                                                        {row.code && (
                                                            <span
                                                                className="h-4 w-4 shrink-0 rounded-full border border-gray-300"
                                                                style={{ background: row.code }}
                                                            />
                                                        )}
                                                        <span>{row.name}</span>
                                                    </div>
                                                </td>
                                            )}

                                            <td className="px-3 py-2">
                                                <input
                                                    type="text"
                                                    className={cellInputClass}
                                                    disabled={disabled}
                                                    value={row.sku}
                                                    onChange={(e) => updateRow(index, { sku: e.target.value })}
                                                />
                                            </td>
                                            <td className="px-3 py-2">
                                                <input
                                                    type="number"
                                                    min={0}
                                                    className={cellInputClass}
                                                    disabled={disabled}
                                                    value={row.price}
                                                    onChange={(e) =>
                                                        updateRow(index, {
                                                            price: e.target.value === '' ? '' : Number(e.target.value),
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="px-2 py-3">
                                                <select
                                                    className={cellInputClass}
                                                    disabled={disabled}
                                                    value={row.discount_type}
                                                    onChange={(e) => {
                                                        const type = e.target.value as VariantEditorRow['discount_type'];
                                                        updateRow(index, {
                                                            discount_type: type,
                                                            ...(type === ''
                                                                ? {
                                                                      discount_value: '',
                                                                      sale_start: '',
                                                                      sale_end: '',
                                                                  }
                                                                : {}),
                                                        });
                                                    }}
                                                >
                                                    <option value="">انتخاب نوع</option>
                                                    <option value="percentage">درصدی</option>
                                                    <option value="fixed">مبلغ ثابت</option>
                                                </select>
                                            </td>
                                            <td className="px-2 py-3">
                                                <input
                                                    type="number"
                                                    min={0}
                                                    className={cellInputClass}
                                                    disabled={disabled || !row.discount_type}
                                                    value={row.discount_value}
                                                    onChange={(e) =>
                                                        updateRow(index, {
                                                            discount_value:
                                                                e.target.value === '' ? '' : Number(e.target.value),
                                                        })
                                                    }
                                                />
                                            </td>
                                            {mode === 'edit' && (
                                                <td className="px-3 py-2">
                                                    <select
                                                        className={cellInputClass}
                                                        disabled={disabled}
                                                        value={row.display_mode}
                                                        onChange={(e) =>
                                                            updateRow(index, { display_mode: e.target.value })
                                                        }
                                                    >
                                                        {displayModeOptions.map((opt) => (
                                                            <option key={opt.value} value={opt.value}>
                                                                {opt.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </td>
                                            )}
                                            <td className="px-4 py-3">
                                                <div
                                                    className="min-w-[6rem] rounded-xl border border-blue-100 bg-blue-50 px-3 py-2 text-center text-sm font-medium text-blue-700 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-300"
                                                >
                                                    {row.final_price !== '' ? row.final_price : '—'}
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">
                                                <input
                                                    type="text"
                                                    className={cellInputClass}
                                                    disabled={disabled || !row.discount_type}
                                                    value={row.sale_start}
                                                    placeholder="تاریخ"
                                                    onChange={(e) => updateRow(index, { sale_start: e.target.value })}
                                                />
                                            </td>
                                            <td className="px-4 py-3">
                                                <input
                                                    type="text"
                                                    className={cellInputClass}
                                                    disabled={disabled || !row.discount_type}
                                                    value={row.sale_end}
                                                    placeholder="تاریخ"
                                                    onChange={(e) => updateRow(index, { sale_end: e.target.value })}
                                                />
                                            </td>
                                            {mode === 'create' && (
                                                <td className="px-4 py-3">
                                                    <select
                                                        className={cellInputClass}
                                                        disabled={disabled}
                                                        value={row.display_mode}
                                                        onChange={(e) =>
                                                            updateRow(index, { display_mode: e.target.value })
                                                        }
                                                    >
                                                        {displayModeOptions.map((opt) => (
                                                            <option key={opt.value} value={opt.value}>
                                                                {opt.label}
                                                            </option>
                                                        ))}
                                                    </select>
                                                </td>
                                            )}
                                            <td className="bg-amber-50/40 px-2 py-2 dark:bg-amber-950/15">
                                                <input
                                                    type="number"
                                                    min={0}
                                                    className={cellInputClass}
                                                    disabled={disabled}
                                                    value={row.stock}
                                                    onChange={(e) =>
                                                        updateRow(index, {
                                                            stock: e.target.value === '' ? '' : Number(e.target.value),
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="bg-amber-50/40 px-2 py-2 dark:bg-amber-950/15">
                                                <input
                                                    type="number"
                                                    min={0}
                                                    className={cellInputClass}
                                                    disabled={disabled}
                                                    value={row.min_stock}
                                                    onChange={(e) =>
                                                        updateRow(index, {
                                                            min_stock: Number(e.target.value) || 0,
                                                        })
                                                    }
                                                />
                                            </td>
                                            <td className="px-2 py-2">
                                                <div className="flex items-center justify-center gap-1">
                                                    {mode === 'edit' && (
                                                        <button
                                                            type="button"
                                                            disabled={disabled}
                                                            title="کپی"
                                                            onClick={() => onChange(duplicateVariantRow(rows, index))}
                                                            className="rounded p-1.5 text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700"
                                                        >
                                                            <Copy className="h-4 w-4" />
                                                        </button>
                                                    )}
                                                    <button
                                                        type="button"
                                                        disabled={disabled || rows.length <= 1}
                                                        title="حذف"
                                                        onClick={() => removeRow(index)}
                                                        className="rounded p-1.5 text-red-500 hover:bg-red-50 disabled:opacity-40 dark:hover:bg-red-950/30"
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    );
}

function VariantsBulkPanel({
    bulk,
    displayModeOptions,
    disabled,
    onBulkChange,
    onApply,
}: {
    bulk: VariantBulkState;
    displayModeOptions: Option[];
    disabled: boolean;
    onBulkChange: (bulk: VariantBulkState) => void;
    onApply: () => void;
}) {
    const labelClass = 'mb-1 block text-[11px] font-medium text-gray-500 dark:text-gray-400';

    return (
        <div className="rounded-xl border border-dashed border-gray-300 bg-gray-50/70 p-4 dark:border-gray-600 dark:bg-gray-900/30">
            <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h4 className="text-sm font-semibold text-gray-800 dark:text-gray-200">
                        اعمال گروهی روی همه واریانت‌ها
                    </h4>
                    <p className="mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                        فیلدهای لازم را پر کنید و یک‌جا اعمال کنید
                    </p>
                </div>
                <button
                    type="button"
                    disabled={disabled}
                    onClick={onApply}
                    className="shrink-0 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600 disabled:opacity-50 dark:bg-brand-600"
                >
                    اعمال به همه
                </button>
            </div>
            <div className="grid grid-cols-2 gap-3 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
                <div>
                    <label className={labelClass}>قیمت (ریال)</label>
                    <input
                        type="number"
                        min={0}
                        className={inputClass}
                        value={bulk.price}
                        disabled={disabled}
                        onChange={(e) => onBulkChange({ ...bulk, price: e.target.value })}
                    />
                </div>
                <div>
                    <label className={labelClass}>نوع تخفیف</label>
                    <select
                        className={inputClass}
                        value={bulk.discount_type}
                        disabled={disabled}
                        onChange={(e) =>
                            onBulkChange({
                                ...bulk,
                                discount_type: e.target.value as VariantBulkState['discount_type'],
                            })
                        }
                    >
                        <option value="">—</option>
                        <option value="percentage">درصدی</option>
                        <option value="fixed">مبلغ ثابت</option>
                    </select>
                </div>
                <div>
                    <label className={labelClass}>مقدار تخفیف</label>
                    <input
                        type="number"
                        min={0}
                        className={inputClass}
                        disabled={disabled || !bulk.discount_type}
                        value={bulk.discount_value}
                        onChange={(e) => onBulkChange({ ...bulk, discount_value: e.target.value })}
                    />
                </div>
                <div>
                    <label className={labelClass}>نوع نمایش</label>
                    <select
                        className={inputClass}
                        value={bulk.display_mode}
                        disabled={disabled}
                        onChange={(e) => onBulkChange({ ...bulk, display_mode: e.target.value })}
                    >
                        <option value="">—</option>
                        {displayModeOptions.map((opt) => (
                            <option key={opt.value} value={opt.value}>{opt.label}</option>
                        ))}
                    </select>
                </div>
                <div>
                    <label className={labelClass}>شروع تخفیف</label>
                    <input
                        type="text"
                        className={inputClass}
                        disabled={disabled || !bulk.discount_type}
                        value={bulk.sale_start}
                        onChange={(e) => onBulkChange({ ...bulk, sale_start: e.target.value })}
                    />
                </div>
                <div>
                    <label className={labelClass}>پایان تخفیف</label>
                    <input
                        type="text"
                        className={inputClass}
                        disabled={disabled || !bulk.discount_type}
                        value={bulk.sale_end}
                        onChange={(e) => onBulkChange({ ...bulk, sale_end: e.target.value })}
                    />
                </div>
                <div>
                    <label className={labelClass}>موجودی</label>
                    <input
                        type="number"
                        min={0}
                        className={inputClass}
                        value={bulk.stock}
                        disabled={disabled}
                        onChange={(e) => onBulkChange({ ...bulk, stock: e.target.value })}
                    />
                </div>
                <div>
                    <label className={labelClass}>حداقل موجودی</label>
                    <input
                        type="number"
                        min={0}
                        className={inputClass}
                        value={bulk.min_stock}
                        disabled={disabled}
                        onChange={(e) => onBulkChange({ ...bulk, min_stock: e.target.value })}
                    />
                </div>
            </div>
        </div>
    );
}

/** Payload for Inertia form submit */
export function variantsPayloadForForm(rows: VariantEditorRow[]): Record<string, unknown>[] {
    return rows.map((row) => ({
        id: row.id ?? null,
        sku: row.sku || null,
        attributes: row.attributes,
        price: row.price === '' ? 0 : row.price,
        discount_type: row.discount_type || null,
        discount_value: row.discount_value === '' ? null : row.discount_value,
        final_price: row.final_price === '' ? null : row.final_price,
        display_mode: row.display_mode,
        sale_start: row.sale_start || null,
        sale_end: row.sale_end || null,
        stock: row.stock === '' ? 0 : row.stock,
        min_stock: row.min_stock,
    }));
}
