import { CategoryFormFields } from '@/Components/Admin/Catalog/CategoryFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    category: {
        id: number;
        parent_id: number | null;
        title: string;
        slug: string;
        description: string | null;
        sort: number;
        status: boolean;
        show_in_menu: boolean;
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        canonical: string;
        image: ExistingFilePayload | null;
    };
    categoryType: string;
    categoryTypeLabel: string;
    parentOptions: { value: number; label: string }[];
};

export default function CategoriesEdit({ category, categoryType, categoryTypeLabel, parentOptions }: Props) {
    const [image, setImage] = useGalleryUploadState(category.image ? [category.image] : []);
    const indexUrl = `/admin/categories?type=${categoryType}`;
    const form = useForm({
        parent_id: category.parent_id,
        title: category.title,
        slug: category.slug,
        description: category.description ?? '',
        status: category.status,
        show_in_menu: category.show_in_menu,
        meta_title: category.meta_title,
        meta_description: category.meta_description,
        meta_keywords: category.meta_keywords,
        canonical: category.canonical,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش دسته ${categoryTypeLabel}`} />
            <PageHeader title={`ویرایش دسته ${categoryTypeLabel}`} backHref={indexUrl} />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(data, image, { field: 'image' }));
                    form.put(`/admin/categories/${category.id}`, { forceFormData: true });
                }}>
                <GalleryImagesField
                    title="تصویر دسته"
                    items={image}
                    onItemsChange={setImage}
                    multiple={false}
                    maxFiles={1}
                    disabled={form.processing}
                />
                <CategoryFormFields
                    data={form.data}
                    errors={form.errors}
                    parentOptions={parentOptions}
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
