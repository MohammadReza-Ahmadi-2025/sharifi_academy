import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import {
    Table,
    TableBody,
    TableCell,
    TableEmptyState,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Pencil, Plus, Search, Trash2, UserRound } from 'lucide-react';
import { FormEvent, useState } from 'react';

export type AdminUserListItem = {
    id: number;
    name: string | null;
    family: string | null;
    fullName: string;
    mobile: string;
    gender: 'male' | 'female' | null;
    email: string | null;
    status: number;
    statusLabel: string;
    roles: { name: string; label: string }[];
    avatar: { url: string; alt?: string | null; title?: string | null } | null;
    lastLoginAt: string | null;
    canUpdate: boolean;
    canDelete: boolean;
};

type PaginatedUsers = {
    data: AdminUserListItem[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
};

type RoleFilter = { value: string; label: string };

type Props = {
    users: PaginatedUsers;
    filters: { search: string; role: string; status: string; per_page: number };
    roleFilters: RoleFilter[];
    abilities: { create: boolean };
};

const emptyPaginated: PaginatedUsers = {
    data: [],
    current_page: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    from: null,
    to: null,
};

export default function UsersIndex({ users, filters, roleFilters, abilities }: Props) {
    const page = users ?? emptyPaginated;
    const rows = page.data ?? [];
    const [search, setSearch] = useState(filters.search);
    const [role, setRole] = useState(filters.role);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/users',
            {
                search: search || undefined,
                role: role || undefined,
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

    const handleDelete = (user: AdminUserListItem) => {
        if (!window.confirm(`کاربر «${user.fullName}» حذف شود؟`)) {
            return;
        }

        router.delete(`/admin/users/${user.id}`, { preserveScroll: true });
    };

    return (
        <AdminLayout>
            <Head title="کاربران" />

            <PageHeader
                title="کاربران"
                description="مدیریت حساب‌ها، نقش‌ها و وضعیت ورود"
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/users/create">
                            <Plus className="ml-1 h-4 w-4" />
                            کاربر جدید
                        </AdminButton>
                    ) : null
                }
            />

            <Card className="mt-6 space-y-4 p-4">
                <form onSubmit={submitSearch} className="grid gap-3 lg:grid-cols-4">
                    <div className="relative lg:col-span-2">
                        <Search className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400" />
                        <AdminInput
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            placeholder="جستجو: نام، موبایل، ایمیل..."
                            className="pr-9"
                        />
                    </div>
                    <select
                        value={role}
                        onChange={(e) => setRole(e.target.value)}
                        className="flex h-10 rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">همه نقش‌ها</option>
                        {roleFilters.map((item) => (
                            <option key={item.value} value={item.value}>{item.label}</option>
                        ))}
                    </select>
                    <select
                        value={status}
                        onChange={(e) => setStatus(e.target.value)}
                        className="flex h-10 rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-950"
                    >
                        <option value="">همه وضعیت‌ها</option>
                        <option value="1">فعال</option>
                        <option value="0">غیرفعال</option>
                    </select>
                    <div className="flex gap-2 lg:col-span-4">
                        <AdminButton type="submit">اعمال فیلتر</AdminButton>
                        <AdminButton
                            type="button"
                            variant="outline"
                            onClick={() => {
                                setSearch('');
                                setRole('');
                                setStatus('');
                                router.get('/admin/users', {}, { replace: true });
                            }}
                        >
                            پاک کردن
                        </AdminButton>
                    </div>
                </form>
            </Card>

            <Card className="mt-4 overflow-hidden p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>کاربر</TableHead>
                            <TableHead>موبایل</TableHead>
                            <TableHead>نقش‌ها</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>آخرین ورود</TableHead>
                            <TableHead className="w-28 text-left">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {rows.length === 0 ? (
                            <TableEmptyState
                                colSpan={6}
                                title="کاربری پیدا نشد"
                                description="فیلترها را تغییر دهید یا کاربر جدید اضافه کنید."
                            />
                        ) : (
                            rows.map((user) => (
                                <TableRow key={user.id}>
                                    <TableCell>
                                        <div className="flex items-center gap-3">
                                            {user.avatar?.url ? (
                                                <img
                                                    src={user.avatar.url}
                                                    alt={user.avatar.alt ?? user.fullName}
                                                    className="h-9 w-9 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700"
                                                />
                                            ) : (
                                                <span className="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 dark:bg-slate-800">
                                                    <UserRound className="h-4 w-4" />
                                                </span>
                                            )}
                                            <div className="min-w-0">
                                                <div className="font-medium text-slate-900 dark:text-slate-100">{user.fullName}</div>
                                                {user.email ? (
                                                    <div className="text-xs text-slate-500 dark:text-slate-400" dir="ltr">{user.email}</div>
                                                ) : null}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="font-mono text-sm" dir="ltr">{user.mobile}</TableCell>
                                    <TableCell>
                                        <div className="flex flex-wrap gap-1">
                                            {user.roles.map((r) => (
                                                <Badge key={r.name} variant="secondary">{r.label}</Badge>
                                            ))}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={user.status === 1 ? 'success' : 'warning'}>{user.statusLabel}</Badge>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-600 dark:text-slate-300">
                                        {formatPersianDateTime(user.lastLoginAt, 'table')}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-center justify-end gap-1">
                                            {user.canUpdate ? (
                                                <Link
                                                    href={`/admin/users/${user.id}/edit`}
                                                    className="inline-flex h-8 w-8 items-center justify-center rounded-md text-slate-600 hover:bg-slate-100 dark:text-slate-300 dark:hover:bg-slate-800"
                                                    title="ویرایش"
                                                >
                                                    <Pencil className="h-4 w-4" />
                                                </Link>
                                            ) : null}
                                            {user.canDelete ? (
                                                <button
                                                    type="button"
                                                    onClick={() => handleDelete(user)}
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
                itemLabel="کاربر"
                onPageChange={(nextPage) => applyFilters({ page: nextPage })}
                onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
            />
        </AdminLayout>
    );
}
