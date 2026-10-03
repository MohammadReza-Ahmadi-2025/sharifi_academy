import { SettingFormFields } from '@/Components/Admin/Settings/SettingFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { Card } from '@/Components/Admin/shared/ui/card';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

type Props = {
    setting: {
        id: number;
        key: string;
        label: string;
        group: string;
        group_label: string;
        type: string;
        autoload: boolean;
        value: string | boolean | null;
        image_url: string | null;
    };
};

export default function SettingsEdit({ setting }: Props) {
    const [imageFile, setImageFile] = useState<File | null>(null);
    const form = useForm({
        value: setting.value as string | boolean,
    });

    const backHref = `/admin/settings?group=${setting.group}`;

    return (
        <AdminLayout>
            <Head title={`ویرایش: ${setting.label}`} />
            <PageHeader title={setting.label} description={setting.group_label} backHref={backHref} />
            <Card className="mt-6 max-w-2xl space-y-4 p-6">
                <div className="text-sm text-slate-500">
                    کلید: <span className="font-mono" dir="ltr">{setting.key}</span>
                </div>
                <form
                    onSubmit={(e) => {
                        e.preventDefault();
                        const payload = new FormData();
                        payload.append('_method', 'PUT');

                        if (setting.type === 'switch') {
                            payload.append('value', form.data.value ? '1' : '0');
                        } else if (setting.type === 'image') {
                            if (imageFile) {
                                payload.append('image', imageFile);
                            }
                        } else {
                            payload.append('value', String(form.data.value ?? ''));
                        }

                        router.post(`/admin/settings/${setting.id}`, payload, {
                            forceFormData: true,
                            preserveScroll: true,
                            onStart: () => form.clearErrors(),
                        });
                    }}
                    className="space-y-6"
                >
                    <SettingFormFields
                        type={setting.type}
                        data={form.data}
                        errors={form.errors}
                        imageUrl={setting.image_url}
                        processing={form.processing}
                        onChange={(key, value) => form.setData(key, value)}
                        onImageChange={setImageFile}
                    />
                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </form>
            </Card>
        </AdminLayout>
    );
}
