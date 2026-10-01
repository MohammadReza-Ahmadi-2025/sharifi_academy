import { UserAvatarField } from '@/Components/Admin/Users/UserAvatarField';
import { UserFormFields } from '@/Components/Admin/Users/UserFormFields';
import { withAvatarPayload } from '@/Components/Admin/Users/user-form-submit';
import type { RoleOption } from '@/Components/Admin/Users/UserRolesPicker';
import type { AdminUserListItem } from '@/Pages/Admin/Users/Index';
import { useFileUploadState } from '@/Components/shared/FileUploader';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    user: AdminUserListItem;
    roleOptions: RoleOption[];
    selectedRoles: string[];
    abilities: { update: boolean; delete: boolean };
};

export default function UsersEdit({ user, roleOptions, selectedRoles, abilities }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };
    const [avatarFiles, setAvatarFiles] = useFileUploadState();
    const [removeAvatar, setRemoveAvatar] = useState(false);

    const form = useForm({
        name: user.name ?? '',
        family: user.family ?? '',
        mobile: user.mobile,
        email: user.email ?? '',
        gender: user.gender ?? '',
        status: user.status,
        password: '',
        roles: selectedRoles,
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form
            .transform((data) => withAvatarPayload(data, avatarFiles, { removeAvatar }))
            .put(`/admin/users/${user.id}`, { preserveScroll: true, forceFormData: true });
    };

    const handleDelete = () => {
        if (!window.confirm(`کاربر «${user.fullName}» حذف شود؟`)) {
            return;
        }

        router.delete(`/admin/users/${user.id}`);
    };

    return (
        <AdminLayout>
            <Head title={`ویرایش ${user.fullName}`} />

            <PageHeader
                title="ویرایش کاربر"
                description={user.fullName}
                backHref="/admin/users"
            />

            {flash?.success ? (
                <p className="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-300">
                    {flash.success}
                </p>
            ) : null}

            <form onSubmit={submit} className="mt-6 space-y-6">
                <UserAvatarField
                    files={avatarFiles}
                    onFilesChange={setAvatarFiles}
                    existing={user.avatar}
                    removeExisting={removeAvatar}
                    onRemoveExistingChange={setRemoveAvatar}
                    disabled={form.processing}
                    error={form.errors.avatar}
                    showAlt
                />

                <UserFormFields
                    data={form.data}
                    errors={form.errors}
                    roleOptions={roleOptions}
                    processing={form.processing}
                    onChange={(key, value) => form.setData(key, value)}
                    passwordRequired={false}
                />

                <div className="flex flex-wrap gap-3">
                    <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/users">بازگشت</AdminButton>
                    {abilities.delete ? (
                        <AdminButton type="button" variant="outline" onClick={handleDelete} className="text-rose-600">
                            حذف کاربر
                        </AdminButton>
                    ) : null}
                </div>
            </form>
        </AdminLayout>
    );
}
