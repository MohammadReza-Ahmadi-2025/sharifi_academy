import { StoryFormFields, type StoryFormData } from '@/Components/Admin/Stories/StoryFormFields';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { AdminFormErrors } from '@/Components/Admin/shared/ui/admin-form-errors';
import { useToast } from '@/Components/shared/contexts/toast-context';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    typeOptions: { value: string; label: string }[];
    visibilityOptions: { value: string; label: string }[];
    mediaLimits: { max_video_mb: number; max_image_mb: number };
    defaultDisplayStartsAt: string;
};

export default function StoriesCreate({
    typeOptions,
    visibilityOptions,
    mediaLimits,
    defaultDisplayStartsAt,
}: Props) {
    const toast = useToast();

    const form = useForm<StoryFormData>({
        title: '',
        type: typeOptions[0]?.value ?? 'text',
        content: '',
        background_color: '#0f766e',
        visibility: visibilityOptions[0]?.value ?? 'public',
        display_starts_at: defaultDisplayStartsAt,
        display_ends_at: '',
        publish_now: true,
        media: null,
        thumbnail: null,
    });

    return (
        <AdminLayout>
            <Head title="ایجاد استوری" />
            <PageHeader title="ایجاد استوری" backHref="/admin/stories" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/admin/stories', {
                        forceFormData: true,
                        onError: (errors) => {
                            const first = Object.values(errors).find((m) => typeof m === 'string' && m.length > 0);
                            if (first) {
                                toast.error(first);
                            }
                        },
                    });
                }}
            >
                <AdminFormErrors errors={form.errors} />
                <StoryFormFields
                    data={form.data}
                    errors={form.errors}
                    typeOptions={typeOptions}
                    visibilityOptions={visibilityOptions}
                    mediaLimits={mediaLimits}
                    processing={form.processing}
                    onChange={(key, value) => form.setData(key, value as never)}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره استوری</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
