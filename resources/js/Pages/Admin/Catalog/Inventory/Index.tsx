import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Input } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Props = {
    items: { data: { id: number; warehouse: string | null; warehouse_id: number; variant_id: number; sku: string | null; product_title: string | null; stock: number; reserved: number; available: number }[]; current_page: number; last_page: number; per_page: number; total: number };
    warehouses: { id: number; name: string }[];
    filters: { search: string; warehouse_id: string; per_page: number };
    abilities: { adjust: boolean };
};

export default function InventoryIndex({ items, warehouses, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [warehouseId, setWarehouseId] = useState(filters.warehouse_id);
    const adjust = useForm({ variant_id: '', warehouse_id: '', stock: 0 });

    const apply = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get('/admin/catalog/inventory', { search: search || undefined, warehouse_id: warehouseId || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout>
            <Head title="موجودی انبار" />
            <PageHeader title="موجودی انبار" />
            {abilities.adjust && (
                <Card className="mt-4 p-4">
                    <h2 className="mb-3 font-semibold">تنظیم موجودی</h2>
                    <form onSubmit={(e) => { e.preventDefault(); adjust.post('/admin/catalog/inventory/adjust', { preserveScroll: true }); }} className="grid gap-3 md:grid-cols-4">
                        <FormField label="شناسه واریانت" error={adjust.errors.variant_id}><Input type="number" value={adjust.data.variant_id} onChange={(e) => adjust.setData('variant_id', e.target.value)} /></FormField>
                        <FormField label="انبار" error={adjust.errors.warehouse_id}>
                            <AdminFilterSelect value={adjust.data.warehouse_id} onChange={(e) => adjust.setData('warehouse_id', e.target.value)} placeholder="پیش‌فرض" options={warehouses.map((w) => ({ value: String(w.id), label: w.name }))} />
                        </FormField>
                        <FormField label="موجودی" error={adjust.errors.stock}><Input type="number" min={0} value={adjust.data.stock} onChange={(e) => adjust.setData('stock', Number(e.target.value))} /></FormField>
                        <div className="flex items-end"><AdminButton type="submit" disabled={adjust.processing}>اعمال</AdminButton></div>
                    </form>
                </Card>
            )}
            <AdminListFilters className="mt-4" search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); setWarehouseId(''); router.get('/admin/catalog/inventory'); }} searchPlaceholder="جستجو SKU یا محصول…">
                <AdminFilterSelect value={warehouseId} onChange={(e) => setWarehouseId(e.target.value)} placeholder="همه انبارها" options={warehouses.map((w) => ({ value: String(w.id), label: w.name }))} />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader><TableRow><TableHead>محصول</TableHead><TableHead>SKU</TableHead><TableHead>انبار</TableHead><TableHead>موجودی</TableHead><TableHead>رزرو</TableHead><TableHead>قابل فروش</TableHead></TableRow></TableHeader>
                    <TableBody>
                        {items.data.length === 0 ? <TableEmptyState colSpan={6} message="رکوردی نیست." /> : items.data.map((row) => (
                            <TableRow key={row.id}><TableCell>{row.product_title ?? '—'}</TableCell><TableCell dir="ltr" className="font-mono text-sm">{row.sku}</TableCell><TableCell>{row.warehouse}</TableCell><TableCell>{row.stock}</TableCell><TableCell>{row.reserved}</TableCell><TableCell className="font-semibold">{row.available}</TableCell></TableRow>
                        ))}
                    </TableBody>
                </Table>
                <SimpleTablePagination className="border-t" currentPage={items.current_page} lastPage={items.last_page} total={items.total} perPage={items.per_page} from={null} to={null} itemLabel="ردیف" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
}
