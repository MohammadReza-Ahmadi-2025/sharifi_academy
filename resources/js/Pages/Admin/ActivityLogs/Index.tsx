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
import { Eye } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    description: string;
    action: string;
    action_label: string;
    module_label: string;
    user: { id: number; name: string; mobile: string } | null;
    created_at: string | null;
};

type Props = {
    logs: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
        module: string;
        action: string;
        user_id: string;
        per_page: number;
    };
    moduleOptions: { value: string; label: string }[];
    actionOptions: { value: string; label: string }[];
    userOptions: { value: string; label: string }[];
    abilities: { show: boolean };
};

function formatDate(iso: string | null): string {
    if (!iso) {
        return '—';
    }

    try {
        return new Intl.DateTimeFormat('fa-IR', {
            dateStyle: 'short',
            timeStyle: 'short',
        }).format(new Date(iso));
    } catch {
        return iso;
    }
}

export default function ActivityLogsIndex({
    logs,
    filters,
    moduleOptions,
    actionOptions,
    userOptions,
    abilities,
}: Props) {
    const [search, setSearch] = useState(filters.search);
    const [module, setModule] = useState(filters.module);
    const [action, setAction] = useState(filters.action);
    const [userId, setUserId] = useState(filters.user_id);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/activity-logs',
            {
                search: search || undefined,
                module: module || undefined,
                action: action || undefined,
                user_id: userId || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setModule('');
        setAction('');
        setUserId('');
        router.get('/admin/activity-logs', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="گزارش فعالیت" />
            <PageHeader title="گزارش فعالیت کاربران" />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو در توضیح یا نوع موجودیت…"
            >
                <AdminFilterSelect
                    value={module}
                    onChange={(e) => setModule(e.target.value)}
                    placeholder="همه ماژول‌ها"
                    options={moduleOptions}
                />
                <AdminFilterSelect
                    value={action}
                    onChange={(e) => setAction(e.target.value)}
                    placeholder="همه عملیات"
                    options={actionOptions}
                />
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={userId}
                    onChange={(e) => setUserId(e.target.value)}
                    placeholder="همه کاربران (دارای نقش)"
                    options={userOptions}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>زمان</TableHead>
                            <TableHead>کاربر</TableHead>
                            <TableHead>عملیات</TableHead>
                            <TableHead>توضیح</TableHead>
                            <TableHead className="w-16" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {logs.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="گزارشی ثبت نشده است." />
                        ) : (
                            logs.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="whitespace-nowrap text-sm text-slate-600">
                                        {formatDate(row.created_at)}
                                    </TableCell>
                                    <TableCell>
                                        {row.user ? (
                                            <div>
                                                <div className="font-medium">{row.user.name}</div>
                                                <div className="text-xs text-slate-500" dir="ltr">{row.user.mobile}</div>
                                            </div>
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">{row.action_label}</Badge>
                                        <div className="mt-1 text-xs text-slate-500">{row.module_label}</div>
                                    </TableCell>
                                    <TableCell className="max-w-md truncate">{row.description}</TableCell>
                                    <TableCell>
                                        {abilities.show && (
                                            <AdminButton
                                                variant="ghost"
                                                size="sm"
                                                href={`/admin/activity-logs/${row.id}`}
                                            >
                                                <Eye className="size-4" />
                                            </AdminButton>
                                        )}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={logs.current_page}
                    lastPage={logs.last_page}
                    total={logs.total}
                    perPage={logs.per_page}
                    from={null}
                    to={null}
                    itemLabel="رکورد"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
