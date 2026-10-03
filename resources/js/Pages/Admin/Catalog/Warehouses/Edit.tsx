import { WarehouseFormFields } from '@/Components/Admin/Catalog/WarehouseFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = { warehouse: { id: number; name: string; code: string; address: string; phone: string; is_default: boolean; is_active: boolean } };

export default function WarehouseEdit({ warehouse }: Props) {
    const form = useForm({ ...warehouse });
    return (
        <AdminLayout>
            <Head title={`ویرایش ${warehouse.name}`} />
            <PageHeader title={`ویرایش انبار: ${warehouse.name}`} backHref="/admin/catalog/warehouses" />
            <form onSubmit={(e) => { e.preventDefault(); form.put(`/admin/catalog/warehouses/${warehouse.id}`); }} className="mt-6 space-y-6">
                <WarehouseFormFields data={form.data} errors={form.errors} onChange={(k, v) => form.setData(k, v as never)} />
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
