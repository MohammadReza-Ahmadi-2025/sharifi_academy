import { formatAdminDateDisplay } from '@/Components/Admin/shared/lib/format-persian-datetime';
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
    is_active: boolean;
    updated_at: string | null;
};

type Props = {
    pages: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function PagesIndex({ pages, filters, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/pages',
            { search: search || undefined, per_page: filters.per_page, ...overrides },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AdminLayout>
            <Head title="صفحات ثابت" />
            <PageHeader
                title="صفحات ثابت"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/pages/create">
                            <Plus className="size-4" />
                            صفحه جدید
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={(e: FormEvent) => {
                    e.preventDefault();
                    applyFilters({ page: 1 });
                }}
                onClear={() => {
                    setSearch('');
                    router.get('/admin/pages');
                }}
                searchPlaceholder="جستجو عنوان یا اسلاگ…"
            />
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>اسلاگ</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>آخرین تغییر</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {pages.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="صفحه‌ای ثبت نشده است." />
                        ) : (
                            pages.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.title}</TableCell>
                                    <TableCell className="font-mono text-xs text-slate-500" dir="ltr">
                                        {row.slug}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={row.is_active ? 'success' : 'secondary'}>
                                            {row.is_active ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell className="text-xs text-slate-500">
                                        {formatAdminDateDisplay(row.updated_at)}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {abilities.update && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/admin/pages/${row.slug}/edit`}
                                                    className="gap-1.5"
                                                >
                                                    <Pencil className="size-4" />
                                                    ویرایش
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        confirmToast('حذف این صفحه؟', () =>
                                                            router.delete(`/admin/pages/${row.slug}`),
                                                        )
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
                    currentPage={pages.current_page}
                    lastPage={pages.last_page}
                    total={pages.total}
                    perPage={pages.per_page}
                    from={null}
                    to={null}
                    itemLabel="صفحه"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
