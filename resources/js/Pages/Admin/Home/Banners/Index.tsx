import { AdminButton } from '@/Components/Admin/shared/ui/button';
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
    title: string | null;
    location_label: string | null;
    image_url: string | null;
};

type Props = {
    banners: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; per_page: number };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function HomeBannersIndex({ banners, filters, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);

    const apply = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/home-banners',
            { search: search || undefined, per_page: filters.per_page, ...overrides },
            { preserveState: true, replace: true },
        );
    };

    const onSubmit = (e: FormEvent) => {
        e.preventDefault();
        apply({ page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="بنرهای خانه" />
            <PageHeader
                title="بنرهای خانه"
                description="تصاویر اسلایدر و سکشن بنر صفحه اصلی"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/home-banners/create">
                            <Plus className="size-4" />
                            بنر جدید
                        </AdminButton>
                    ) : null
                }
            />
            <form onSubmit={onSubmit} className="mt-6 flex gap-2">
                <AdminInput value={search} onChange={(e) => setSearch(e.target.value)} placeholder="جستجو…" className="max-w-xs" />
                <AdminButton type="submit">اعمال</AdminButton>
            </form>
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>تصویر</TableHead>
                            <TableHead>عنوان</TableHead>
                            <TableHead>محل</TableHead>
                            <TableHead className="w-24">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {banners.data.length === 0 ? (
                            <TableEmptyState colSpan={4} message="بنری ثبت نشده است." />
                        ) : (
                            banners.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        {row.image_url ? (
                                            <img src={row.image_url} alt="" className="size-14 rounded object-cover" />
                                        ) : (
                                            '—'
                                        )}
                                    </TableCell>
                                    <TableCell>{row.title ?? `بنر #${row.id}`}</TableCell>
                                    <TableCell>{row.location_label}</TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton variant="ghost" size="sm" href={`/admin/home-banners/${row.id}/edit`}>
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() => {
                                                        if (window.confirm('حذف بنر؟')) {
                                                            router.delete(`/admin/home-banners/${row.id}`);
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
                    currentPage={banners.current_page}
                    lastPage={banners.last_page}
                    total={banners.total}
                    perPage={banners.per_page}
                    from={null}
                    to={null}
                    itemLabel="بنر"
                    onPageChange={(page) => apply({ page })}
                    onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
