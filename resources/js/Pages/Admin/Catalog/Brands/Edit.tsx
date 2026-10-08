import { BrandFormFields } from '@/Components/Admin/Catalog/BrandFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    brand: {
        id: number;
        title: string;
        slug: string;
        description: string;
        website: string;
        status: boolean;
        sort: number;
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        canonical: string;
    };
};

export default function BrandEdit({ brand }: Props) {
    const form = useForm({
        title: brand.title,
        slug: brand.slug,
        description: brand.description,
        website: brand.website,
        status: brand.status,
        meta_title: brand.meta_title,
        meta_description: brand.meta_description,
        meta_keywords: brand.meta_keywords,
        canonical: brand.canonical,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش ${brand.title}`} />
            <PageHeader title={`ویرایش برند: ${brand.title}`} backHref="/admin/catalog/brands" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/catalog/brands/${brand.id}`);
                }}>
                <BrandFormFields
                    data={form.data}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
