import {
    emptyVariantFormValues,
    ProductVariantFormFields,
    type VariantFormValues,
} from '@/Components/Admin/Catalog/ProductVariantFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { Dialog } from '@/Components/Admin/shared/ui/dialog';
import { formatNumberInput } from '@/Components/Admin/shared/lib/format-number';
import { reorderById } from '@/Components/shared/reorder-list';
import { cn } from '@/lib/utils';
import { router, useForm } from '@inertiajs/react';
import { GripVertical, Package, Pencil, Plus, Trash2 } from 'lucide-react';
import { FormEvent, useEffect, useMemo, useState } from 'react';

export type VariantRow = {
    id: number;
    title: string | null;
    sku: string | null;
    barcode: string | null;
    status: boolean;
    display_mode: string;
    display_mode_label: string | null;
    sort: number;
    price: number | null;
    final_price: number | null;
    stock: number;
    label: string;
};

type Option = { value: string; label: string };

type Props = {
    productId: number;
    variants: VariantRow[];
    displayModeOptions: Option[];
    canManage: boolean;
};

type DialogMode = 'create' | 'edit' | null;

function rowToFormValues(row: VariantRow): VariantFormValues {
    return {
        title: row.title ?? '',
        sku: row.sku ?? '',
        barcode: row.barcode ?? '',
        status: row.status,
        display_mode: row.display_mode,
        price: row.price ?? '',
        final_price: row.final_price ?? row.price ?? '',
        stock: row.stock,
    };
}

function formatPrice(amount: number | null): string {
    if (amount == null) {
        return '—';
    }

    return `${formatNumberInput(String(amount))} ریال`;
}

export function ProductVariantPanel({ productId, variants, displayModeOptions, canManage }: Props) {
    const baseUrl = `/admin/catalog/products/${productId}/variants`;
    const [dialogMode, setDialogMode] = useState<DialogMode>(null);
    const [editingId, setEditingId] = useState<number | null>(null);
    const [dragVariantId, setDragVariantId] = useState<number | null>(null);
    const [dropVariantId, setDropVariantId] = useState<number | null>(null);
    const [orderedIds, setOrderedIds] = useState(() => variants.map((v) => v.id));

    const form = useForm<VariantFormValues>({ ...emptyVariantFormValues });

    useEffect(() => {
        setOrderedIds(variants.map((v) => v.id));
    }, [variants]);

    const orderedVariants = useMemo(
        () =>
            orderedIds
                .map((id) => variants.find((v) => v.id === id))
                .filter((row): row is VariantRow => row !== undefined),
        [orderedIds, variants],
    );

    if (!canManage) {
        return null;
    }

    const closeDialog = () => {
        setDialogMode(null);
        setEditingId(null);
        form.clearErrors();
        form.setData({ ...emptyVariantFormValues });
    };

    const openCreate = () => {
        form.clearErrors();
        form.setData({ ...emptyVariantFormValues });
        setEditingId(null);
        setDialogMode('create');
    };

    const openEdit = (row: VariantRow) => {
        form.clearErrors();
        form.setData(rowToFormValues(row));
        setEditingId(row.id);
        setDialogMode('edit');
    };

    const submitDialog = (event: FormEvent) => {
        event.preventDefault();

        const payload = {
            ...form.data,
            price: form.data.price === '' ? 0 : form.data.price,
            stock: form.data.stock === '' ? 0 : form.data.stock,
        };

        if (dialogMode === 'create') {
            form.transform(() => payload).post(baseUrl, {
                preserveScroll: true,
                onSuccess: closeDialog,
            });

            return;
        }

        if (dialogMode === 'edit' && editingId !== null) {
            form.transform(() => payload).put(`${baseUrl}/${editingId}`, {
                preserveScroll: true,
                onSuccess: closeDialog,
            });
        }
    };

    const persistVariantOrder = (nextIds: number[]) => {
        setOrderedIds(nextIds);
        router.put(`${baseUrl}/reorder`, { variant_ids: nextIds }, { preserveScroll: true });
    };

    const handleVariantDrop = (targetId: number) => {
        if (dragVariantId === null || dragVariantId === targetId) {
            setDragVariantId(null);
            setDropVariantId(null);

            return;
        }

        const fromKey = String(dragVariantId);
        const toKey = String(targetId);
        const items = orderedIds.map((id) => ({ id: String(id) }));
        const reordered = reorderById(items, fromKey, toKey).map((item) => Number(item.id));
        persistVariantOrder(reordered);
        setDragVariantId(null);
        setDropVariantId(null);
    };

    const removeVariant = (row: VariantRow) => {
        if (!window.confirm(`تنوع «${row.label}» حذف شود؟`)) {
            return;
        }
        router.delete(`${baseUrl}/${row.id}`, { preserveScroll: true });
    };

    const dialogTitle = dialogMode === 'create' ? 'افزودن تنوع جدید' : 'ویرایش تنوع';

    return (
        <>
            <Card className="mt-8 overflow-hidden p-0">
                <div className="flex flex-col gap-4 border-b border-slate-100 p-6 dark:border-slate-800 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-start gap-3">
                        <div className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-slate-100 dark:bg-slate-800">
                            <Package className="h-5 w-5 text-slate-600 dark:text-slate-300" />
                        </div>
                        <div>
                            <h2 className="text-base font-semibold text-slate-900 dark:text-slate-100">
                                تنوع‌های فروش
                                <span className="ms-2 text-sm font-normal text-slate-500">({variants.length})</span>
                            </h2>
                            <p className="mt-0.5 text-sm text-slate-500 dark:text-slate-400">
                                خرید مشتری روی تنوع است؛ هر تنوع قیمت و موجودی جدا دارد.
                            </p>
                        </div>
                    </div>
                    <AdminButton type="button" onClick={openCreate} className="shrink-0 gap-2">
                        <Plus className="h-4 w-4" />
                        تنوع جدید
                    </AdminButton>
                </div>

                <div className="p-6">
                    {orderedVariants.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-slate-200 px-6 py-12 text-center dark:border-slate-700">
                            <Package className="mx-auto h-10 w-10 text-slate-300 dark:text-slate-600" />
                            <p className="mt-3 text-sm font-medium text-slate-700 dark:text-slate-300">
                                هنوز تنوعی ثبت نشده
                            </p>
                            <p className="mt-1 text-sm text-slate-500">اولین تنوع را اضافه کنید تا محصول قابل فروش شود.</p>
                            <AdminButton type="button" className="mt-4" size="sm" onClick={openCreate}>
                                افزودن تنوع
                            </AdminButton>
                        </div>
                    ) : (
                        <ul className="space-y-3">
                            {orderedVariants.map((row, index) => {
                                const hasDiscount =
                                    row.price != null &&
                                    row.final_price != null &&
                                    row.final_price < row.price;
                                const isDraggable = orderedVariants.length > 1;

                                return (
                                    <li
                                        key={row.id}
                                        draggable={isDraggable}
                                        onDragStart={() => setDragVariantId(row.id)}
                                        onDragEnd={() => {
                                            setDragVariantId(null);
                                            setDropVariantId(null);
                                        }}
                                        onDragOver={(e) => {
                                            if (!isDraggable || dragVariantId === null) {
                                                return;
                                            }
                                            e.preventDefault();
                                            setDropVariantId(row.id);
                                        }}
                                        onDrop={(e) => {
                                            e.preventDefault();
                                            handleVariantDrop(row.id);
                                        }}
                                        className={cn(
                                            'group flex flex-col gap-4 rounded-xl border border-slate-200 bg-white p-4 transition-shadow sm:flex-row sm:items-center dark:border-slate-800 dark:bg-slate-950/40',
                                            dragVariantId === row.id && 'opacity-50',
                                            dropVariantId === row.id &&
                                                dragVariantId !== row.id &&
                                                'border-slate-400 ring-2 ring-slate-200 dark:ring-slate-700',
                                            editingId === row.id && dialogMode === 'edit' && 'border-slate-900 dark:border-slate-300',
                                        )}
                                    >
                                        <div className="flex min-w-0 flex-1 items-start gap-3">
                                            {isDraggable ? (
                                                <span
                                                    className="mt-1 inline-flex cursor-grab text-slate-400 active:cursor-grabbing"
                                                    title="جابه‌جایی"
                                                >
                                                    <GripVertical className="size-4" />
                                                </span>
                                            ) : (
                                                <span className="mt-1 inline-flex w-4" />
                                            )}
                                            <div className="min-w-0 flex-1">
                                                <div className="flex flex-wrap items-center gap-2">
                                                    <span className="font-medium text-slate-900 dark:text-slate-100">
                                                        {row.label}
                                                    </span>
                                                    <span className="text-xs text-slate-400">#{index + 1}</span>
                                                    {!row.status ? (
                                                        <Badge variant="secondary">غیرفعال</Badge>
                                                    ) : (
                                                        <Badge variant="success">فعال</Badge>
                                                    )}
                                                    {row.display_mode !== 'normal' ? (
                                                        <Badge variant="warning">
                                                            {row.display_mode_label ?? row.display_mode}
                                                        </Badge>
                                                    ) : null}
                                                    {row.stock === 0 ? (
                                                        <Badge variant="destructive">ناموجود</Badge>
                                                    ) : null}
                                                </div>
                                                <div className="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500 dark:text-slate-400">
                                                    {row.sku ? (
                                                        <span dir="ltr" className="font-mono">SKU: {row.sku}</span>
                                                    ) : null}
                                                    <span>موجودی: {formatNumberInput(String(row.stock))}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div className="flex shrink-0 flex-col items-end gap-2 sm:items-end">
                                            <div className="text-left" dir="ltr">
                                                {hasDiscount ? (
                                                    <div className="flex flex-col items-end gap-0.5">
                                                        <span className="text-xs text-slate-400 line-through">
                                                            {formatPrice(row.price)}
                                                        </span>
                                                        <span className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                            {formatPrice(row.final_price)}
                                                        </span>
                                                    </div>
                                                ) : (
                                                    <span className="text-sm font-semibold text-slate-900 dark:text-slate-100">
                                                        {formatPrice(row.final_price ?? row.price)}
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex gap-1">
                                                <AdminButton
                                                    type="button"
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => openEdit(row)}
                                                >
                                                    <Pencil className="size-4" />
                                                    <span className="sr-only">ویرایش</span>
                                                </AdminButton>
                                                {variants.length > 1 ? (
                                                    <AdminButton
                                                        type="button"
                                                        variant="ghost"
                                                        size="sm"
                                                        onClick={() => removeVariant(row)}
                                                    >
                                                        <Trash2 className="size-4 text-red-600" />
                                                        <span className="sr-only">حذف</span>
                                                    </AdminButton>
                                                ) : null}
                                            </div>
                                        </div>
                                    </li>
                                );
                            })}
                        </ul>
                    )}

                    {orderedVariants.length > 1 ? (
                        <p className="mt-4 text-xs text-slate-500">
                            برای تغییر ترتیب نمایش، ردیف‌ها را با دستگیره جابه‌جا کنید.
                        </p>
                    ) : null}
                </div>
            </Card>

            <Dialog
                isOpen={dialogMode !== null}
                onClose={closeDialog}
                title={dialogTitle}
                panelClassName="max-w-2xl"
            >
                <form onSubmit={submitDialog} className="space-y-6">
                    <ProductVariantFormFields
                        data={form.data}
                        errors={form.errors}
                        displayModeOptions={displayModeOptions}
                        processing={form.processing}
                        onChange={(key, value) => form.setData(key, value as never)}
                    />
                    <div className="flex flex-wrap gap-2 border-t border-slate-100 pt-4 dark:border-slate-800">
                        <AdminButton type="submit" disabled={form.processing}>
                            {dialogMode === 'create' ? 'افزودن تنوع' : 'ذخیره تغییرات'}
                        </AdminButton>
                        <AdminButton type="button" variant="ghost" disabled={form.processing} onClick={closeDialog}>
                            انصراف
                        </AdminButton>
                    </div>
                </form>
            </Dialog>
        </>
    );
}
