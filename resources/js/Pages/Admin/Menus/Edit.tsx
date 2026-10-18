import { MenuFormFields, type MenuItemFormData } from '@/Components/Admin/Menu/MenuFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    menu: {
        id: number;
        title: string;
        url: string;
        icon: string;
        location: string;
        sort: number;
        status: boolean;
        items: MenuItemFormData[];
    };
    locationOptions: { value: string; label: string }[];
    itemTypeOptions: { value: string; label: string }[];
    pageOptions: { value: number; label: string }[];
};

export default function MenusEdit({ menu, locationOptions, itemTypeOptions, pageOptions }: Props) {
    const form = useForm({
        title: menu.title,
        url: menu.url,
        icon: menu.icon,
        location: menu.location,
        status: menu.status,
        items: menu.items,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش منو" />
            <PageHeader title={menu.title} backHref="/admin/menus" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/menus/${menu.id}`);
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

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
