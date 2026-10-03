import { ProductFormFields } from '@/Components/Admin/Catalog/ProductFormFields';
import { ProductVariantPanel, VariantRow } from '@/Components/Admin/Catalog/ProductVariantPanel';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import { Head, router, useForm } from '@inertiajs/react';

type Props = {
    product: {
        id: number;
        title: string;
        slug: string;
        sku: string | null;
        short_description: string | null;
        description: string | null;
        brand_id: number | null;
        model: string | null;
        status: boolean;
        is_visible: boolean;
        sort: number;
        category_ids: number[];
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        canonical: string;
        features: string;
        gallery: ExistingFilePayload[];
    };
    variants: VariantRow[];
    categoryOptions: { value: number; label: string }[];
    brandOptions: { value: number; label: string }[];
    displayModeOptions: { value: string; label: string }[];
    abilities: { variants: boolean; delete: boolean };
};

export default function ProductsEdit({
    product,
    variants,
    categoryOptions,
    brandOptions,
    displayModeOptions,
    abilities,
}: Props) {
    const [gallery, setGallery] = useGalleryUploadState(product.gallery ?? []);
    const form = useForm({
        title: product.title,
        slug: product.slug,
        sku: product.sku ?? '',
        short_description: product.short_description ?? '',
        description: product.description ?? '',
        brand_id: product.brand_id,
        model: product.model ?? '',
        status: product.status,
        is_visible: product.is_visible,
        category_ids: product.category_ids,
        meta_title: product.meta_title,
        meta_description: product.meta_description,
        meta_keywords: product.meta_keywords,
        canonical: product.canonical,
        features: product.features,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش ${product.title}`} />
            <PageHeader
                title="ویرایش محصول"
                backHref="/admin/catalog/products"
                actions={
                    abilities.delete ? (
                        <AdminButton
                            variant="ghost"
                            onClick={() =>
                                window.confirm('محصول حذف شود؟') &&
                                router.delete(`/admin/catalog/products/${product.id}`)
                            }
                        >
                            حذف محصول
                        </AdminButton>
                    ) : null
                }
            />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form
                        .transform((data) => withGalleryPayload(data, gallery))
                        .put(`/admin/catalog/products/${product.id}`, { forceFormData: true });
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
                <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی محصول</AdminButton>
            </form>

            <ProductVariantPanel
                productId={product.id}
                variants={variants}
                displayModeOptions={displayModeOptions}
                canManage={abilities.variants}
            />
        </AdminLayout>
    );
}
