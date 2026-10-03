import { BrandFormFields } from '@/Components/Admin/Catalog/BrandFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function BrandCreate() {
    const form = useForm({
        title: '',
        slug: '',
        description: '',
        website: '',
        status: true,
        sort: 0,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    return (
        <AdminLayout>
            <Head title="ایجاد برند" />
            <PageHeader title="ایجاد برند" backHref="/admin/catalog/brands" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/catalog/brands');
                }}
                className="mt-6 space-y-6"
            >
                <BrandFormFields
                    data={form.data}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
