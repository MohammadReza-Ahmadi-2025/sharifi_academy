import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import { formatPersianDateTime } from '@/Components/Admin/shared/lib/format-persian-datetime';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { ExternalLink, MessageSquarePlus, Pencil, Plus, Trash2 } from 'lucide-react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { FormEvent, useState } from 'react';

type ChildComment = {
    id: number;
    body: string;
    author_name: string;
    status: number;
    status_label: string;
    created_at: string | null;
    children: ChildComment[];
};

type Row = {
    id: number;
    body: string;
    author_name: string;
    status: number;
    status_label: string;
    rating: number | null;
    commentable_type_label: string;
    commentable_title: string | null;
    commentable_url: string | null;
    created_at: string | null;
    children: ChildComment[];
};

type Props = {
    comments: {
        data: Row[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    filters: { search: string; status: string; per_page: number };
    statusOptions: { value: string; label: string }[];
    abilities: { create: boolean; update: boolean; delete: boolean };
};

function CommentTarget({
    typeLabel,
    title,
    url,
}: {
    typeLabel: string;
    title: string | null;
    url: string | null;
}) {
    return (
        <div className="mt-1 flex flex-wrap items-center gap-2 text-xs">
            <Badge variant="info">{typeLabel}</Badge>
            {title ? (
                url ? (
                    <Link
                        href={url}
                        target="_blank"
                        className="inline-flex max-w-full items-center gap-1 font-medium text-primary hover:underline"
                    >
                        <span className="truncate">{title}</span>
                        <ExternalLink className="size-3.5 shrink-0" aria-hidden />
                    </Link>
                ) : (
                    <span className="font-medium text-slate-700 dark:text-slate-300">{title}</span>
                )
            ) : (
                <span className="text-slate-400">منبع نامشخص</span>
            )}
        </div>
    );
}

function ReplyForm({ commentId }: { commentId: number }) {
    const [open, setOpen] = useState(false);
    const form = useForm({ body: '', display_name: '' });

    if (!open) {
        return (
            <AdminButton type="button" variant="ghost" size="sm" onClick={() => setOpen(true)}>
                <MessageSquarePlus className="size-4" />
                پاسخ
            </AdminButton>
        );
    }

    return (
        <form
            className="mt-2 space-y-2 rounded-md border border-slate-200 bg-slate-50 p-3 dark:border-slate-700 dark:bg-slate-900/40"
            onSubmit={(e) => {
                e.preventDefault();
                form.post(`/admin/comments/${commentId}/reply`, {
                    preserveScroll: true,
                    onSuccess: () => {
                        form.reset();
                        setOpen(false);
                    },
                });
            }}
        >
            <FormField label="نام نمایشی (اختیاری)" error={form.errors.display_name}>
                <AdminInput
                    value={form.data.display_name}
                    disabled={form.processing}
                    placeholder="خالی = پاسخ با نام ادمین"
                    onChange={(e) => form.setData('display_name', e.target.value)}
                />
            </FormField>
            <FormField label="پاسخ" required error={form.errors.body}>
                <Textarea
                    plain
                    rows={3}
                    value={form.data.body}
                    disabled={form.processing}
                    placeholder="متن پاسخ ادمین..."
                    onChange={(e) => form.setData('body', e.target.value)}
                />
            </FormField>
            <div className="flex gap-2">
                <AdminButton type="submit" size="sm" disabled={form.processing || form.data.body.trim() === ''}>
                    ارسال پاسخ
                </AdminButton>
                <AdminButton type="button" variant="secondary" size="sm" onClick={() => setOpen(false)}>
                    انصراف
                </AdminButton>
            </div>
        </form>
    );
}

function CommentReplies({
    items,
    canReply,
    depth = 0,
}: {
    items: ChildComment[];
    canReply: boolean;
    depth?: number;
}) {
    if (items.length === 0) {
        return null;
    }

    return (
        <ul className={`mt-3 space-y-3 ${depth > 0 ? 'mr-3 border-r border-slate-200 pr-3 dark:border-slate-700' : ''}`}>
            {items.map((child) => (
                <li key={child.id} className="rounded-lg bg-slate-50 p-3 dark:bg-slate-900/30">
                    <div className="flex flex-wrap items-center justify-between gap-2">
                        <div className="font-medium text-slate-800 dark:text-slate-200">{child.author_name}</div>
                        <Badge variant={child.status === 1 ? 'default' : child.status === 2 ? 'destructive' : 'secondary'}>
                            {child.status_label}
                        </Badge>
                    </div>
                    <p className="mt-1 text-sm text-slate-600 dark:text-slate-400">{child.body}</p>
                    {child.created_at ? (
                        <p className="mt-1 text-[11px] text-slate-400">
                            {formatPersianDateTime(child.created_at, 'table')}
                        </p>
                    ) : null}
                    {canReply ? <ReplyForm commentId={child.id} /> : null}
                    <CommentReplies items={child.children} canReply={canReply} depth={depth + 1} />
                </li>
            ))}
        </ul>
    );
}

export default function CommentsIndex({ comments, filters, statusOptions, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const [search, setSearch] = useState(filters.search);
    const [status, setStatus] = useState(filters.status);

    const applyFilters = (overrides: Record<string, string | number | undefined> = {}) => {
        router.get(
            '/admin/comments',
            {
                search: search || undefined,
                status: status || undefined,
                per_page: filters.per_page,
                ...overrides,
            },
            { preserveState: true, replace: true },
        );
    };

    const clearFilters = () => {
        setSearch('');
        setStatus('');
        router.get('/admin/comments', { per_page: filters.per_page }, { replace: true });
    };

    const applySearch = (e: FormEvent) => {
        e.preventDefault();
        applyFilters({ page: 1 });
    };

    const statusVariant = (value: number) => {
        if (value === 1) return 'default';
        if (value === 2) return 'destructive';
        return 'secondary';
    };

    return (
        <AdminLayout>
            <Head title="نظرات" />
            <PageHeader
                title="نظرات"
                description="نظرات دوره‌ها، مقالات وبلاگ و پست‌های کاربری را ببینید و پاسخ دهید."
                actions={
                    abilities.create ? (
                        <AdminButton href="/admin/comments/create">
                            <Plus className="size-4" />
                            نظر دستی
                        </AdminButton>
                    ) : null
                }
            />
            <AdminListFilters
                search={search}
                onSearchChange={setSearch}
                onSubmit={applySearch}
                onClear={clearFilters}
                searchPlaceholder="جستجو متن یا نام نمایشی…"
            >
                <AdminFilterSelect
                    className="lg:col-span-2"
                    value={status}
                    onChange={(e) => setStatus(e.target.value)}
                    placeholder="همه وضعیت‌ها"
                    options={statusOptions.map((option) => ({
                        value: String(option.value),
                        label: option.label,
                    }))}
                />
            </AdminListFilters>
            <div className="mt-4 space-y-4">
                {comments.data.length === 0 ? (
                    <Card className="p-8 text-center text-slate-500">نظری ثبت نشده است.</Card>
                ) : (
                    comments.data.map((row) => (
                        <Card key={row.id} className="p-4">
                            <div className="flex flex-wrap items-start justify-between gap-2">
                                <div className="min-w-0 flex-1">
                                    <div className="font-semibold">{row.author_name}</div>
                                    <CommentTarget
                                        typeLabel={row.commentable_type_label}
                                        title={row.commentable_title}
                                        url={row.commentable_url}
                                    />
                                    {row.created_at ? (
                                        <span className="mt-1 block text-xs text-slate-500">
                                            {formatPersianDateTime(row.created_at, 'table')}
                                        </span>
                                    ) : null}
                                </div>
                                <Badge variant={statusVariant(row.status)}>{row.status_label}</Badge>
                            </div>
                            <p className="mt-3 whitespace-pre-wrap text-slate-700 dark:text-slate-300">{row.body}</p>
                            <CommentReplies items={row.children} canReply={abilities.update} />
                            {abilities.update ? <ReplyForm commentId={row.id} /> : null}
                            <div className="mt-4 flex flex-wrap gap-2">
                                {abilities.update && row.status !== 1 ? (
                                    <AdminButton
                                        size="sm"
                                        variant="secondary"
                                        onClick={() =>
                                            router.post(`/admin/comments/${row.id}/approve`, {}, { preserveScroll: true })
                                        }
                                    >
                                        تأیید
                                    </AdminButton>
                                ) : null}
                                {abilities.update && row.status !== 2 ? (
                                    <AdminButton
                                        size="sm"
                                        variant="secondary"
                                        onClick={() =>
                                            router.post(`/admin/comments/${row.id}/reject`, {}, { preserveScroll: true })
                                        }
                                    >
                                        رد
                                    </AdminButton>
                                ) : null}
                                {abilities.update ? (
                                    <AdminButton variant="ghost" size="sm" href={`/admin/comments/${row.id}/edit`}>
                                        <Pencil className="size-4" />
                                        ویرایش
                                    </AdminButton>
                                ) : null}
                                {abilities.delete ? (
                                    <AdminButton
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            confirmToast('نظر و پاسخ‌ها حذف شوند؟', () =>
                                                router.delete(`/admin/comments/${row.id}`),
                                            )
                                        }
                                    >
                                        <Trash2 className="size-4 text-red-600" />
                                    </AdminButton>
                                ) : null}
                            </div>
                        </Card>
                    ))
                )}
            </div>
            <SimpleTablePagination
                className="mt-4"
                currentPage={comments.current_page}
                lastPage={comments.last_page}
                total={comments.total}
                perPage={comments.per_page}
                from={null}
                to={null}
                itemLabel="نظر"
                onPageChange={(page) => applyFilters({ page })}
                onPerPageChange={(perPage) => applyFilters({ per_page: perPage, page: 1 })}
            />
        </AdminLayout>
    );
}
