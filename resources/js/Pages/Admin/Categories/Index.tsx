import {
    CategoryTypeFilter,
    type CategoryTypeOption,
} from '@/Components/Admin/Catalog/CategoryTypeFilter';
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
    parent_title: string | null;
    sort: number;
    status: number;
    type: string;
    type_label: string;
};

type Props = {
    categories: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    categoryType: string;
    categoryTypeLabel: string;
    typeOptions: CategoryTypeOption[];
    filters: { search: string; per_page: number; type: string };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

export default function CategoriesIndex({
    categories,
    categoryType,
    categoryTypeLabel,
    typeOptions,
    filters,
    abilities,
}: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/categories',
            {
                type: categoryType,
                search: search || undefined,
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
        router.get(
            '/admin/categories',
            { type: categoryType, per_page: filters.per_page },
            { replace: true },
        );
    };

    return (
        <AdminLayout>
            <Head title="دسته‌بندی‌ها" />
            <PageHeader
                title="دسته‌بندی‌ها"
                description={`فیلتر فعال: ${categoryTypeLabel}`}
                actions={
                    abilities.create ? (
                        <AdminButton href={`/admin/categories/create?type=${categoryType}`}>
                            <Plus className="size-4" />
                            دسته جدید
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
                leading={
                    <CategoryTypeFilter
                        options={typeOptions}
                        activeType={categoryType}
                        basePath="/admin/categories"
                        query={{ search: search || undefined, per_page: filters.per_page }}
                    />
                }
            />
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>نوع</TableHead>
                            <TableHead>والد</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead className="w-28" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {categories.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="دسته‌ای ثبت نشده است." />
                        ) : (
                            categories.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.title}</div>
                                        <div className="font-mono text-xs text-slate-500">{row.slug}</div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">{row.type_label}</Badge>
                                    </TableCell>
                                    <TableCell>{row.parent_title ?? '—'}</TableCell>
                                    <TableCell>
                                        <Badge variant={row.status ? 'default' : 'secondary'}>
                                            {row.status ? 'فعال' : 'غیرفعال'}
                                        </Badge>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {abilities.update && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/admin/categories/${row.id}/edit?type=${row.type}`}
                                                >
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            )}
                                            {abilities.delete && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    onClick={() =>
                                                        confirmToast('اطمینان دارید که حذف شود؟', () =>
                                                            router.delete(`/admin/categories/${row.id}`, {
                                                                data: { type: categoryType },
                                                            }),
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
                    currentPage={categories.current_page}
                    lastPage={categories.last_page}
                    total={categories.total}
                    perPage={categories.per_page}
                    from={null}
                    to={null}
                    itemLabel="دسته"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
    }
