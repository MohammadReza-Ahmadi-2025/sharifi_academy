import { PageFormFields, type PageFormData } from '@/Components/Admin/Pages/PageFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    page: PageFormData & { id: number };
};

export default function PagesEdit({ page }: Props) {
    const form = useForm({
        title: page.title,
        slug: page.slug,
        content: page.content,
        is_active: page.is_active,
        meta_title: page.meta_title,
        meta_description: page.meta_description,
        meta_keywords: page.meta_keywords,
        canonical: page.canonical,
    });

    return (
        <AdminLayout>
            <Head title={`ویرایش: ${page.title}`} />
            <PageHeader title={`ویرایش: ${page.title}`} backHref="/admin/pages" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/pages/${page.slug}`);
                }}
            >
                <PageFormFields
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
