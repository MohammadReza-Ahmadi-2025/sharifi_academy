import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
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
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    visitable_type_label: string;
    visitable_title: string;
    visitable_id: number;
    ip_address: string | null;
    visitor_name: string | null;
    created_at: string | null;
};

type Props = {
    visits: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: { total: number; today: number; week: number };
    filters: { search: string; type: string; per_page: number };
    typeOptions: { value: string; label: string }[];
};

export default function VisitsIndex({ visits, stats, filters, typeOptions }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [type, setType] = useState(filters.type);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/visits',
            {
                search: search || undefined,
                type: type || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setType('');
        router.get('/admin/visits', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="بازدیدها" />
            <PageHeader title="بازدیدها" description="ثبت بازدید صفحات و محتوا (بدون ربات‌ها)" />
            <div className="mt-4 grid gap-4 sm:grid-cols-3">
                <Card className="p-4">
                    <p className="text-sm text-slate-500">کل بازدیدها</p>
                    <p className="text-2xl font-semibold">{stats.total.toLocaleString('fa-IR')}</p>
                </Card>
                <Card className="p-4">
                    <p className="text-sm text-slate-500">امروز</p>
                    <p className="text-2xl font-semibold">{stats.today.toLocaleString('fa-IR')}</p>
                </Card>
                <Card className="p-4">
                    <p className="text-sm text-slate-500">۷ روز اخیر</p>
                    <p className="text-2xl font-semibold">{stats.week.toLocaleString('fa-IR')}</p>
                </Card>
            </div>
            <AdminListFilters
                className="mt-4"
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="IP یا نام کاربر…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={type}
                    onChange={(e) => setType(e.target.value)}
                    placeholder="همه نوع‌ها"
                    options={typeOptions}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>محتوا</TableHead>
                            <TableHead>نوع</TableHead>
                            <TableHead>IP</TableHead>
                            <TableHead>کاربر</TableHead>
                            <TableHead>زمان</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {visits.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="بازدیدی ثبت نشده است." />
                        ) : (
                            visits.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.visitable_title}</div>
                                        <div className="text-xs text-slate-500">#{row.visitable_id}</div>
                                    </TableCell>
                                    <TableCell>{row.visitable_type_label}</TableCell>
                                    <TableCell className="font-mono text-sm" dir="ltr">
                                        {row.ip_address ?? '—'}
                                    </TableCell>
                                    <TableCell>{row.visitor_name ?? '—'}</TableCell>
                                    <TableCell className="text-sm text-slate-600">
                                        {row.created_at
                                            ? new Date(row.created_at).toLocaleString('fa-IR')
                                            : '—'}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={visits.current_page}
                    lastPage={visits.last_page}
                    total={visits.total}
                    perPage={visits.per_page}
                    from={null}
                    to={null}
                    itemLabel="بازدید"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
