import { MenuFormFields } from '@/Components/Admin/Menu/MenuFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    locationOptions: { value: string; label: string }[];
    itemTypeOptions: { value: string; label: string }[];
};

export default function MenusCreate({ locationOptions, itemTypeOptions }: Props) {
    const form = useForm({
        title: '',
        url: '',
        icon: '',
        location: locationOptions[0]?.value ?? 'footer',
        sort: 0,
        status: true,
        items: [] as import('@/Components/Admin/Menu/MenuFormFields').MenuItemFormData[],
    });

    return (
        <AdminLayout>
            <Head title="ایجاد منو" />
            <PageHeader title="ایجاد منو" backHref="/admin/menus" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/menus');
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
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
