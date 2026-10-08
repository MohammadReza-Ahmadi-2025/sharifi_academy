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
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    title: string;
    user_name: string | null;
    type_label: string;
    status: string;
    status_label: string;
    visibility: string;
    published_at: string | null;
    expires_at: string | null;
    created_at: string | null;
};

type Props = {
    stories: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; status: string; per_page: number };
    statusOptions: { value: string; label: string }[];
    abilities: { create: boolean; update: boolean; moderate: boolean };
};

export default function StoriesIndex({ stories, filters, statusOptions, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/stories',
            {
                search: search || undefined,
                status: status || undefined,
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
        setStatus('');
        router.get('/admin/stories', { per_page: filters.per_page }, { replace: true });
    };

    const removeStory = (row: Row) => {
        if (!window.confirm('این استوری به‌عنوان حذف‌شده ثبت شود؟')) {
            return;
        }
        router.post(`/admin/stories/${row.id}/remove`, {}, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <Head title="استوری‌ها" />
            <PageHeader
                title="استوری‌ها"
                actions={
                    abilities.create
                        ? (
                              <AdminButton asChild>
                                  <Link href="/admin/stories/create">
                                      <Plus className="size-4" />
                                      استوری جدید
                                  </Link>
                              </AdminButton>
                          )
                        : undefined
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان یا ناشر…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    placeholder="همه وضعیت‌ها"
                    options={statusOptions}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>ناشر</TableHead>
                            <TableHead>نوع</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>شروع نمایش</TableHead>
                            <TableHead>پایان نمایش</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {stories.data.length === 0 ? (
                            <TableEmptyState colSpan={7} message="استوری ثبت نشده است." />
                        ) : (
                            stories.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.title}</TableCell>
                                    <TableCell>{row.user_name ?? '—'}</TableCell>
                                    <TableCell>{row.type_label}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status === 'published' ? 'success' : 'secondary'}>
                                            {row.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-xs text-slate-500">{row.published_at ?? '—'}</TableCell>
                                    <TableCell className="text-xs text-slate-500">{row.expires_at ?? '—'}</TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" asChild>
                                                    <Link href={`/admin/stories/${row.id}/edit`}>
                                                        <Pencil className="size-4" />
                                                        ویرایش
                                                    </Link>
                                                </AdminButton>
                                            )}
                                            {abilities.moderate && row.status !== 'removed' ? (
                                                <AdminButton variant="ghost" size="sm" onClick={() => removeStory(row)}>
                                                    حذف
                                                </AdminButton>
                                            ) : null}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={stories.current_page}
                    lastPage={stories.last_page}
                    perPage={stories.per_page}
                    total={stories.total}
                    from={null}
                    to={null}
                    itemLabel="استوری"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
