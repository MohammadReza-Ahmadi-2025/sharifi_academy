import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useState } from 'react';

type Props = {
    warehouses: { data: { id: number; name: string; code: string; is_default: boolean; is_active: boolean }[]; current_page: number; last_page: number; per_page: number; total: number };
    filters: { search: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function WarehousesIndex({ warehouses, filters, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const apply = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get('/admin/catalog/warehouses', { search: search || undefined, per_page: filters.per_page, ...overrides }, { preserveState: true, replace: true });
    };

    return (
        <AdminLayout>
            <Head title="انبارها" />
            <PageHeader title="انبارها" actions={abilities.create ? <AdminButton href="/admin/catalog/warehouses/create"><Plus className="size-4" />انبار جدید</AdminButton> : null} />
            <AdminListFilters search={search} onSearchChange={setSearch} onSubmit={(e: FormEvent) => { e.preventDefault(); apply({ page: 1 }); }} onClear={() => { setSearch(''); router.get('/admin/catalog/warehouses'); }} searchPlaceholder="جستجو نام یا کد…" />
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader><TableRow><TableHead>نام</TableHead><TableHead>کد</TableHead><TableHead>وضعیت</TableHead><TableHead className="w-28" /></TableRow></TableHeader>
                    <TableBody>
                        {warehouses.data.length === 0 ? <TableEmptyState colSpan={4} message="انباری ثبت نشده." /> : warehouses.data.map((row) => (
                            <TableRow key={row.id}>
                                <TableCell className="font-medium">{row.name}{row.is_default ? <Badge className="ms-2">پیش‌فرض</Badge> : null}</TableCell>
                                <TableCell dir="ltr" className="font-mono text-sm">{row.code}</TableCell>
                                <TableCell><Badge variant={row.is_active ? 'default' : 'secondary'}>{row.is_active ? 'فعال' : 'غیرفعال'}</Badge></TableCell>
                                <TableCell><div className="flex gap-1">{abilities.update && <AdminButton variant="ghost" size="sm" href={`/admin/catalog/warehouses/${row.id}/edit`}><Pencil className="size-4" /></AdminButton>}{abilities.delete && <AdminButton variant="ghost" size="sm" onClick={() => confirmToast('حذف شود؟', () => router.delete(`/admin/catalog/warehouses/${row.id}`))}><Trash2 className="size-4 text-red-600" /></AdminButton>}</div></TableCell>
                            </TableRow>
                        ))}
                    </TableBody>
                </Table>
                <SimpleTablePagination className="border-t" currentPage={warehouses.current_page} lastPage={warehouses.last_page} total={warehouses.total} perPage={warehouses.per_page} from={null} to={null} itemLabel="انبار" onPageChange={(page) => apply({ page })} onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })} />
            </Card>
        </AdminLayout>
    );
    }
