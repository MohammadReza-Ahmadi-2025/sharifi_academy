import { SocialFormFields } from '@/Components/Admin/Social/SocialFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

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
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`/admin/socials/${social.id}`);
                }}
                className="mt-6 space-y-6"
            >
                <SocialFormFields
                    data={form.data}
                    errors={form.errors}
                    typeOptions={typeOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
            </form>
        </AdminLayout>
    );
}
