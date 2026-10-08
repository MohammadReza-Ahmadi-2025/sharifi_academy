import { WarehouseFormFields } from '@/Components/Admin/Catalog/WarehouseFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = { warehouse: { id: number; name: string; code: string; address: string; phone: string; is_default: boolean; is_active: boolean } };

export default function WarehouseEdit({ warehouse }: Props) {
    const form = useForm({ ...warehouse });
    return (
        <AdminLayout>
            <Head title={`ویرایش ${warehouse.name}`} />
            <PageHeader title={`ویرایش انبار: ${warehouse.name}`} backHref="/admin/catalog/warehouses" />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.put(`/admin/catalog/warehouses/${warehouse.id}`); }}>
                <WarehouseFormFields data={form.data} errors={form.errors} onChange={(k, v) => form.setData(k, v as never)} />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
