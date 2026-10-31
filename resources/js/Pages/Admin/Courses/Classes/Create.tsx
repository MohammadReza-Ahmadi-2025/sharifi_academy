import { ClassFormFields } from '@/Components/Admin/Courses/ClassFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    routePrefix: string;
    course: { id: number; title: string };
    statusOptions: { value: string; label: string }[];
    deliveryTypeOptions: { value: string; label: string }[];
    onlinePlatformOptions: { value: string; label: string }[];
    instructorRoleOptions: { value: string; label: string }[];
    userOptions: { value: number; label: string }[];
};

export default function CourseClassesCreate(props: Props) {
    const { course, routePrefix } = props;
    const form = useForm({
        name: '',
        code: '',
        status: 'draft',
        delivery_type: '',
        online_platform: 'adobe_connect',
        capacity: '' as number | '',
        price: 0,
        registration_starts_at: '',
        registration_ends_at: '',
        starts_at: '',
        ends_at: '',
        meeting_weekdays: [] as number[],
        meeting_starts_time: '',
        meeting_ends_time: '',
        location: '',
        description: '',
        is_public: true,
        instructors: [] as { user_id: number; role: string; is_primary: boolean; share_percentage: string }[],
    });

    return (
        <AdminLayout>
            <Head title="ایجاد کلاس" />
            <PageHeader title="ایجاد کلاس" backHref={`${routePrefix}/courses/${course.id}/classes`} />
            <AdminFormPage onSubmit={(e) => { e.preventDefault(); form.post(`${routePrefix}/courses/${course.id}/classes`); }}>
                <ClassFormFields
                    {...props}
                    data={form.data}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
