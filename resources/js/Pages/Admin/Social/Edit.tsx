import { SocialFormFields } from '@/Components/Admin/Social/SocialFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    social: {
        id: number;
        type: string;
        value: string;
        label: string;
        target_blank: boolean;
        is_active: boolean;
    };
    typeOptions: { value: string; label: string }[];
};

export default function SocialEdit({ social, typeOptions }: Props) {
    const form = useForm({
        type: social.type,
        value: social.value,
        label: social.label,
        target_blank: social.target_blank,
        is_active: social.is_active,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش شبکه اجتماعی" />
            <PageHeader title="ویرایش آیتم" backHref="/admin/socials" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/socials/${social.id}`);
                }}>
                <SocialFormFields
                    data={form.data}
                    errors={form.errors}
                    typeOptions={typeOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
