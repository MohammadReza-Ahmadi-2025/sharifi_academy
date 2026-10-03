import { SessionFormFields } from '@/Components/Admin/Courses/SessionFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';

type Props = {
    course: { id: number };
    courseClass: { id: number; name: string };
    statusOptions: { value: string; label: string }[];
};

export default function SessionsCreate({ course, courseClass, statusOptions }: Props) {
    const form = useForm({
        session_number: 1,
        title: '',
        description: '',
        status: 'scheduled',
        scheduled_start_at: '',
        scheduled_end_at: '',
        timezone: 'Asia/Tehran',
    });
    const base = `/admin/courses/${course.id}/classes/${courseClass.id}/sessions`;

    return (
        <AdminLayout>
            <Head title="جلسه جدید" />
            <PageHeader title="جلسه جدید" backHref={base} />
            <form onSubmit={(e) => { e.preventDefault(); form.post(base); }} className="mt-6 space-y-4">
                <SessionFormFields
                    data={form.data}
                    errors={form.errors}
                    statusOptions={statusOptions}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminButton type="submit">ذخیره</AdminButton>
            </form>
        </AdminLayout>
    );
}
