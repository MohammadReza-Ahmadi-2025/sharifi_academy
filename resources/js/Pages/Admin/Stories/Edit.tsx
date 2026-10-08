import { StoryFormFields, type StoryFormData } from '@/Components/Admin/Stories/StoryFormFields';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';
import { AdminFormErrors } from '@/Components/Admin/shared/ui/admin-form-errors';
import { useToast } from '@/Components/shared/contexts/toast-context';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type StoryPayload = {
    id: number;
    title: string;
    type: string;
    content: string | null;
    background_color: string | null;
    visibility: string;
    status: string;
    status_label: string;
    file_url: string | null;
    thumbnail_url: string | null;
    display_starts_at: string;
    display_ends_at: string;
    published_at: string | null;
    expires_at: string | null;
};

type Props = {
    story: StoryPayload;
    typeOptions: { value: string; label: string }[];
    visibilityOptions: { value: string; label: string }[];
    mediaLimits: { max_video_mb: number; max_image_mb: number };
};

export default function StoriesEdit({ story, typeOptions, visibilityOptions, mediaLimits }: Props) {
    const toast = useToast();

    const form = useForm<StoryFormData>({
        title: story.title,
        type: story.type,
        content: story.content ?? '',
        background_color: story.background_color ?? '#0f766e',
        visibility: story.visibility,
        display_starts_at: story.display_starts_at,
        display_ends_at: story.display_ends_at,
        publish_now: story.status === 'published',
        media: null,
        thumbnail: null,
    });

    return (
        <AdminLayout>
            <Head title="ویرایش استوری" />
            <PageHeader title="ویرایش استوری" backHref="/admin/stories" />
            <AdminFormPage
                onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => {
                        const payload = {
                            ...data,
                            display_ends_at: data.display_ends_at || '',
                        };

                        if (data.media === null && data.thumbnail === null) {
                            const { media: _m, thumbnail: _t, ...rest } = payload;

                            return rest;
                        }

                        if (data.media === null) {
                            const { media: _m, ...rest } = payload;

                            return rest;
                        }

                        if (data.thumbnail === null) {
                            const { thumbnail: _t, ...rest } = payload;

                            return rest;
                        }

                        return payload;
                    });
                    form.put(`/admin/stories/${story.id}`, {
                        forceFormData: true,
                        preserveScroll: true,
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
                    existingFileUrl={story.file_url}
                    existingThumbnailUrl={story.thumbnail_url}
                    statusLabel={story.status_label}
                    publishedAt={story.published_at}
                    expiresAt={story.expires_at}
                    onChange={(key, value) => form.setData(key, value as never)}
                />
                <AdminFormActions>
                    <AdminButton type="submit" disabled={form.processing}>ذخیره تغییرات</AdminButton>
                </AdminFormActions>
            </AdminFormPage>
        </AdminLayout>
    );
}
