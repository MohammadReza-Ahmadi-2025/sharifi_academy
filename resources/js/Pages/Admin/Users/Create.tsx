import { UserAvatarField } from '@/Components/Admin/Users/UserAvatarField';
import { UserFormFields } from '@/Components/Admin/Users/UserFormFields';
import { withAvatarPayload } from '@/Components/Admin/Users/user-form-submit';
import type { RoleOption } from '@/Components/Admin/Users/UserRolesPicker';
import { useFileUploadState } from '@/Components/shared/FileUploader';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    roleOptions: RoleOption[];
};

export default function UsersCreate({ roleOptions }: Props) {
    const [avatarFiles, setAvatarFiles] = useFileUploadState();

    const form = useForm({
        name: '',
        family: '',
        mobile: '',
        email: '',
        gender: '',
        status: 1,
        password: '',
        roles: [] as string[],
    });

    const submit = (event: React.FormEvent) => {
        event.preventDefault();
        form
            .transform((data) => withAvatarPayload(data, avatarFiles))
            .post('/admin/users', { forceFormData: true });
    };

    return (
        <AdminLayout>
            <Head title="ایجاد کاربر" />

            <PageHeader title="ایجاد کاربر" description="حساب جدید با نقش و وضعیت مشخص" backHref="/admin/users" />

            <form onSubmit={submit} className="mt-6 space-y-6">
                <UserAvatarField
                    files={avatarFiles}
                    onFilesChange={setAvatarFiles}
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
                    passwordRequired
                />

                <div className="flex gap-3">
                    <AdminButton type="submit" disabled={form.processing}>ذخیره کاربر</AdminButton>
                    <AdminButton type="button" variant="outline" href="/admin/users">انصراف</AdminButton>
                </div>
            </form>
        </AdminLayout>
    );
}
