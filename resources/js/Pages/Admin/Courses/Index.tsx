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
import { BookOpen, Eye, Pencil, Plus, Trash2, Check, X } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useState } from 'react';

type CourseRow = {
    id: number;
    title: string;
    slug: string;
    status?: string;
    status_label: string;
    level_label: string | null;
    is_featured: boolean;
    classes_count: number;
    creator_name?: string | null;
};

type PaginatedCourses = {
    data: CourseRow[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type Props = {
    courses: PaginatedCourses;
    filters: { search: string; status: string; per_page: number };
    statusOptions: { value: string; label: string }[];
    abilities: { create: boolean; update: boolean; delete: boolean; moderate: boolean };
    pendingReviewCount: number;
};

function statusBadgeVariant(status?: string): 'info' | 'warning' | 'secondary' {
    if (status === 'pending_review') {
        return 'warning';
    }
    if (status === 'published') {
        return 'info';
    }

    return 'secondary';
}

const emptyPaginated: PaginatedCourses = {
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    from: null,
    to: null,
};

export default function CoursesIndex({ courses, filters, statusOptions, abilities, pendingReviewCount }: Props) {
    const confirmToast = useConfirmToast();
    const page = courses ?? emptyPaginated;
    const rows = page.data ?? [];
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/courses',
            {
                search: search || undefined,
                status: status || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const submitSearch = (event: FormEvent) => {
        event.preventDefault();
        applyFilters({ page: 1 });
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        router.get('/admin/courses', { per_page: filters.per_page }, { replace: true });
    };

    const handleDelete = (course: CourseRow) => {
        confirmToast(`دوره «${course.title}» حذف شود؟`, () => {
        router.delete(`/admin/courses/${course.id}`, { preserveScroll: true });
    });
    };

    return (
        <AdminLayout>
            <Head title="دوره‌ها" />

            <PageHeader
                title="دوره‌ها"
                description="مدیریت دوره‌ها، کلاس‌ها و جلسات آموزشی"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/courses/create">
                            <Plus className="ml-1 h-4 w-4" />
                            دوره جدید
                        </AdminButton>
                    ) : null
                }
            />

            {pendingReviewCount > 0 ? (
                <div className="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                    <p>
                        <span className="font-semibold">{pendingReviewCount.toLocaleString('fa-IR')}</span>
                        {' '}
                        دوره در انتظار تایید انتشار است.
                    </p>
                    <div className="flex flex-wrap gap-2">
                        <AdminButton
                            type="button"
                            size="sm"
                            variant={filters.status === 'pending_review' ? 'default' : 'outline'}
                            onClick={() => {
                                setStatus('pending_review');
                                applyFilters({ status: 'pending_review', page: 1 });
                            }}
                        >
                            فقط در انتظار تایید
                        </AdminButton>
                        <AdminButton href="/admin/instructor-moderation" size="sm" variant="outline">
                            صف تایید اساتید
                        </AdminButton>
                    </div>
                </div>
            ) : null}

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={submitSearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو در عنوان، اسلاگ یا کد…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    placeholder="همه وضعیت‌ها"
                    options={statusOptions}
                />
            </AdminListFilters>

            <Card className="mt-4 overflow-hidden p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>دوره</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>سطح</TableHead>
                            <TableHead>کلاس‌ها</TableHead>
                            <TableHead className="w-32 text-left">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {rows.length === 0 ? (
                            <TableEmptyState
                                colSpan={5}
                                icon={<BookOpen className="h-8 w-8" />}
                                title="هنوز دوره‌ای ثبت نشده"
                                description={
                                    abilities.create
                                        ? 'با دکمه «دوره جدید» اولین دوره را بسازید یا از seeder دمو در محیط توسعه استفاده کنید.'
                                        : 'دوره‌ای برای نمایش وجود ندارد.'
                                }
                                action={
                                    abilities.create ? (
                                        <AdminButton href="/admin/courses/create" className="mt-2">
                                            <Plus className="ml-1 h-4 w-4" />
                                            ایجاد دوره
                                        </AdminButton>
                                    ) : undefined
                                }
                            />
                        ) : (
                            rows.map((course) => (
                                <TableRow key={course.id}>
                                    <TableCell>
                                        <div className="font-medium text-slate-900 dark:text-slate-100">
                                            {course.title}
                                        </div>
                                        <div className="text-xs text-slate-500" dir="ltr">{course.slug}</div>
                                        {course.creator_name ? (
                                            <div className="mt-0.5 text-xs text-slate-500">استاد: {course.creator_name}</div>
                                        ) : null}
                                        {course.is_featured ? (
                                            <Badge variant="secondary" className="mt-1">ویژه</Badge>
                                        ) : null}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={statusBadgeVariant(course.status)}>{course.status_label}</Badge>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-600 dark:text-slate-300">
                                        {course.level_label ?? '—'}
                                    </TableCell>
                                    <TableCell>{course.classes_count.toLocaleString('fa-IR')}</TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {abilities.moderate && course.status === 'pending_review' ? (
                                                <>
                                                    <button
                                                        type="button"
                                                        title="تایید انتشار"
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-md text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-500/10"
                                                        onClick={() =>
                                                            router.post(
                                                                `/admin/instructor-moderation/courses/${course.id}/approve`,
                                                                {},
                                                                { preserveScroll: true },
                                                            )
                                                        }
                                                    >
                                                        <Check className="h-4 w-4" />
                                                    </button>
                                                    <button
                                                        type="button"
                                                        title="رد"
                                                        className="inline-flex h-8 w-8 items-center justify-center rounded-md text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                                                        onClick={() =>
                                                            router.post(
                                                                `/admin/instructor-moderation/courses/${course.id}/reject`,
                                                                {},
                                                                { preserveScroll: true },
                                                            )
                                                        }
                                                    >
                                                        <X className="h-4 w-4" />
                                                    </button>
                                                </>
                                            ) : null}
                                            <Link
                                                href={`/admin/courses/${course.id}`}
                                                className="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                                title="مشاهده"
                                            >
                                                <Eye className="h-4 w-4" />
                                            </Link>
                                            {abilities.update ? (
                                                <Link
                                                    href={`/admin/courses/${course.id}/edit`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                                    title="ویرایش"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            ) : null}
                                            {abilities.delete ? (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(course)}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-md text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-500/10"
                                                    title="حذف"
                                                >
                                                    <Trash2 className="h-4 w-4" />
                                                </button>
                                            ) : null}
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </Card>

            <SimpleTablePagination
                className="mt-4"
                currentPage={page.current_page}
                lastPage={page.last_page}
                total={page.total}
                perPage={page.per_page}
                from={page.from}
                to={page.to}
                itemLabel="دوره"
                onPageChange={(nextPage) => applyFilters({ page: nextPage })}
                onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
            />
        </AdminLayout>
    );
}
