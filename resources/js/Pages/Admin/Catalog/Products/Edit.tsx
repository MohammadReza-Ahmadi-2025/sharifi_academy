import { ProductFormFields } from '@/Components/Admin/Catalog/ProductFormFields';
import {
    ProductVariantsEditor,
    variantsPayloadForForm,
} from '@/Components/Admin/Catalog/ProductVariantsEditor';
import { rowsFromServerVariants, type VariantEditorRow } from '@/Components/Admin/Catalog/product-variants-logic';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { Head, router, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { useMemo, useState } from 'react';

type VariantRow = {
    id: number;
    sku: string | null;
    price: number | null;
    final_price: number | null;
    discount_type?: string | null;
    discount_value?: number | null;
    stock: number;
    min_stock?: number;
    display_mode: string;
    attributes?: Record<string, string>;
    attribute_value_ids?: number[];
    attributes_text?: string;
    title?: string | null;
};

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
    variantAttributes: CatalogVariantAttribute[];
    abilities: { variants: boolean; delete: boolean };
};

export default function ProductsEdit({
    product,
    variants,
    categoryOptions,
    brandOptions,
    displayModeOptions,
    variantAttributes,
    abilities,
}: Props) {
    const [gallery, setGallery] = useGalleryUploadState(product.gallery ?? []);
    const initialVariantRows = useMemo(
        () => rowsFromServerVariants(variants, variantAttributes),
        [variants, variantAttributes],
    );
    const [variantRows, setVariantRows] = useState<VariantEditorRow[]>(initialVariantRows);

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
        variants: variantsPayloadForForm(initialVariantRows),
    });

    const syncVariantsToForm = (rows: VariantEditorRow[]) => {
        setVariantRows(rows);
        form.setData('variants', variantsPayloadForForm(rows));
    };

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
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => ({
                        ...withGalleryPayload(data, gallery),
                        variants: variantsPayloadForForm(variantRows),
                    }));
                    form.put(`/admin/catalog/products/${product.id}`, { forceFormData: true });
                }}
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
                {abilities.variants && (
                    <ProductVariantsEditor
                        mode="edit"
                        variantAttributes={variantAttributes}
                        displayModeOptions={displayModeOptions}
                        rows={variantRows}
                        onChange={syncVariantsToForm}
                        disabled={form.processing}
                    />
                )}
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی محصول</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
