import { ProductFormFields } from '@/Components/Admin/Catalog/ProductFormFields';
import {
    ProductInitialVariantFields,
} from '@/Components/Admin/Catalog/ProductInitialVariantFields';
import { emptyVariantFormValues } from '@/Components/Admin/Catalog/ProductVariantFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    categoryOptions: { value: number; label: string }[];
    brandOptions: { value: number; label: string }[];
    displayModeOptions: { value: string; label: string }[];
};

export default function ProductsCreate({ categoryOptions, brandOptions, displayModeOptions }: Props) {
    const [gallery, setGallery] = useGalleryUploadState();
    const form = useForm({
        title: '',
        slug: '',
        sku: '',
        short_description: '',
        description: '',
        features: '',
        brand_id: null as number | null,
        model: '',
        status: true,
        is_visible: true,
        category_ids: [] as number[],
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
        variant: { ...emptyVariantFormValues },
    });

    return (
        <AdminLayout>
            <Head title="ایجاد محصول" />
            <PageHeader title="ایجاد محصول" backHref="/admin/catalog/products" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form
                        .transform((data) => withGalleryPayload(data, gallery))
                        .post('/admin/catalog/products', { forceFormData: true });
                }}
                className="mt-6 space-y-6"
            >
                <GalleryImagesField
                    title="گالری تصاویر محصول"
                    items={gallery}
                    onItemsChange={setGallery}
                    disabled={form.processing}
                />
                <ProductFormFields
                    data={form.data}
                    errors={form.errors}
                    categoryOptions={categoryOptions}
                    brandOptions={brandOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <ProductInitialVariantFields
                    data={form.data.variant}
                    errors={form.errors}
                    displayModeOptions={displayModeOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData('variant', { ...form.data.variant, [k]: v })}
                />
                <AdminButton type="submit" disabled={form.processing}>ذخیره محصول</AdminButton>
            </form>
        </AdminLayout>
    );
}
