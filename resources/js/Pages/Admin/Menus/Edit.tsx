import { MenuFormFields, type MenuItemFormData } from '@/Components/Admin/Menu/MenuFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

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
};

export default function MenusEdit({ menu, locationOptions, itemTypeOptions }: Props) {
    const form = useForm({
        title: menu.title,
        url: menu.url,
        icon: menu.icon,
        location: menu.location,
        sort: menu.sort,
        status: menu.status,
        items: menu.items,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش منو" />
            <PageHeader title={menu.title} backHref="/admin/menus" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/menus/${menu.id}`);
                }}
                className="mt-6 space-y-6"
            >
                <MenuFormFields
                    data={form.data}
                    errors={form.errors}
                    locationOptions={locationOptions}
                    itemTypeOptions={itemTypeOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                    onItemsChange={(items) => form.setData('items', items)}
                />
                <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
            </form>
        </AdminLayout>
    );
}
