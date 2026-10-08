import { CategoryFormFields } from '@/Components/Admin/Catalog/CategoryFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    categoryType: string;
    categoryTypeLabel: string;
    parentOptions: { value: number; label: string }[];
};

export default function CategoriesCreate({ categoryType, categoryTypeLabel, parentOptions }: Props) {
    const [image, setImage] = useGalleryUploadState();
    const form = useForm({
        type: categoryType,
        parent_id: null as number | null,
        title: '',
        slug: '',
        description: '',
        status: true,
        show_in_menu: false,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    const indexUrl = `/admin/categories?type=${categoryType}`;

    return (
        <AdminLayout>
            <Head title={`ایجاد دسته ${categoryTypeLabel}`} />
            <PageHeader title={`ایجاد دسته ${categoryTypeLabel}`} backHref={indexUrl} />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(data, image, { field: 'image' }));
                    form.post(`/admin/categories?type=${categoryType}`, { forceFormData: true });
                }}>
                <GalleryImagesField
                    title="تصویر دسته"
                    description="فقط یک تصویر برای نمایش در فروشگاه."
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

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
