import { AdminFilterSelect } from '@/Components/Admin/shared/ui/admin-filter-select';
import { AdminListFilters } from '@/Components/Admin/shared/ui/admin-list-filters';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { SimpleTablePagination } from '@/Components/Admin/shared/ui/simple-table-pagination';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { MessageSquarePlus, Pencil, Plus, Trash2 } from 'lucide-react';
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

function CommentReplies({ items, depth = 0 }: { items: ChildComment[]; depth?: number }) {
    if (items.length === 0) {
        return null;
    }

    return (
        <ul className={`mt-2 space-y-2 ${depth > 0 ? 'mr-4 border-r pr-3' : ''}`}>
            {items.map((child) => (
                <li key={child.id} className="text-sm">
                    <div className="font-medium text-slate-800 dark:text-slate-200">{child.author_name}</div>
                    <p className="text-slate-600 dark:text-slate-400">{child.body}</p>
                    <CommentReplies items={child.children} depth={depth + 1} />
                </li>
            ))}
        </ul>
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
            className="mt-2 space-y-2 rounded-md border p-3"
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
                    onChange={(e) => form.setData('display_name', e.target.value)}
                />
            </FormField>
            <FormField label="پاسخ" required error={form.errors.body}>
                <Textarea
                    rows={3}
                    value={form.data.body}
                    disabled={form.processing}
                    onChange={(e) => form.setData('body', e.target.value)}
                />
            </FormField>
            <div className="flex gap-2">
                <AdminButton type="submit" size="sm" disabled={form.processing}>ارسال</AdminButton>
                <AdminButton type="button" variant="secondary" size="sm" onClick={() => setOpen(false)}>
                    انصراف
                </AdminButton>
            </div>
        </form>
    );
}

export default function CommentsIndex({ comments, filters, statusOptions, abilities }: Props) {
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

    const statusVariant = (status: number) => {
        if (status === 1) return 'default';
        if (status === 2) return 'destructive';
        return 'secondary';
    };

    return (
        <AdminLayout>
            <Head title="نظرات" />
            <PageHeader
                title="نظرات"
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
                                <div>
                                    <div className="font-semibold">{row.author_name}</div>
                                    <div className="text-xs text-slate-500">
                                        {row.commentable_type_label}
                                        {row.commentable_title ? ` — ${row.commentable_title}` : ''}
                                    </div>
                                </div>
                                <Badge variant={statusVariant(row.status)}>{row.status_label}</Badge>
                            </div>
                            <p className="mt-3 text-slate-700 dark:text-slate-300">{row.body}</p>
                            <CommentReplies items={row.children} />
                            {abilities.update && <ReplyForm commentId={row.id} />}
                            <div className="mt-4 flex flex-wrap gap-2">
                                {abilities.update && row.status !== 1 && (
                                    <AdminButton
                                        size="sm"
                                        variant="secondary"
                                        onClick={() => router.post(`/admin/comments/${row.id}/approve`, {}, { preserveScroll: true })}
                                    >
                                        تأیید
                                    </AdminButton>
                                )}
                                {abilities.update && row.status !== 2 && (
                                    <AdminButton
                                        size="sm"
                                        variant="secondary"
                                        onClick={() => router.post(`/admin/comments/${row.id}/reject`, {}, { preserveScroll: true })}
                                    >
                                        رد
                                    </AdminButton>
                                )}
                                {abilities.update && (
                                    <AdminButton variant="ghost" size="sm" href={`/admin/comments/${row.id}/edit`}>
                                        <Pencil className="size-4" />
                                        ویرایش
                                    </AdminButton>
                                )}
                                {abilities.delete && (
                                    <AdminButton
                                        variant="ghost"
                                        size="sm"
                                        onClick={() =>
                                            window.confirm('نظر و پاسخ‌ها حذف شوند؟') &&
                                            router.delete(`/admin/comments/${row.id}`)
                                        }
                                    >
                                        <Trash2 className="size-4 text-red-600" />
                                    </AdminButton>
                                )}
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
