import { PostFormFields } from '@/Components/Admin/Blog/PostFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/Admin/shared/ui/table';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import InstructorLayout from '@/Layouts/InstructorLayout';
import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import type { ComponentType, FormEvent, ReactNode } from 'react';

type PanelMeta = { base_path: string; panel: 'instructor' | 'student' };

type LayoutProps = {
    title: string;
    description?: string;
    contentVariant?: 'panel' | 'plain';
    children: ReactNode;
};

type PostListRow = {
    id: number;
    title: string;
    status: string;
    status_label: string;
    category_titles: string[];
    published_at: string | null;
};

type PaginatedPosts = {
    data: PostListRow[];
    links: { url: string | null; label: string; active: boolean }[];
};

function usePanelLayout(panel: PanelMeta): ComponentType<LayoutProps> {
    return panel.panel === 'student' ? StudentLayout : InstructorLayout;
}

export function PanelBlogIndex({
    posts,
    panel,
    abilities,
}: {
    posts: PaginatedPosts;
    panel: PanelMeta;
    abilities: { create: boolean };
}) {
    const Layout = usePanelLayout(panel);
    const { flash } = usePage().props as { flash?: { success?: string } };

    return (
        <Layout title="وبلاگ من" description="نوشته‌های شما برای انتشار در سایت" contentVariant="plain">
            <Head title="وبلاگ من" />

            {flash?.success ? (
                <p className="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">
                    {flash.success}
                </p>
            ) : null}

            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <p className="text-sm text-slate-600">برای انتشار عمومی، وضعیت «در انتظار تایید» را انتخاب کنید تا ادمین بررسی کند.</p>
                {abilities.create ? (
                    <Link
                        href={`${panel.base_path}/create`}
                        className="inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        <Plus className="size-4" />
                        پست جدید
                    </Link>
                ) : null}
            </div>

            <div className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>عنوان</TableHead>
                            <TableHead>وضعیت</TableHead>
                            <TableHead>دسته‌ها</TableHead>
                            <TableHead className="w-[120px]">عملیات</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        {posts.data.length === 0 ? (
                            <TableRow>
                                <TableCell colSpan={4} className="text-center text-slate-500">
                                    هنوز پستی ثبت نکرده‌اید.
                                </TableCell>
                            </TableRow>
                        ) : (
                            posts.data.map((row) => (
                                <TableRow key={row.id}>
                                    <TableCell className="font-medium">{row.title}</TableCell>
                                    <TableCell>
                                        <Badge variant="secondary">{row.status_label}</Badge>
                                    </TableCell>
                                    <TableCell className="text-sm text-slate-600">
                                        {row.category_titles.join('، ') || '—'}
                                    </TableCell>
                                    <TableCell>
                                        <div className="flex gap-1">
                                            <Link
                                                href={`${panel.base_path}/${row.id}/edit`}
                                                className="rounded-lg p-2 text-indigo-600 hover:bg-indigo-50"
                                                aria-label="ویرایش"
                                            >
                                                <Pencil className="size-4" />
                                            </Link>
                                            <button
                                                type="button"
                                                className="rounded-lg p-2 text-red-600 hover:bg-red-50"
                                                aria-label="حذف"
                                                onClick={() => {
                                                    if (confirm('این پست حذف شود؟')) {
                                                        router.delete(`${panel.base_path}/${row.id}`);
                                                    }
                                                }}
                                            >
                                                <Trash2 className="size-4" />
                                            </button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>
        </Layout>
    );
}

type FormProps = {
    panel: PanelMeta;
    categoryOptions: { value: number; label: string }[];
    statusOptions: { value: string; label: string }[];
    post?: {
        id: number;
        title: string;
        slug: string;
        excerpt: string;
        content: string;
        status: string;
        category_ids: number[];
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        canonical: string;
        featured: ExistingFilePayload | null;
    };
};

export function PanelBlogCreate({ panel, categoryOptions, statusOptions }: FormProps) {
    const Layout = usePanelLayout(panel);
    const [featured, setFeatured] = useGalleryUploadState();
    const form = useForm({
        title: '',
        slug: '',
        excerpt: '',
        content: '',
        status: 'draft',
        category_ids: [] as number[],
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => withGalleryPayload(data, featured, { field: 'featured' }));
        form.post(panel.base_path, { forceFormData: true });
    };

    return (
        <Layout title="پست جدید" description="بدون ویرایشگر پیشرفته — متن ساده" contentVariant="plain">
            <Head title="پست جدید" />
            <form onSubmit={submit} className="space-y-6">
                <GalleryImagesField
                    title="تصویر شاخص"
                    description="حداقل یک تصویر الزامی است."
                    items={featured}
                    onItemsChange={setFeatured}
                    multiple={false}
                    maxFiles={1}
                    disabled={form.processing}
                    error={form.errors.featured}
                />
                <PostFormFields
                    data={form.data}
                    errors={form.errors}
                    categoryOptions={categoryOptions}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    plainEditor
                    showSeo={false}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <div className="flex gap-2">
                    <AdminButton type="submit" disabled={form.processing}>
                        ذخیره
                    </AdminButton>
                    <Link href={panel.base_path} className="rounded-lg border px-4 py-2 text-sm">
                        انصراف
                    </Link>
                </div>
            </form>
        </Layout>
    );
}

export function PanelBlogEdit({ panel, categoryOptions, statusOptions, post }: FormProps) {
    const Layout = usePanelLayout(panel);
    if (!post) {
        return null;
    }

    const [featured, setFeatured] = useGalleryUploadState(post.featured ? [post.featured] : []);
    const form = useForm({
        title: post.title,
        slug: post.slug,
        excerpt: post.excerpt,
        content: post.content,
        status: post.status === 'published' ? 'pending_review' : post.status,
        category_ids: post.category_ids,
        meta_title: post.meta_title,
        meta_description: post.meta_description,
        meta_keywords: post.meta_keywords,
        canonical: post.canonical,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => withGalleryPayload(data, featured, { field: 'featured' }));
        form.put(`${panel.base_path}/${post.id}`, { forceFormData: true });
    };

    return (
        <Layout title="ویرایش پست" description="بدون ویرایشگر پیشرفته — متن ساده" contentVariant="plain">
            <Head title="ویرایش پست" />
            {post.status === 'published' ? (
                <p className="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-900">
                    این پست قبلاً منتشر شده؛ با ذخیره دوباره برای تایید ادمین ارسال می‌شود (مگر پیش‌نویس انتخاب کنید).
                </p>
            ) : null}
            <form onSubmit={submit} className="space-y-6">
                <GalleryImagesField
                    title="تصویر شاخص"
                    items={featured}
                    onItemsChange={setFeatured}
                    multiple={false}
                    maxFiles={1}
                    disabled={form.processing}
                    error={form.errors.featured}
                />
                <PostFormFields
                    data={form.data}
                    errors={form.errors}
                    categoryOptions={categoryOptions}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    plainEditor
                    showSeo={false}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <div className="flex gap-2">
                    <AdminButton type="submit" disabled={form.processing}>
                        به‌روزرسانی
                    </AdminButton>
                    <Link href={panel.base_path} className="rounded-lg border px-4 py-2 text-sm">
                        بازگشت
                    </Link>
                </div>
            </form>
        </Layout>
    );
}
