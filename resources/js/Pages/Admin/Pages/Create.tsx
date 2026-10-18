import { PageFormFields } from '@/Components/Admin/Pages/PageFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

export default function PagesCreate() {
    const form = useForm({
        title: '',
        slug: '',
        content: '',
        is_active: true,
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    return (
        <AdminLayout>
            <Head title="ایجاد صفحه" />
            <PageHeader title="ایجاد صفحه ثابت" backHref="/admin/pages" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/pages');
                }}
            >
                <PageFormFields
                    data={form.data}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره صفحه</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
