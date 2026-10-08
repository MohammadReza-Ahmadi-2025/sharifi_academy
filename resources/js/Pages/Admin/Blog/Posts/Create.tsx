import { PostFormFields } from '@/Components/Admin/Blog/PostFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    categoryOptions: { value: number; label: string }[];
    statusOptions: { value: string; label: string }[];
};

export default function BlogPostsCreate({ categoryOptions, statusOptions }: Props) {
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

    return (
        <AdminLayout>
            <Head title="ایجاد پست" />
            <PageHeader title="ایجاد پست" backHref="/admin/posts" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(data, featured, { field: 'featured' }));
                    form.post('/admin/posts', { forceFormData: true });
                }}>
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
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره پست</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
