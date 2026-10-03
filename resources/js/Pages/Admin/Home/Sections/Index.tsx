import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
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
    title: string;
    status: boolean;
    sort: number;
    content_type: string | null;
    content_type_label: string | null;
    tabs_count: number;
};

type Props = {
    sections: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function HomeSectionsIndex({ sections, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [sortDraft, setSortDraft] = useState<Record<number, number>>(
        () => Object.fromEntries(sections.data.map((row) => [row.id, row.sort])),
    );

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/home-sections',
            { search: search || undefined, per_page: filters.per_page, ...overrides },
            { preserveState: true, replace: true },
        );
    };

    const saveOrder = () => {
        router.put(
            '/admin/home-sections/reorder',
            {
                sections: sections.data.map((row) => ({
                    id: row.id,
                    sort: sortDraft[row.id] ?? row.sort,
                })),
            },
            { preserveScroll: true },
        );
    };

    const onFilterSubmit = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    const clearFilters = () => {
        setSearch('');
        router.get('/admin/home-sections', { per_page: filters.per_page }, { replace: true });
    };

    return (
        <AdminLayout>
            <Head title="سکشن‌های صفحه اصلی" />
            <PageHeader
                title="سکشن‌های خانه"
                description="چیدمان و محتوای داینامیک صفحه اصلی"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/home-sections/create">
                            <Plus className="size-4" />
                            سکشن جدید
                        </AdminButton>
                    ) : null
                }
            />

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={onFilterSubmit}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان سکشن…"
            >
                <AdminFilterSelect
                    value={String(filters.per_page)}
                    onChange={(e) => applyFilters({ per_page: Number(e.target.value), page: 1 })}
                    options={[
                        { value: '25', label: '۲۵ در صفحه' },
                        { value: '50', label: '۵۰ در صفحه' },
                        { value: '100', label: '۱۰۰ در صفحه' },
                    ]}
                />
                {abilities.update && (
                    <AdminButton type="button" variant="outline" onClick={saveOrder}>
                        ذخیره ترتیب
                    </AdminButton>
                )}
            </AdminListFilters>

            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>نوع</TableHead>
                            <TableHead>ترتیب</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {sections.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="سکشنی ثبت نشده است." />
                        ) : (
                            sections.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.title}</TableCell>
                                    <TableCell>{row.content_type_label ?? '—'}</TableCell>
                                    <TableCell>
                                        {abilities.update ? (
                                            <AdminInput
                                                type="number"
                                                className="w-20"
                                                value={sortDraft[row.id] ?? row.sort}
                                                onChange={(e) =>
                                                    setSortDraft((prev) => ({
                                                        ...prev,
                                                        [row.id]: Number(e.target.value),
                                                    }))
                                                }
                                            />
                                        ) : (
                                            row.sort
                                        )}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={row.status ? 'success' : 'secondary'}>
                                            {row.status ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/home-sections/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => {
                                                        if (window.confirm('این سکشن حذف شود؟')) {
                                                            router.delete(`/admin/home-sections/${row.id}`);
                                                        }
                                                    }}
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
                    currentPage={sections.current_page}
                    lastPage={sections.last_page}
                    total={sections.total}
                    perPage={sections.per_page}
                    from={null}
                    to={null}
                    itemLabel="سکشن"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
