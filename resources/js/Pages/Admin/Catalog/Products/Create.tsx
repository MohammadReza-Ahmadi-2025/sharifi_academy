import { ProductFormFields } from '@/Components/Admin/Catalog/ProductFormFields';
import {
    ProductVariantsEditor,
    variantsPayloadForForm,
} from '@/Components/Admin/Catalog/ProductVariantsEditor';
import { emptySimpleVariantRow, type VariantEditorRow } from '@/Components/Admin/Catalog/product-variants-logic';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { CatalogVariantAttribute } from '@/Components/Admin/Catalog/variant-attributes';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { useState } from 'react';

type Props = {
    categoryOptions: { value: number; label: string }[];
    brandOptions: { value: number; label: string }[];
    displayModeOptions: { value: string; label: string }[];
    variantAttributes: CatalogVariantAttribute[];
};

export default function ProductsCreate({
    categoryOptions,
    brandOptions,
    displayModeOptions,
    variantAttributes,
}: Props) {
    const [gallery, setGallery] = useGalleryUploadState();
    const [variantRows, setVariantRows] = useState<VariantEditorRow[]>([emptySimpleVariantRow()]);

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
        variants: variantsPayloadForForm([emptySimpleVariantRow()]),
    });

    const syncVariantsToForm = (rows: VariantEditorRow[]) => {
        setVariantRows(rows);
        form.setData('variants', variantsPayloadForForm(rows));
    };

    return (
        <AdminLayout>
            <Head title="ایجاد محصول" />
            <PageHeader title="ایجاد محصول" backHref="/admin/catalog/products" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => ({
                        ...withGalleryPayload(data, gallery),
                        variants: variantsPayloadForForm(variantRows),
                    }));
                    form.post('/admin/catalog/products', { forceFormData: true });
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
                <ProductVariantsEditor
                    mode="create"
                    variantAttributes={variantAttributes}
                    displayModeOptions={displayModeOptions}
                    rows={variantRows}
                    onChange={syncVariantsToForm}
                    disabled={form.processing}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره محصول</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
