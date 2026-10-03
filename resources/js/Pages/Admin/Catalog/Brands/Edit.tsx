import { BrandFormFields } from '@/Components/Admin/Catalog/BrandFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

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
        sort: brand.sort,
        meta_title: brand.meta_title,
        meta_description: brand.meta_description,
        meta_keywords: brand.meta_keywords,
        canonical: brand.canonical,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش ${brand.title}`} />
            <PageHeader title={`ویرایش برند: ${brand.title}`} backHref="/admin/catalog/brands" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/catalog/brands/${brand.id}`);
                }}
                className="mt-6 space-y-6"
            >
                <BrandFormFields
                    data={form.data}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
            </form>
        </AdminLayout>
    );
}
