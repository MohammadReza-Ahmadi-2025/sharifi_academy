import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { Table, TableBody, TableCell, TableEmptyState, TableHead, TableHeader, TableRow } from '@/Components/Admin/shared/ui/table';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Eye } from 'lucide-react';
import { FormEvent, useState } from 'react';

type SessionRow = {
    id: number;
    title: string;
    session_number: number;
    starts_at: string | null;
    course_title: string | null;
    class_title: string | null;
    attendances_count: number;
    present_count: number;
    absent_count: number;
    enrolled_count: number;
    attendance_rate: number | null;
};

type Summary = {
    sessions_total: number;
    sessions_with_records: number;
    present_count: number;
    absent_count: number;
    excused_count: number;
    late_count: number;
    attendance_rate: number | null;
    avg_duration_minutes: number;
    low_attendance: { id: number; title: string; course_title: string | null; rate: number; present: number; enrolled: number }[];
};

type Props = {
    summary: Summary;
    sessions: {
        data: SessionRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; per_page: number };
};

export default function SessionAttendancesIndex({ summary, sessions, filters }: Props) {
    const [search, setSearch] = useState(filters.search);
    const apply = (overrides: Record<string, string | number | undefined> = {}) =>
        router.get(
            '/admin/session-attendances',
            { search: search || undefined, per_page: filters.per_page, ...overrides },
            { preserveState: true, replace: true },
        );

    const cards = [
        { label: 'نرخ حضور', value: summary.attendance_rate === null ? '—' : `${summary.attendance_rate}%` },
        { label: 'حاضر / غایب', value: `${summary.present_count} / ${summary.absent_count}` },
        { label: 'جلسات دارای رکورد', value: `${summary.sessions_with_records} از ${summary.sessions_total}` },
        { label: 'میانگین مدت حضور', value: summary.avg_duration_minutes > 0 ? `${summary.avg_duration_minutes} دقیقه` : '—' },
    ];

    return (
        <AdminLayout>
            <Head title="حضور و غیاب" />
            <PageHeader title="داشبورد حضور و غیاب" description="خلاصه وضعیت حضور جلسات و همگام‌سازی Adobe" />

            <div className="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {cards.map((card) => (
                    <Card key={card.label} className="p-4">
                        <p className="text-xs font-medium text-slate-500">{card.label}</p>
                        <p className="mt-1 text-2xl font-bold tabular-nums text-slate-900">{card.value}</p>
                    </Card>
                ))}
            </div>

            {summary.low_attendance.length > 0 ? (
                <Card className="mt-4 p-4">
                    <h3 className="text-sm font-semibold text-slate-900">جلسات با حضور پایین (کمتر از ۷۰٪)</h3>
                    <ul className="mt-3 divide-y divide-slate-100">
                        {summary.low_attendance.map((row) => (
                            <li key={row.id} className="flex flex-wrap items-center justify-between gap-2 py-2 text-sm">
                                <div>
                                    <p className="font-medium text-slate-800">{row.title}</p>
                                    <p className="text-xs text-slate-500">{row.course_title}</p>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="tabular-nums text-rose-700">{row.rate}% ({row.present}/{row.enrolled})</span>
                                    <Link href={`/admin/session-attendances/${row.id}`} className="text-indigo-600 hover:underline">
                                        جزئیات
                                    </Link>
                                </div>
                            </li>
                        ))}
                    </ul>
                </Card>
            ) : null}

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={(e: FormEvent) => {
                    e.preventDefault();
                    apply({ page: 1 });
                }}
                onClear={() => {
                    setSearch('');
                    router.get('/admin/session-attendances');
                }}
                searchPlaceholder="جستجو دوره یا جلسه…"
            />
            <Card className="mt-4 overflow-hidden">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>جلسه</TableHead>
                            <TableHead>دوره / کلاس</TableHead>
                            <TableHead>حضور</TableHead>
                            <TableHead>نرخ</TableHead>
                            <TableHead className="w-16" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {sessions.data.length === 0 ? (
                            <TableEmptyState colSpan={5} message="جلسه‌ای نیست." />
                        ) : (
                            sessions.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="font-medium">{row.title}</div>
                                        <div className="text-xs text-slate-500">جلسه {row.session_number}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div>{row.course_title}</div>
                                        <div className="text-xs text-slate-500">{row.class_title}</div>
                                    </TableCell>
                                    <TableCell className="tabular-nums text-sm">
                                        {row.present_count} حاضر · {row.absent_count} غایب
                                        <div className="text-xs text-slate-500">{row.enrolled_count} ثبت‌نام قطعی</div>
                                    </TableCell>
                                    <TableCell className="tabular-nums">
                                        {row.attendance_rate === null ? '—' : `${row.attendance_rate}%`}
                                    </TableCell>
                                    <TableCell>
                                        <AdminButton variant="ghost" size="sm" href={`/admin/session-attendances/${row.id}`}>
                                            <Eye className="size-4" />
                                        </AdminButton>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={sessions.current_page}
                    lastPage={sessions.last_page}
                    total={sessions.total}
                    perPage={sessions.per_page}
                    from={null}
                    to={null}
                    itemLabel="جلسه"
                    onPageChange={(page) => apply({ page })}
                    onPerPageChange={(perPage) => apply({ per_page: perPage, page: 1 })}
                />
            </Card>
        </AdminLayout>
    );
}
