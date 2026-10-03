import { SocialFormFields } from '@/Components/Admin/Social/SocialFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    typeOptions: { value: string; label: string }[];
};

export default function SocialCreate({ typeOptions }: Props) {
    const form = useForm({
        type: typeOptions[0]?.value ?? 'instagram',
        value: '',
        label: '',
        target_blank: true,
        is_active: true,
    });

    return (
        <AdminLayout>
            <Head title="ایجاد شبکه اجتماعی" />
            <PageHeader title="ایجاد آیتم شبکه اجتماعی" backHref="/admin/socials" />
            <form
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/socials');
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
                <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
