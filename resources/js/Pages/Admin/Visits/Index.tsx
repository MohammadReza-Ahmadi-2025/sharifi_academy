import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
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
import { cn } from '@/lib/utils';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';

type RecentRow = {
    id: number;
    visitable_type_label: string;
    visitable_title: string;
    visitable_id: number;
    ip_address: string | null;
    visitor_label: string;
    created_at: string | null;
};

type TopRow = {
    visitable_type_label: string;
    visitable_id: number;
    visits_count: number;
    title: string;
    admin_url: string | null;
};

type Props = {
    filters: {
        period: string;
        from: string | null;
        to: string | null;
        type: string | null;
        per_page: number;
    };
    summary: {
        total: number;
        unique_visitors: number;
        logged_in: number;
        guests: number;
    };
    byType: { label: string; count: number }[];
    topVisited: TopRow[];
    dailyTrend: { day: string; total: number }[];
    recentVisits: {
        data: RecentRow[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    typeOptions: { value: string; label: string }[];
};

const PERIOD_OPTIONS = [
    { value: 'month', label: '۳۰ روز' },
    { value: 'week', label: '۷ روز' },
    { value: 'today', label: 'امروز' },
    { value: '', label: 'همه' },
] as const;

export default function VisitsIndex({
    filters,
    summary,
    byType,
    topVisited,
    dailyTrend,
    recentVisits,
    typeOptions,
}: Props) {
    const [type, setType] = useState(filters.type ?? '');
    const [from, setFrom] = useState(filters.from ?? '');
    const [to, setTo] = useState(filters.to ?? '');

    const maxTrend = useMemo(
        () => Math.max(1, ...dailyTrend.map((d) => d.total)),
        [dailyTrend],
    );

    const visitQuery = (overrides: Record<string, string | number | null | undefined> = {}) => {
        const period = overrides.period !== undefined ? overrides.period : filters.period;
        const nextFrom = overrides.from !== undefined ? overrides.from : from || undefined;
        const nextTo = overrides.to !== undefined ? overrides.to : to || undefined;
        const nextType = overrides.type !== undefined ? overrides.type : type || undefined;

        return {
            period: period === '' ? undefined : period,
            from: period && period !== 'custom' ? undefined : nextFrom || undefined,
            to: period && period !== 'custom' ? undefined : nextTo || undefined,
            type: nextType || undefined,
            per_page: overrides.per_page ?? filters.per_page,
            page: overrides.page,
        };
    };

    const applyVisitFilters = (overrides: Record<string, string | number | null | undefined> = {}) => {
        router.get('/admin/visits', visitQuery(overrides), { preserveState: true, replace: true });
    };

    const selectPeriod = (period: string) => {
        setFrom('');
        setTo('');
        applyVisitFilters({ period, from: null, to: null, page: 1 });
    };

    const submitCustomRange = (e: FormEvent) => {
        e.preventDefault();
        applyVisitFilters({ period: 'custom', page: 1 });
    };

    return (
        <AdminLayout>
            <Head title="آمار بازدید صفحات" />
            <PageHeader
                title="آمار بازدید"
                description="بازدیدهای ثبت‌شده از محصول، مقاله، دسته‌بندی، برند و دوره (بدون ربات؛ cooldown ۳۰ دقیقه)"
            />

            <Card className="mt-4 overflow-hidden">
                <div className="space-y-4 border-b border-slate-200 p-5 dark:border-slate-700">
                    <div className="flex flex-wrap gap-2">
                        {PERIOD_OPTIONS.map((opt) => (
                            <AdminButton
                                key={opt.label}
                                type="button"
                                size="sm"
                                variant={filters.period === opt.value ? 'default' : 'outline'}
                                className={cn(
                                    'rounded-full',
                                    filters.period === opt.value && 'ring-1 ring-slate-900 dark:ring-white',
                                )}
                                onClick={() => selectPeriod(opt.value)}
                            >
                                {opt.label}
                            </AdminButton>
                        ))}
                    </div>

                    <form onSubmit={submitCustomRange} className="grid gap-4 md:grid-cols-4">
                        <div>
                            <label className="mb-1 block text-sm text-slate-600 dark:text-slate-400">نوع صفحه</label>
                            <select
                                value={type}
                                onChange={(e) => setType(e.target.value)}
                                className="w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm dark:border-slate-600 dark:bg-slate-950"
                            >
                                {typeOptions.map((opt) => (
                                    <option key={opt.value || 'all'} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="mb-1 block text-sm text-slate-600 dark:text-slate-400">از تاریخ</label>
                            <AdminInput type="date" value={from} onChange={(e) => setFrom(e.target.value)} />
                        </div>
                        <div>
                            <label className="mb-1 block text-sm text-slate-600 dark:text-slate-400">تا تاریخ</label>
                            <AdminInput type="date" value={to} onChange={(e) => setTo(e.target.value)} />
                        </div>
                        <div className="flex items-end">
                            <AdminButton type="submit" className="w-full">اعمال فیلتر</AdminButton>
                        </div>
                    </form>
                </div>

                <div className="grid grid-cols-2 gap-4 border-b border-slate-200 p-5 lg:grid-cols-4 dark:border-slate-700">
                    <div className="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p className="text-xs text-slate-500">کل بازدیدها</p>
                        <p className="text-2xl font-bold">{summary.total.toLocaleString('fa-IR')}</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p className="text-xs text-slate-500">بازدیدکننده یکتا (تقریبی)</p>
                        <p className="text-2xl font-bold">{summary.unique_visitors.toLocaleString('fa-IR')}</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p className="text-xs text-slate-500">کاربران لاگین</p>
                        <p className="text-2xl font-bold">{summary.logged_in.toLocaleString('fa-IR')}</p>
                    </div>
                    <div className="rounded-xl bg-slate-50 p-4 dark:bg-slate-900/50">
                        <p className="text-xs text-slate-500">مهمان (IP)</p>
                        <p className="text-2xl font-bold">{summary.guests.toLocaleString('fa-IR')}</p>
                    </div>
                </div>

                {byType.length > 0 && (
                    <div className="flex flex-wrap gap-2 px-5 pt-5">
                        {byType.map((row) => (
                            <span
                                key={row.label}
                                className="rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-300"
                            >
                                {row.label}: {row.count.toLocaleString('fa-IR')}
                            </span>
                        ))}
                    </div>
                )}

                <div className="grid grid-cols-1 gap-6 p-5 xl:grid-cols-2">
                    <div>
                        <h3 className="mb-3 font-semibold text-slate-800 dark:text-slate-200">پربازدیدترین صفحات</h3>
                        <div className="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead className="w-10">#</TableHead>
                                        <TableHead>نوع</TableHead>
                                        <TableHead>عنوان</TableHead>
                                        <TableHead>بازدید</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {topVisited.length === 0 ? (
                                        <TableEmptyState colSpan={4} message="داده‌ای نیست" />
                                    ) : (
                                        topVisited.map((row, index) => (
                                            <TableRow key={`${row.visitable_id}-${index}`}>
                                                <TableCell>{index + 1}</TableCell>
                                                <TableCell>{row.visitable_type_label}</TableCell>
                                                <TableCell>
                                                    {row.admin_url ? (
                                                        <Link
                                                            href={row.admin_url}
                                                            className="text-blue-600 hover:underline dark:text-blue-400"
                                                        >
                                                            {row.title}
                                                        </Link>
                                                    ) : (
                                                        row.title
                                                    )}
                                                </TableCell>
                                                <TableCell className="font-semibold">
                                                    {row.visits_count.toLocaleString('fa-IR')}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>
                    </div>

                    <div>
                        <h3 className="mb-3 font-semibold text-slate-800 dark:text-slate-200">
                            روند روزانه (۱۴ روز اخیر در بازه)
                        </h3>
                        {dailyTrend.length === 0 ? (
                            <p className="text-sm text-slate-500">داده‌ای برای نمودار نیست.</p>
                        ) : (
                            <div className="space-y-2">
                                {dailyTrend.map((day) => (
                                    <div key={day.day} className="flex items-center gap-3 text-sm">
                                        <span className="w-24 shrink-0 text-slate-500" dir="ltr">
                                            {day.day}
                                        </span>
                                        <div className="h-2 flex-1 overflow-hidden rounded-full bg-slate-100 dark:bg-slate-800">
                                            <div
                                                className="h-full rounded-full bg-blue-500"
                                                style={{ width: `${Math.round((day.total / maxTrend) * 100)}%` }}
                                            />
                                        </div>
                                        <span className="w-12 text-left font-medium">
                                            {day.total.toLocaleString('fa-IR')}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>

                <div className="border-t border-slate-200 p-5 dark:border-slate-700">
                    <h3 className="mb-3 font-semibold text-slate-800 dark:text-slate-200">آخرین بازدیدها</h3>
                    <div className="overflow-x-auto rounded-xl border border-slate-200 dark:border-slate-700">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>زمان</TableHead>
                                    <TableHead>صفحه</TableHead>
                                    <TableHead>بازدیدکننده</TableHead>
                                    <TableHead>IP</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {recentVisits.data.length === 0 ? (
                                    <TableEmptyState colSpan={4} message="بازدیدی ثبت نشده است." />
                                ) : (
                                    recentVisits.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="whitespace-nowrap text-sm">
                                                {row.created_at
                                                    ? formatPersianDateTime(row.created_at, 'table')
                                                    : '—'}
                                            </TableCell>
                                            <TableCell>
                                                <span className="text-xs text-slate-500">{row.visitable_type_label}</span>
                                                {' — '}
                                                {row.visitable_title}
                                            </TableCell>
                                            <TableCell>{row.visitor_label}</TableCell>
                                            <TableCell className="font-mono text-xs" dir="ltr">
                                                {row.ip_address ?? '—'}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </div>
                    <SimpleTablePagination
                        className="mt-4 border-t pt-4"
                        currentPage={recentVisits.current_page}
                        lastPage={recentVisits.last_page}
                        total={recentVisits.total}
                        perPage={recentVisits.per_page}
                        from={null}
                        to={null}
                        itemLabel="بازدید"
                        onPageChange={(page) => applyVisitFilters({ page })}
                        onPerPageChange={(perPage) => applyVisitFilters({ per_page: perPage, page: 1 })}
                    />
                </div>
            </Card>
        </AdminLayout>
    );
}
