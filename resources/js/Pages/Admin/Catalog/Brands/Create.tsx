import { BrandFormFields } from '@/Components/Admin/Catalog/BrandFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

export default function BrandCreate() {
    const form = useForm({
        title: '',
        slug: '',
        description: '',
        website: '',
        status: true,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    return (
        <AdminLayout>
            <Head title="ایجاد برند" />
            <PageHeader title="ایجاد برند" backHref="/admin/catalog/brands" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/catalog/brands');
                }}>
                <BrandFormFields
                    data={form.data}
                    errors={form.errors}
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
