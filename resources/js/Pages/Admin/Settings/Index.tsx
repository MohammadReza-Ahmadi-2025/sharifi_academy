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
import { Pencil } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    key: string;
    label: string;
    group: string;
    group_label: string;
    type: string;
    value_preview: string;
    autoload: boolean;
};

type Props = {
    settings: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    groupOptions: { value: string; label: string }[];
    filters: { search: string; group: string; per_page: number };
    abilities: { update: boolean };
};

export default function SettingsIndex({ settings, groupOptions, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [group, setGroup] = useState(filters.group);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/settings',
            {
                search: search || undefined,
                group: group || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    const clearFilters = () => {
        setSearch('');
        setGroup('');
        router.get('/admin/settings', { per_page: filters.per_page }, { replace: true });
    };

    return (
        <AdminLayout>
            <Head title="تنظیمات سایت" />
            <PageHeader
                title="تنظیمات سایت"
                description="عمومی، تماس، سئو و کلیدهای یکپارچه‌سازی"
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان یا کلید…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={group}
                    onChange={(e) => setGroup(e.target.value)}
                    placeholder="همه گروه‌ها"
                    options={groupOptions}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>تنظیم</TableHead>
                            <TableHead>گروه</TableHead>
                            <TableHead>مقدار</TableHead>
                            <TableHead>بارگذاری خودکار</TableHead>
                            <TableHead className="w-24" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {settings.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="تنظیمی پیدا نشد." />
                        ) : (
                            settings.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.label}</div>
                                        <div className="font-mono text-xs text-slate-500" dir="ltr">{row.key}</div>
                                    </TableCell>
                                    <TableCell className="text-sm">{row.group_label}</TableCell>
                                    <TableCell className="max-w-xs truncate text-sm text-slate-600 dark:text-slate-300">
                                        {row.value_preview}
                                    </TableCell>
                                    <TableCell>
                                        {row.autoload ? (
                                            <Badge variant="secondary">بله</Badge>
                                        ) : (
                                            <span className="text-slate-400">—</span>
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        {abilities.update ? (
                                            <AdminButton
                                                variant="ghost"
                                                size="sm"
                                                href={`/admin/settings/${row.id}/edit`}
                                            >
                                                <Pencil className="size-4" />
                                            </AdminButton>
                                        ) : null}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={settings.current_page}
                    lastPage={settings.last_page}
                    perPage={settings.per_page}
                    total={settings.total}
                    from={null}
                    to={null}
                    itemLabel="تنظیم"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
