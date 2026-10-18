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
    status: boolean;
    sort: number;
    website: string | null;
};

type Props = {
    brands: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; status: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function BrandsIndex({ brands, filters, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/catalog/brands',
            {
                search: search || undefined,
                status: status || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        router.get('/admin/catalog/brands', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="برندها" />
            <PageHeader
                title="برندها"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/catalog/brands/create">
                            <Plus className="size-4" />
                            برند جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان یا نامک…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    placeholder="همه وضعیت‌ها"
                    options={[
                        { value: '1', label: 'فعال' },
                        { value: '0', label: 'غیرفعال' },
                    ]}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>نامک</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {brands.data.length === 0 ? (
                            <TableEmptyState colSpan={4} message="برندی ثبت نشده است." />
                        ) : (
                            brands.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.title}</TableCell>
                                    <TableCell className="font-mono text-sm" dir="ltr">{row.slug}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status ? 'default' : 'secondary'}>
                                            {row.status ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/admin/catalog/brands/${row.id}/edit`}
                                                >
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        confirmToast('برند حذف شود؟', () => router.delete(`/admin/catalog/brands/${row.id}`))
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
                    currentPage={brands.current_page}
                    lastPage={brands.last_page}
                    total={brands.total}
                    perPage={brands.per_page}
                    from={null}
                    to={null}
                    itemLabel="برند"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
    }
