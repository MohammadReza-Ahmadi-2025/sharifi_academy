import { CategoryFormFields } from '@/Components/Admin/Catalog/CategoryFormFields';
import type { CategoryTypeOption } from '@/Components/Admin/Catalog/CategoryTypeFilter';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { Head, router, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    categoryType: string;
    categoryTypeLabel: string;
    creatableTypeOptions: CategoryTypeOption[];
    parentOptions: { value: number; label: string }[];
};

export default function CategoriesCreate({
    categoryType,
    categoryTypeLabel,
    creatableTypeOptions,
    parentOptions,
}: Props) {
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

    const handleTypeChange = (nextType: string) => {
        if (nextType === form.data.type) {
            return;
        }

        router.get('/admin/categories/create', { type: nextType }, { preserveState: false });
    };

    return (
        <AdminLayout>
            <Head title="ایجاد دسته‌بندی" />
            <PageHeader title="ایجاد دسته‌بندی" description={`نوع: ${categoryTypeLabel}`} backHref={indexUrl} />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(data, image, { field: 'image' }));
                    form.post('/admin/categories', { forceFormData: true });
                }}
            >
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
                    typeOptions={creatableTypeOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                    onTypeChange={handleTypeChange}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
