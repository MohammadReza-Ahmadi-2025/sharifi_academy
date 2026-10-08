import { WarehouseFormFields } from '@/Components/Admin/Catalog/WarehouseFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

export default function WarehouseCreate() {
    const form = useForm({ name: '', code: '', address: '', phone: '', is_default: false, is_active: true });
    return (
        <AdminLayout>
            <Head title="ایجاد انبار" />
            <PageHeader title="ایجاد انبار" backHref="/admin/catalog/warehouses" />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.post('/admin/catalog/warehouses'); }}>
                <WarehouseFormFields data={form.data} errors={form.errors} onChange={(k, v) => form.setData(k, v as never)} />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
