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
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    type: string;
    type_label: string;
    value: string;
    label: string | null;
    is_active: boolean;
    sort: number;
};

type Props = {
    socials: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; active: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function SocialIndex({ socials, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [active, setActive] = useState(filters.active);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/socials',
            {
                search: search || undefined,
                active: active || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setActive('');
        router.get('/admin/socials', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="شبکه‌های اجتماعی" />
            <PageHeader
                title="شبکه‌های اجتماعی"
                description="لینک تلگرام، اینستاگرام و بقیه در فوتر سایت (زیر منوها) نمایش داده می‌شود. برای ستون‌های فوتر از بخش منوها با موقعیت «فوتر» استفاده کنید."
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/socials/create">
                            <Plus className="size-4" />
                            آیتم جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو نوع، مقدار یا برچسب…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={active}
                    onChange={(e) => setActive(e.target.value)}
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
                            <TableHead>نوع</TableHead>
                            <TableHead>مقدار</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {socials.data.length === 0 ? (
                            <TableEmptyState colSpan={4} message="آیتمی ثبت نشده است." />
                        ) : (
                            socials.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.type_label}</div>
                                        {row.label ? (
                                            <div className="text-xs text-slate-500">{row.label}</div>
                                        ) : null}
                                    </TableCell>
                                    <TableCell className="font-mono text-sm" dir="ltr">{row.value}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.is_active ? 'default' : 'secondary'}>
                                            {row.is_active ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/socials/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        window.confirm('حذف شود؟') &&
                                                        router.delete(`/admin/socials/${row.id}`)
                                                    }
                                                >
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
                    currentPage={socials.current_page}
                    lastPage={socials.last_page}
                    total={socials.total}
                    perPage={socials.per_page}
                    from={null}
                    to={null}
                    itemLabel="آیتم"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
