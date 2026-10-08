import { RolePermissionsPicker, type PermissionGroup } from '@/Components/Admin/Roles/RolePermissionsPicker';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { FormField } from '@/Components/Admin/shared/ui/form-field';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    permissionGroups: PermissionGroup[];
};

export default function RolesCreate({ permissionGroups }: Props) {
    const form = useForm({
        name: '',
        permissions: [] as string[],
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.post('/admin/roles');
    };

    return (
        <AdminLayout>
            <Head title="ایجاد نقش" />

            <PageHeader
                title="ایجاد نقش"
                description="نام نقش را به انگلیسی (slug) وارد کنید و دسترسی‌ها را انتخاب کنید."
                backHref="/admin/roles"
            />

            <AdminFormPage onSubmit={submit}>
                <Card className="w-full space-y-4 p-6">
                    <FormField label="نام نقش (slug)" htmlFor="name" required error={form.errors.name}>
                        <AdminInput
                            id="name"
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            placeholder="مثال: content-editor"
                            dir="ltr"
                            className="text-left"
                            disabled={form.processing}
                        />
                    </FormField>
                    <p className="text-xs text-slate-500 dark:text-slate-400">
                        فقط حروف کوچک لاتین، عدد و خط تیره. عنوان فارسی از تنظیمات یا خود نام نمایش داده می‌شود.
                    </p>
                </Card>

                <RolePermissionsPicker
                    permissionGroups={permissionGroups}
                    selected={form.data.permissions}
                    onChange={(permissions) => form.setData('permissions', permissions)}
                    error={form.errors.permissions}
                    disabled={form.processing}
                />

                <AdminFormActions>

                        <AdminButton type="submit" disabled={form.processing}>ذخیره نقش</AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/roles">انصراف</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
