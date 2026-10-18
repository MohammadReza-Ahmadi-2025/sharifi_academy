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
    url: string | null;
    location: string;
    location_label: string;
    sort: number;
    status: boolean;
    items_count: number;
};

type Props = {
    menus: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; location: string; per_page: number };
    locationOptions: { value: string; label: string }[];
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function MenusIndex({ menus, filters, locationOptions, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [location, setLocation] = useState(filters.location);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/menus',
            {
                search: search || undefined,
                location: location || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setLocation('');
        router.get('/admin/menus', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="منوهای سایت" />
            <PageHeader
                title="منوهای سایت"
                description="منو با موقعیت «فوتر» ستون‌ها و لینک‌های متنی فوتر را می‌سازد. برای تلگرام و شبکه‌های اجتماعی از بخش شبکه‌های اجتماعی استفاده کنید."
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/menus/create">
                            <Plus className="size-4" />
                            منوی جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان یا لینک…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={location}
                    onChange={(e) => setLocation(e.target.value)}
                    placeholder="همه محل‌ها"
                    options={locationOptions}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>محل</TableHead>
                            <TableHead>آیتم‌ها</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {menus.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="منویی ثبت نشده است." />
                        ) : (
                            menus.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.title}</div>
                                        {row.url ? (
                                            <div className="text-xs text-slate-500" dir="ltr">{row.url}</div>
                                        ) : null}
                                    </TableCell>
                                    <TableCell>{row.location_label}</TableCell>
                                    <TableCell>{row.items_count}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status ? 'default' : 'secondary'}>
                                            {row.status ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/menus/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        confirmToast('حذف شود؟', () => router.delete(`/admin/menus/${row.id}`))
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
                    currentPage={menus.current_page}
                    lastPage={menus.last_page}
                    total={menus.total}
                    perPage={menus.per_page}
                    from={null}
                    to={null}
                    itemLabel="منو"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
    }
