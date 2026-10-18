import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import {
    Table,
    TableBody,
    TableCell,
    TableEmptyState,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    title: string;
    slug: string;
    sku: string | null;
    brand_title: string | null;
    status: boolean;
    is_visible: boolean;
    variants_count: number;
};

type Props = {
    products: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; status: string; visible: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function ProductsIndex({ products, filters, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const [visible, setVisible] = useState(filters.visible);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/catalog/products',
            {
                search: search || undefined,
                status: status || undefined,
                visible: visible || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        setVisible('');
        router.get('/admin/catalog/products', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="محصولات" />
            <PageHeader
                title="محصولات"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/catalog/products/create">
                            <Plus className="size-4" />
                            محصول جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان، اسلاگ یا SKU…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    placeholder="وضعیت انتشار"
                    options={[
                        { value: '1', label: 'فعال' },
                        { value: '0', label: 'غیرفعال' },
                    ]}
                />
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={visible}
                    onChange={(e) => setVisible(e.target.value)}
                    placeholder="نمایش در فروشگاه"
                    options={[
                        { value: '1', label: 'قابل مشاهده' },
                        { value: '0', label: 'مخفی' },
                    ]}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>محصول</TableHead>
                            <TableHead>برند</TableHead>
                            <TableHead>تنوع</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {products.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="محصولی ثبت نشده است." />
                        ) : (
                            products.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.title}</div>
                                        <div className="text-xs text-slate-500 font-mono">{row.sku ?? row.slug}</div>
                                    </TableCell>
                                    <TableCell>{row.brand_title ?? '—'}</TableCell>
                                    <TableCell>{row.variants_count}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status ? 'default' : 'secondary'}>
                                            {row.is_visible ? 'قابل نمایش' : 'مخفی'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/catalog/products/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        confirmToast('محصول حذف شود؟', () => router.delete(`/admin/catalog/products/${row.id}`))
                                                }>
                                                
                                                    <Trash2 className="size-4 text-red-600" />
                                                </AdminButton>
                                            )}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={products.current_page}
                    lastPage={products.last_page}
                    total={products.total}
                    perPage={products.per_page}
                    from={null}
                    to={null}
                    itemLabel="محصول"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
    }
