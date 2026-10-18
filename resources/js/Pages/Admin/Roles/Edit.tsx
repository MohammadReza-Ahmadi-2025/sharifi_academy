import { RolePermissionsPicker, type PermissionGroup } from '@/Components/Admin/Roles/RolePermissionsPicker';
import type { AdminRoleListItem } from '@/Components/Admin/Roles/RoleCard';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Badge } from '@/Components/Admin/shared/ui/badge';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useConfirmToast } from '@/Components/shared/hooks/use-confirm-toast';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    role: AdminRoleListItem;
    permissionGroups: PermissionGroup[];
    selectedPermissions: string[];
    abilities: { update: boolean; delete: boolean };
};

export default function RolesEdit({ role, permissionGroups, selectedPermissions, abilities }: Props) {
    const confirmToast = useConfirmToast();
    const form = useForm({
        permissions: selectedPermissions,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form.put(`/admin/roles/${role.id}`);
    };

    const handleDelete = () => {
        if (!abilities.delete) {
            return;
        }
        confirmToast(`نقش «${role.label}» حذف شود؟`, () => {
        router.delete(`/admin/roles/${role.id}`);
    });;
    };

    return (
        <AdminLayout>
            <Head title={`ویرایش نقش: ${role.label}`} />

            <PageHeader
                title={role.label}
                description={`شناسه: ${role.name}`}
                backHref="/admin/roles"
                actions={
                    <div className="flex items-center gap-2">
                        <Badge variant={role.isSystem ? 'info' : 'secondary'}>
                            {role.isSystem ? 'نقش سیستمی' : 'نقش سفارشی'}
                        </Badge>
                        {abilities.delete ? (
                            <AdminButton type="button" variant="outline" className="text-rose-600" onClick={handleDelete}>
                                حذف نقش
                            </AdminButton>
                        ) : null}
                    </div>
                }
            />

            <AdminFormPage onSubmit={submit}>
                <Card className="w-full p-6 text-sm text-slate-600 dark:text-slate-300">
                    <p>
                        کاربران این نقش: <strong>{role.usersCount.toLocaleString('fa-IR')}</strong>
                    </p>
                    {role.name === 'super-admin' ? (
                        <p className="mt-2 text-xs text-amber-700 dark:text-amber-400">
                            فقط مدیر ارشد می‌تواند دسترسی‌های این نقش را تغییر دهد.
                        </p>
                    ) : null}
                </Card>

                <RolePermissionsPicker
                    permissionGroups={permissionGroups}
                    selected={form.data.permissions}
                    onChange={(permissions) => form.setData('permissions', permissions)}
                    error={form.errors.permissions}
                    disabled={form.processing || !abilities.update}
                />

                {abilities.update ? (
                    <AdminFormActions>
                        <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
                        <AdminButton type="button" variant="outline" href="/admin/roles">بازگشت</AdminButton>
                    </AdminFormActions>
                ) : null}
            </AdminFormPage>
        </AdminLayout>
    );
}
