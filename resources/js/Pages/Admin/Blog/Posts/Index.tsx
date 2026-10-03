import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { findSelectOption, mapToSelectOptions } from '@/Components/Admin/shared/lib/select-options';
import { Select } from '@/Components/Admin/shared/ui/select';
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
    slug: string;
    status: string;
    status_label: string;
    author_name: string | null;
    category_titles: string[];
};

type Props = {
    posts: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
        status: string | null;
        category_id: number | null;
        per_page: number;
    };
    categoryOptions: { value: number; label: string }[];
    statusOptions: { value: string; label: string }[];
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function BlogPostsIndex({
    posts,
    filters,
    categoryOptions,
    statusOptions,
    abilities,
}: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status ?? '');
    const [categoryId, setCategoryId] = useState(filters.category_id ? String(filters.category_id) : '');
    const categorySelectOptions = mapToSelectOptions(categoryOptions);
    const statusSelectOptions = mapToSelectOptions(statusOptions);

    const applyFilters = (overrides: Record<string, string | number | undefined | null> = {}) => {
        router.get(
            '/admin/blog/posts',
            {
                search: search || undefined,
                status: status || undefined,
                category_id: categoryId || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true },
        );
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        setCategoryId('');
        router.get('/admin/blog/posts', { per_page: filters.per_page }, { replace: true });
    };

    return (
        <AdminLayout>
            <Head title="پست‌های وبلاگ" />
            <PageHeader
                title="پست‌های وبلاگ"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/blog/posts/create">
                            <Plus className="size-4" />
                            پست جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان یا اسلاگ…"
            >
                <Select
                    className="lg:col-span-2 min-w-0"
                    isClearable
                    placeholder="وضعیت"
                    options={statusSelectOptions}
                    value={findSelectOption(statusSelectOptions, status)}
                    onChange={(option) => setStatus(option?.value ?? '')}
                />
                <Select
                    className="lg:col-span-2 min-w-0"
                    isClearable
                    placeholder="دسته وبلاگ"
                    options={categorySelectOptions}
                    value={findSelectOption(categorySelectOptions, categoryId)}
                    onChange={(option) => setCategoryId(option?.value ?? '')}
                />
            </AdminListFilters>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>دسته‌ها</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {posts.data.length === 0 ? (
                            <TableEmptyState colSpan={4} message="پستی ثبت نشده است." />
                        ) : (
                            posts.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.title}</div>
                                        <div className="text-xs text-slate-500 font-mono">{row.slug}</div>
                                    </TableCell>
                                    <TableCell className="text-sm">{row.category_titles.join('، ') || '—'}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status === 'published' ? 'default' : 'secondary'}>
                                            {row.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/blog/posts/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        window.confirm('حذف شود؟') &&
                                                        router.delete(`/admin/blog/posts/${row.id}`)
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
                    currentPage={posts.current_page}
                    lastPage={posts.last_page}
                    total={posts.total}
                    perPage={posts.per_page}
                    from={null}
                    to={null}
                    itemLabel="پست"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
