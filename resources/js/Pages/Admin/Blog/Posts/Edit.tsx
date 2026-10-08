import { PostFormFields } from '@/Components/Admin/Blog/PostFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    post: {
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
    categoryOptions: { value: number; label: string }[];
    statusOptions: { value: string; label: string }[];
};

export default function BlogPostsEdit({ post, categoryOptions, statusOptions }: Props) {
    const [featured, setFeatured] = useGalleryUploadState(post.featured ? [post.featured] : []);
    const form = useForm({
        title: post.title,
        slug: post.slug,
        excerpt: post.excerpt,
        content: post.content,
        status: post.status,
        category_ids: post.category_ids,
        meta_title: post.meta_title,
        meta_description: post.meta_description,
        meta_keywords: post.meta_keywords,
        canonical: post.canonical,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش پست" />
            <PageHeader title="ویرایش پست" backHref="/admin/posts" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(data, featured, { field: 'featured' }));
                    form.put(`/admin/posts/${post.id}`, { forceFormData: true });
                }}>
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
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
