import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { formatNumberInput } from '@/Components/Admin/shared/lib/format-number';
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
import { Select } from '@/Components/Admin/shared/ui/select';
import { mapToSelectOptions, findSelectOption } from '@/Components/Admin/shared/lib/select-options';
import { FormEvent, useState } from 'react';

type EnrollmentRow = {
    id: number;
    user_label: string;
    user_mobile: string | null;
    course_title: string | null;
    course_id: number | null;
    class_name: string | null;
    class_id: number;
    status: string;
    status_label: string;
    price_amount: number;
};

type Props = {
    enrollments: {
        data: EnrollmentRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; status: string; per_page: number };
    statusOptions: { value: string; label: string }[];
    abilities: { create: boolean };
};

const ALL_STATUS = { value: 'all', label: 'همه وضعیت‌ها' };

export default function CourseEnrollmentsIndex({ enrollments, filters, statusOptions, abilities }: Props) {
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);
    const statusSelectOptions = mapToSelectOptions([ALL_STATUS, ...statusOptions]);

    const apply = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/course-enrollments',
            {
                search: search || undefined,
                status: status || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    return (
        <AdminLayout>
            <Head title="ثبت‌نام در دوره" />

            <PageHeader
                title="ثبت‌نام در دوره"
                description="ثبت‌نام دستی کاربران در کلاس‌های دوره — فقط کاربران با نام، نام خانوادگی و شهر"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/course-enrollments/create">
                            ثبت‌نام جدید
                        </AdminButton>
                    ) : undefined
                }
            />

            <Card className="p-4">
                <AdminListFilters
                    search={search}
                    onSearchChange={setSearch}
                    onSubmit={(e: FormEvent) => {
                        e.preventDefault();
                        apply({ page: 1 });
                    }}
                    onClear={() => {
                        setSearch('');
                        setStatus('pending');
                        router.get('/admin/course-enrollments', { status: 'pending', per_page: filters.per_page });
                    }}
                    searchPlaceholder="جستجو کاربر، دوره یا کلاس…"
                >
                    <div className="lg:col-span-3">
                        <Select
                            placeholder="وضعیت"
                            options={statusSelectOptions}
                            value={findSelectOption(statusSelectOptions, status)}
                            onChange={(option) => setStatus(String(option?.value ?? 'pending'))}
                        />
                    </div>
                </AdminListFilters>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>کاربر</TableHead>
                            <TableHead>دوره / کلاس</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>مبلغ</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {enrollments.data.length === 0 ? (
                            <TableEmptyState colSpan={4} title="ثبت‌نامی نیست" />
                        ) : (
                            enrollments.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium text-slate-900 dark:text-slate-100">{row.user_label}</div>
                                        {row.user_mobile ? (
                                            <div className="text-xs text-slate-500" dir="ltr">{row.user_mobile}</div>
                                        ) : null}
                                    </TableCell>
                                    <TableCell>
                                        <div>{row.course_title}</div>
                                        <div className="text-xs text-slate-500">{row.class_name}</div>
                                        {row.course_id ? (
                                            <Link
                                                href={`/admin/courses/${row.course_id}/classes/${row.class_id}/edit`}
                                                className="text-xs text-indigo-600 hover:underline"
                                            >
                                                ویرایش کلاس
                                            </Link>
                                        ) : null}
                                    </TableCell>
                                    <TableCell>
                                        <Badge
                                            variant={
                                                row.status === 'confirmed'
                                                    ? 'success'
                                                    : row.status === 'pending'
                                                      ? 'secondary'
                                                      : 'destructive'
                                            }
                                        >
                                            {row.status_label}
                                        </Badge>
                                    </TableCell>
                                    <TableCell dir="ltr" className="font-mono text-sm">
                                        {formatNumberInput(row.price_amount)}
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>

                <SimpleTablePagination
                    currentPage={enrollments.current_page}
                    lastPage={enrollments.last_page}
                    perPage={enrollments.per_page}
                    total={enrollments.total}
                    onPageChange={(page) => apply({ page })}
                />
            </Card>
        </AdminLayout>
    );
}
