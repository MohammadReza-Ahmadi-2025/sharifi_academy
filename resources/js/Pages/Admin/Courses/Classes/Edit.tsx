import { ClassAdobeRoomPanel } from '@/Components/Admin/Courses/ClassAdobeRoomPanel';
import { ClassEnrollmentPanel } from '@/Components/Admin/Courses/ClassEnrollmentPanel';
import { ClassFormFields } from '@/Components/Admin/Courses/ClassFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    routePrefix: string;
    course: { id: number; title: string };
    courseClass: Record<string, unknown>;
    enrollments: { id: number; user_label: string; status: string; status_label: string; price_amount: number }[];
    adobeRooms: { id: number; sco_id: string; url_path: string; meeting_url?: string | null; status_label: string }[];
    statusOptions: { value: string; label: string }[];
    deliveryTypeOptions: { value: string; label: string }[];
    onlinePlatformOptions: { value: string; label: string }[];
    instructorRoleOptions: { value: string; label: string }[];
    userOptions: { value: number; label: string }[];
    enrollmentUserOptions: { value: number; label: string }[];
    enrollmentStatusOptions: { value: string; label: string }[];
    adobeAccessOptions: { value: string; label: string }[];
    adobeStatusOptions: { value: string; label: string }[];
    abilities: {
        sessions: boolean;
        enrollmentsIndex: boolean;
        enrollmentsCreate: boolean;
        enrollmentsUpdate: boolean;
        enrollmentsDelete: boolean;
        adobeIndex: boolean;
        adobeCreate: boolean;
        adobeUpdate: boolean;
        adobeDelete: boolean;
    };
};

export default function CourseClassesEdit(props: Props) {
    const { routePrefix, course, courseClass, enrollments, adobeRooms, abilities } = props;
    const classId = Number(courseClass.id);
    const showAdobePanels = String(courseClass.online_platform ?? 'adobe_connect') !== 'video_link';
    const form = useForm({
        name: String(courseClass.name ?? ''),
        code: String(courseClass.code ?? ''),
        status: String(courseClass.status ?? 'draft'),
        delivery_type: String(courseClass.delivery_type ?? ''),
        online_platform: String(courseClass.online_platform ?? 'adobe_connect'),
        capacity: (courseClass.capacity as number | null) ?? '',
        price: Number(courseClass.price ?? 0),
        registration_starts_at: String(courseClass.registration_starts_at ?? ''),
        registration_ends_at: String(courseClass.registration_ends_at ?? ''),
        starts_at: String(courseClass.starts_at ?? ''),
        ends_at: String(courseClass.ends_at ?? ''),
        location: String(courseClass.location ?? ''),
        description: String(courseClass.description ?? ''),
        is_public: Boolean(courseClass.is_public),
        instructors: (courseClass.instructors as Props['courseClass']['instructors']) ?? [],
    });

    return (
        <AdminLayout>
            <Head title="ویرایش کلاس" />
            <PageHeader
                title="ویرایش کلاس"
                backHref={`${routePrefix}/courses/${course.id}/classes`}
                actions={
                    abilities.sessions ? (
                        <AdminButton
                            href={`${routePrefix}/courses/${course.id}/classes/${classId}/sessions`}
                            variant="secondary"
                        >
                            جلسات
                        </AdminButton>
                    ) : null
                }
            />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.put(`${routePrefix}/courses/${course.id}/classes/${classId}`);
                }}>
                <ClassFormFields
                    {...props}
                    data={form.data as never}
                    errors={form.errors}
                    processing={form.processing}
                    onChange={(k, v) => form.setData(k, v as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی کلاس</AdminButton>
                </AdminFormActions>
</AdminFormPage>

            {(abilities.enrollmentsIndex ||
                abilities.enrollmentsCreate ||
                abilities.enrollmentsUpdate ||
                abilities.enrollmentsDelete) && (
                <ClassEnrollmentPanel
                    courseId={course.id}
                    classId={classId}
                    enrollments={enrollments}
                    userOptions={props.enrollmentUserOptions}
                    enrollmentStatusOptions={props.enrollmentStatusOptions}
                    defaultPrice={Number(courseClass.price ?? 0)}
                    canCreate={abilities.enrollmentsCreate}
                    canUpdate={abilities.enrollmentsUpdate}
                    canDelete={abilities.enrollmentsDelete}
                />
            )}

            {showAdobePanels && (abilities.adobeIndex || abilities.adobeCreate || abilities.adobeDelete) && (
                <ClassAdobeRoomPanel
                    routePrefix={routePrefix}
                    courseId={course.id}
                    classId={classId}
                    rooms={adobeRooms}
                    adobeAccessOptions={props.adobeAccessOptions}
                    adobeStatusOptions={props.adobeStatusOptions}
                    canCreate={abilities.adobeCreate}
                    canUpdate={abilities.adobeUpdate}
                    canDelete={abilities.adobeDelete}
                />
            )}
        </AdminLayout>
    );
}
