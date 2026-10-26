import { SessionFormFields } from '@/Components/Admin/Courses/SessionFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    routePrefix: string;
    course: { id: number };
    courseClass: { id: number; name: string; online_platform?: string };
    statusOptions: { value: string; label: string }[];
};

export default function SessionsCreate({ routePrefix, course, courseClass, statusOptions }: Props) {
    const form = useForm({
        session_number: 1,
        title: '',
        description: '',
        status: 'scheduled',
        scheduled_start_at: '',
        scheduled_end_at: '',
        timezone: 'Asia/Tehran',
        video_url: '',
    });
    const base = `${routePrefix}/courses/${course.id}/classes/${courseClass.id}/sessions`;
    const showVideoUrl = courseClass.online_platform === 'video_link';

    return (
        <AdminLayout>
            <Head title="جلسه جدید" />
            <PageHeader title="جلسه جدید" backHref={base} />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.post(base); }}>
                <SessionFormFields
                    data={form.data}
                    errors={form.errors}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    showVideoUrl={showVideoUrl}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit">ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
