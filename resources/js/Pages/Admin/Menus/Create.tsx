import { MenuFormFields } from '@/Components/Admin/Menu/MenuFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    locationOptions: { value: string; label: string }[];
    itemTypeOptions: { value: string; label: string }[];
    pageOptions: { value: number; label: string }[];
};

export default function MenusCreate({ locationOptions, itemTypeOptions, pageOptions }: Props) {
    const form = useForm({
        title: '',
        url: '',
        icon: '',
        location: locationOptions[0]?.value ?? 'footer',
        status: true,
        items: [] as import('@/Components/Admin/Menu/MenuFormFields').MenuItemFormData[],
    });

    return (
        <AdminLayout>
            <Head title="ایجاد منو" />
            <PageHeader title="ایجاد منو" backHref="/admin/menus" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/menus');
                }}>
                <MenuFormFields
                    data={form.data}
                    errors={form.errors}
                    locationOptions={locationOptions}
                    itemTypeOptions={itemTypeOptions}
                    pageOptions={pageOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                    onItemsChange={(items) => form.setData('items', items)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
