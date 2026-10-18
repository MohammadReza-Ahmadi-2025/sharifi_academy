import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { PersianDatePicker } from '@/Components/Admin/shared/ui/persian-date-picker';
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
import { Eye } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    description: string;
    action: string;
    action_label: string;
    module: string;
    module_label: string;
    ip_address: string | null;
    user: { id: number; name: string; mobile: string } | null;
    created_at: string | null;
};

type Props = {
    logs: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: {
        search: string;
        module: string;
        action: string;
        user_id: string;
        period: string;
        from: string;
        to: string;
        per_page: number;
    };
    moduleOptions: { value: string; label: string }[];
    actionOptions: { value: string; label: string }[];
    actionStats: Record<string, number>;
    userOptions: { value: string; label: string }[];
    hasActiveFilters: boolean;
    activeFilterLabels: { key: string; label: string }[];
    abilities: { show: boolean };
};

const PERIOD_OPTIONS = [
    { value: '', label: 'همه' },
    { value: 'today', label: 'امروز' },
    { value: 'week', label: '۷ روز اخیر' },
    { value: 'month', label: '۳۰ روز اخیر' },
] as const;

const ACTION_BADGE: Record<string, string> = {
    created: 'bg-emerald-50 text-emerald-700 ring-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-300',
    updated: 'bg-blue-50 text-blue-700 ring-blue-200 dark:bg-blue-900/20 dark:text-blue-300',
    deleted: 'bg-rose-50 text-rose-700 ring-rose-200 dark:bg-rose-900/20 dark:text-rose-300',
    restored: 'bg-violet-50 text-violet-700 ring-violet-200 dark:bg-violet-900/20 dark:text-violet-300',
};

export default function ActivityLogsIndex({
    logs,
    filters,
    moduleOptions,
    actionOptions,
    actionStats,
    userOptions,
    hasActiveFilters,
    activeFilterLabels,
    abilities,
}: Props) {
    const [search, setSearch] = useState(filters.search);
    const [module, setModule] = useState(filters.module);
    const [action, setAction] = useState(filters.action);
    const [userId, setUserId] = useState(filters.user_id);
    const [from, setFrom] = useState(filters.from);
    const [to, setTo] = useState(filters.to);
    const [perPage, setPerPage] = useState(String(filters.per_page));

    const buildQuery = (overrides: Record<string, string | number | null | undefined> = {}) => {
        const period =
            overrides.period !== undefined ? String(overrides.period) : filters.period;
        const useCustom = period === 'custom' || Boolean(from || to);

        const base: Record<string, string | number | undefined> = {
            search: String(overrides.search !== undefined ? overrides.search : search || '') || undefined,
            module: String(overrides.module !== undefined ? overrides.module : module || '') || undefined,
            action: String(overrides.action !== undefined ? overrides.action : action || '') || undefined,
            user_id: String(overrides.user_id !== undefined ? overrides.user_id : userId || '') || undefined,
            per_page: overrides.per_page ?? (Number(perPage) || filters.per_page),
            page: overrides.page as number | undefined,
        };

        if (useCustom) {
            return {
                ...base,
                period: 'custom',
                from: from || undefined,
                to: to || undefined,
            };
        }

        if (period) {
            return { ...base, period };
        }

        return base;
    };

    const applyFilters = (overrides: Record<string, string | number | null | undefined> = {}) => {
        router.get('/admin/activity-logs', buildQuery(overrides), { preserveState: true, replace: true });
    };

    const selectPeriod = (period: string) => {
        setFrom('');
        setTo('');
        applyFilters({ period, from: null, to: null, page: 1 });
    };

    const submitFilters = (e: FormEvent) => {
        e.preventDefault();
        const period = from || to ? 'custom' : filters.period;
        applyFilters({ period, page: 1 });
    };

    const clearFilters = () => {
        setSearch('');
        setModule('');
        setAction('');
        setUserId('');
        setFrom('');
        setTo('');
        router.get('/admin/activity-logs', { per_page: filters.per_page }, { replace: true });
    };

    return (
        <AdminLayout>
            <Head title="گزارش فعالیت‌ها" />
            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <PageHeader
                    title="گزارش فعالیت‌ها"
                    description="پیگیری اقدامات کاربران در پنل ادمین"
                />
                <div className="rounded-xl bg-slate-50 px-4 py-3 text-sm dark:bg-slate-900/50">
                    <span className="text-slate-500">نتایج: </span>
                    <span className="font-bold">{logs.total.toLocaleString('fa-IR')}</span>
                    <span className="text-slate-500"> فعالیت</span>
                </div>
            </div>

            <Card className="mt-4 overflow-hidden">
                <form onSubmit={submitFilters} className="space-y-5 border-b border-slate-200 p-5 dark:border-slate-700">
                    <div className="space-y-2">
                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">بازه زمانی</span>
                        <div className="flex flex-wrap gap-2">
                            {PERIOD_OPTIONS.map((opt) => (
                                <AdminButton
                                    key={opt.label}
                                    type="button"
                                    size="sm"
                                    variant={
                                        filters.period === opt.value ||
                                        (opt.value === '' && !filters.period && !filters.from && !filters.to)
                                            ? 'default'
                                            : 'outline'
                                    }
                                    className="rounded-full"
                                    onClick={() => selectPeriod(opt.value)}
                                >
                                    {opt.label}
                                </AdminButton>
                            ))}
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                        <div className="space-y-1">
                            <label className="text-sm text-slate-600 dark:text-slate-400">کاربر</label>
                            <select
                                value={userId}
                                onChange={(e) => setUserId(e.target.value)}
                                className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                            >
                                <option value="">همه کاربران</option>
                                {userOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>
                        <div className="space-y-1">
                            <label className="text-sm text-slate-600 dark:text-slate-400">نوع عملیات</label>
                            <select
                                value={action}
                                onChange={(e) => setAction(e.target.value)}
                                className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                            >
                                <option value="">همه عملیات‌ها</option>
                                {actionOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>
                                        {opt.label}
                                        {actionStats[opt.value] ? ` (${actionStats[opt.value].toLocaleString('fa-IR')})` : ''}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="space-y-1">
                            <label className="text-sm text-slate-600 dark:text-slate-400">بخش / ماژول</label>
                            <select
                                value={module}
                                onChange={(e) => setModule(e.target.value)}
                                className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                            >
                                <option value="">همه بخش‌ها</option>
                                {moduleOptions.map((opt) => (
                                    <option key={opt.value} value={opt.value}>{opt.label}</option>
                                ))}
                            </select>
                        </div>
                        <div className="space-y-1">
                            <label className="text-sm text-slate-600 dark:text-slate-400">جستجو</label>
                            <AdminInput
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                                placeholder="شرح، IP، مسیر…"
                            />
                        </div>
                    </div>

                    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4 items-end">
                        <PersianDatePicker
                            label="از تاریخ"
                            value={from}
                            onChange={setFrom}
                            placeholder="شروع بازه"
                        />
                        <PersianDatePicker
                            label="تا تاریخ"
                            value={to}
                            onChange={setTo}
                            min={from || undefined}
                            placeholder="پایان بازه"
                        />
                        <div className="space-y-1">
                            <label className="text-sm text-slate-600 dark:text-slate-400">تعداد در صفحه</label>
                            <select
                                value={perPage}
                                onChange={(e) => setPerPage(e.target.value)}
                                className="flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                            >
                                {[25, 50, 100].map((n) => (
                                    <option key={n} value={n}>{n} مورد</option>
                                ))}
                            </select>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <AdminButton type="submit" className="flex-1">اعمال فیلتر</AdminButton>
                            {hasActiveFilters && (
                                <AdminButton type="button" variant="outline" onClick={clearFilters}>
                                    پاک کردن
                                </AdminButton>
                            )}
                        </div>
                    </div>

                    <div className="space-y-2">
                        <span className="text-sm font-medium text-slate-700 dark:text-slate-300">فیلتر سریع عملیات</span>
                        <div className="flex flex-wrap gap-2">
                            <button
                                type="button"
                                onClick={() => applyFilters({ action: null, page: 1 })}
                                className={cn(
                                    'inline-flex items-center rounded-full px-3 py-1.5 text-xs font-medium ring-1',
                                    !filters.action
                                        ? 'bg-slate-900 text-white ring-slate-900 dark:bg-white dark:text-slate-900'
                                        : 'bg-white text-slate-600 ring-slate-300 dark:bg-slate-800 dark:text-slate-300',
                                )}
                            >
                                همه
                            </button>
                            {actionOptions.map((opt) => {
                                const count = actionStats[opt.value] ?? 0;
                                const isActive = filters.action === opt.value;
                                const badge = ACTION_BADGE[opt.value] ?? 'bg-slate-50 text-slate-700 ring-slate-200';

                                return (
                                    <button
                                        key={opt.value}
                                        type="button"
                                        onClick={() => applyFilters({ action: opt.value, page: 1 })}
                                        className={cn(
                                            'inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-xs font-medium ring-1 transition',
                                            isActive ? 'ring-2 ring-offset-1 ring-slate-900 dark:ring-white' : '',
                                            badge,
                                        )}
                                    >
                                        {opt.label}
                                        {count > 0 && (
                                            <span className="rounded-full bg-black/10 px-1.5 py-0.5 text-[10px]">
                                                {count.toLocaleString('fa-IR')}
                                            </span>
                                        )}
                                    </button>
                                );
                            })}
                        </div>
                    </div>

                    {hasActiveFilters && activeFilterLabels.length > 0 && (
                        <div className="rounded-xl border border-dashed border-slate-300 p-4 dark:border-slate-600">
                            <div className="flex flex-wrap items-center gap-2">
                                <span className="text-xs text-slate-500">فیلترهای فعال:</span>
                                {activeFilterLabels.map((chip) => (
                                    <span
                                        key={chip.key}
                                        className="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs text-slate-700 dark:bg-slate-800 dark:text-slate-200"
                                    >
                                        {chip.label}
                                    </span>
                                ))}
                            </div>
                        </div>
                    )}
                </form>

                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>زمان</TableHead>
                            <TableHead>کاربر</TableHead>
                            <TableHead>بخش</TableHead>
                            <TableHead>عملیات</TableHead>
                            <TableHead className="min-w-[200px]">شرح</TableHead>
                            <TableHead>IP</TableHead>
                            <TableHead className="w-16" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {logs.data.length === 0 ? (
                            <TableEmptyState
                                colSpan={7}
                                message={
                                    hasActiveFilters
                                        ? 'فعالیتی با این فیلترها یافت نشد.'
                                        : 'گزارشی ثبت نشده است.'
                                }
                            />
                        ) : (
                            logs.data.map((row) => {
                                const actionBadge = ACTION_BADGE[row.action] ?? 'bg-slate-50 text-slate-700 ring-slate-200';

                                return (
                                    <TableRow key={row.id}>
                                        <TableCell className="whitespace-nowrap text-sm text-slate-600 dark:text-slate-300">
                                            {formatPersianDateTime(row.created_at, 'table')}
                                        </TableCell>
                                        <TableCell>
                                            {row.user ? (
                                                <button
                                                    type="button"
                                                    className="text-right hover:text-blue-600"
                                                    onClick={() => applyFilters({ user_id: String(row.user!.id), page: 1 })}
                                                >
                                                    <div className="font-medium">{row.user.name}</div>
                                                    <div className="text-xs text-slate-500" dir="ltr">{row.user.mobile}</div>
                                                </button>
                                            ) : (
                                                '—'
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <button
                                                type="button"
                                                className="inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium hover:bg-slate-200 dark:bg-slate-800"
                                                onClick={() => applyFilters({ module: row.module, page: 1 })}
                                            >
                                                {row.module_label}
                                            </button>
                                        </TableCell>
                                        <TableCell>
                                            <button
                                                type="button"
                                                className={cn(
                                                    'inline-flex rounded-full px-2.5 py-1 text-xs font-medium ring-1',
                                                    actionBadge,
                                                )}
                                                onClick={() => applyFilters({ action: row.action, page: 1 })}
                                            >
                                                {row.action_label}
                                            </button>
                                        </TableCell>
                                        <TableCell className="max-w-md truncate">{row.description}</TableCell>
                                        <TableCell className="font-mono text-xs text-slate-500" dir="ltr">
                                            {row.ip_address ?? '—'}
                                        </TableCell>
                                        <TableCell>
                                            {abilities.show && (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/admin/activity-logs/${row.id}`}
                                                >
                                                    <Eye className="size-4" />
                                                </AdminButton>
                                            )}
                                        </TableCell>
                                    </TableRow>
                                );
                            })
                        )}
                    </TableBody>
                </Table>
                <SimpleTablePagination
                    className="border-t"
                    currentPage={logs.current_page}
                    lastPage={logs.last_page}
                    total={logs.total}
                    perPage={logs.per_page}
                    from={null}
                    to={null}
                    itemLabel="رکورد"
                    onPageChange={(page) => applyFilters({ page })}
                    onPerPageChange={(pp) => applyFilters({ per_page: pp, page: 1 })}
                />
            </Card>
            {hasActiveFilters && logs.data.length === 0 && (
                <div className="mt-2 text-center">
                    <Link href="/admin/activity-logs" className="text-sm text-blue-600 hover:underline">
                        حذف فیلترها و مشاهده همه
                    </Link>
                </div>
            )}
        </AdminLayout>
    );
}
