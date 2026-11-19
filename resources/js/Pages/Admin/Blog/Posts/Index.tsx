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
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router } from '@inertiajs/react';
import { AdminSeoSettingsButton } from '@/Components/Admin/shared/ui/admin-seo-settings-button';
import { ExternalLink, Eye, Pencil, Plus, Settings2, Trash2, UserRound } from 'lucide-react';
import { FormEvent, useState } from 'react';

type Row = {
    id: number;
    title: string;
    slug: string;
    status: string;
    status_label: string;
    author_name: string | null;
    author_mobile: string | null;
    category_titles: string[];
    published_at: string | null;
    published_at_label: string | null;
    created_at_label: string | null;
    views: number;
    image_url: string | null;
    public_url: string | null;
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
    statusCounts: {
        all: number;
        published: number;
        pending_review: number;
        draft: number;
    };
    abilities: { create: boolean; update: boolean; delete: boolean };
};

function statusBadgeVariant(status: string): 'default' | 'secondary' | 'destructive' | 'outline' {
    if (status === 'published') {
        return 'default';
    }
    if (status === 'pending_review') {
        return 'outline';
    }

    return 'secondary';
}

export default function BlogPostsIndex({
    posts,
    filters,
    categoryOptions,
    statusOptions,
    statusCounts,
    abilities,
}: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status ?? 'all');
    const [categoryId, setCategoryId] = useState(filters.category_id ? String(filters.category_id) : '');
    const categorySelectOptions = mapToSelectOptions(categoryOptions);
    const statusSelectOptions = mapToSelectOptions(statusOptions);

    const applyFilters = (overrides: Record<string, string | number | undefined | null> = {}) => {
        router.get(
            '/admin/posts',
            {
                search: search || undefined,
                status: (overrides.status as string | undefined) ?? status ?? undefined,
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
        setStatus('all');
        setCategoryId('');
        router.get('/admin/posts', { status: 'all', per_page: filters.per_page }, { replace: true });
    };

    const statusTabs: { value: string; label: string; count: number }[] = [
        { value: 'all', label: 'همه', count: statusCounts.all },
        { value: 'pending_review', label: 'در انتظار تأیید', count: statusCounts.pending_review },
        { value: 'published', label: 'تأیید / منتشر شده', count: statusCounts.published },
        { value: 'draft', label: 'پیش‌نویس', count: statusCounts.draft },
    ];

    return (
        <AdminLayout>
            <Head title="پست‌ها" />
            <PageHeader
                title="مقالات و پست‌ها"
                description="مقاله وبلاگ فقط از اینجا ساخته می‌شود. پست‌های کاربران را هم برای تأیید ببینید."
                actions={
                    <div className="flex flex-wrap gap-2">
                        {abilities.update ? (
                            <>
                                <AdminButton variant="outline" href="/admin/posts/settings">
                                    <Settings2 className="size-4" />
                                    سئوی وبلاگ
                                </AdminButton>
                                <AdminButton variant="outline" href="/admin/posts/feed-settings">
                                    <Settings2 className="size-4" />
                                    سئوی پست‌ها
                                </AdminButton>
                            </>
                        ) : null}
                        <AdminSeoSettingsButton />
                        {abilities.create ? (
                            <AdminButton href="/admin/posts/create">
                                <Plus className="size-4" />
                                مقاله وبلاگ
                            </AdminButton>
                        ) : null}
                    </div>
                }
            />

            <div className="mb-4 flex flex-wrap gap-2">
                {statusTabs.map((tab) => {
                    const active = (filters.status ?? 'all') === tab.value;
                    return (
                        <button
                            key={tab.value}
                            type="button"
                            onClick={() => {
                                setStatus(tab.value);
                                applyFilters({ status: tab.value, page: 1 });
                            }}
                            className={`inline-flex items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-semibold transition ${
                                active
                                    ? 'bg-primary text-on-primary shadow-sm'
                                    : 'border border-slate-200 bg-white text-slate-600 hover:border-primary/30 hover:text-primary'
                            }`}
                        >
                            {tab.label}
                            <span
                                className={`rounded-full px-1.5 py-0.5 text-[11px] ${
                                    active ? 'bg-white/20' : 'bg-slate-100 text-slate-500'
                                }`}
                            >
                                {tab.count.toLocaleString('fa-IR')}
                            </span>
                        </button>
                    );
                })}
            </div>

            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو عنوان، اسلاگ یا نویسنده…"
            >
                <Select
                    className="lg:col-span-2 min-w-0"
                    isClearable
                    placeholder="وضعیت"
                    options={statusSelectOptions}
                    value={findSelectOption(statusSelectOptions, status)}
                    onChange={(option) => setStatus(option?.value ?? 'all')}
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
                            <TableHead className="w-16">تصویر</TableHead>
                            <TableHead>عنوان</TableHead>
                            <TableHead>نویسنده</TableHead>
                            <TableHead>دسته‌ها</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>انتشار</TableHead>
                            <TableHead>بازدید</TableHead>
                            <TableHead className="w-32" />
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {posts.data.length === 0 ? (
                            <TableEmptyState colSpan={8} message="پستی با این فیلتر پیدا نشد." />
                        ) : (
                            posts.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell>
                                        <div className="size-12 overflow-hidden rounded-lg bg-slate-100">
                                            {row.image_url ? (
                                                <img src={row.image_url} alt="" className="size-full object-cover" />
                                            ) : (
                                                <div className="flex size-full items-center justify-center text-[10px] text-slate-400">
                                                    —
                                                </div>
                                            )}
                                        </div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="max-w-xs font-medium leading-snug">{row.title}</div>
                                        <div className="mt-0.5 font-mono text-xs text-slate-500">{row.slug}</div>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex items-start gap-2">
                                            <span className="mt-0.5 flex size-7 shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500">
                                                <UserRound className="size-3.5" />
                                            </span>
                                            <div>
                                                <div className="text-sm font-medium">{row.author_name || '—'}</div>
                                                {row.author_mobile ? (
                                                    <div className="text-xs text-slate-500" dir="ltr">
                                                        {row.author_mobile}
                                                    </div>
                                                ) : null}
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell className="max-w-[10rem] text-sm text-slate-600">
                                        {row.category_titles.length > 0 ? row.category_titles.join('، ') : '—'}
                                    </TableCell>
                                    <TableCell>
                                        <Badge variant={statusBadgeVariant(row.status)}>{row.status_label}</Badge>
                                    </TableCell>
                                    <TableCell className="whitespace-nowrap text-sm text-slate-600">
                                        {row.published_at_label || row.created_at_label || '—'}
                                    </TableCell>
                                    <TableCell>
                                        <span className="inline-flex items-center gap-1 text-sm text-slate-600">
                                            <Eye className="size-3.5 opacity-60" />
                                            {row.views.toLocaleString('fa-IR')}
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            {row.public_url ? (
                                                <a
                                                    href={row.public_url}
                                                    target="_blank"
                                                    rel="noopener noreferrer"
                                                    title="مشاهده در سایت"
                                                    className="inline-flex h-8 items-center justify-center rounded-md px-3 text-xs text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-700"
                                                >
                                                    <ExternalLink className="size-4" />
                                                </a>
                                            ) : null}
                                            {abilities.update ? (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    href={`/admin/posts/${row.id}/edit`}
                                                    title="ویرایش"
                                                >
                                                    <Pencil className="size-4" />
                                                </AdminButton>
                                            ) : null}
                                            {abilities.delete ? (
                                                <AdminButton
                                                    variant="ghost"
                                                    size="sm"
                                                    title="حذف"
                                                    onClick={() =>
                                                        confirmToast('این پست حذف شود؟', () =>
                                                            router.delete(`/admin/posts/${row.id}`),
                                                        )
                                                    }
                                                >
                                                    <Trash2 className="size-4 text-red-600" />
                                                </AdminButton>
                                            ) : null}
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
