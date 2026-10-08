import { normalizeCourseFormPayload } from '@/Components/Admin/Courses/course-form-payload';
import { CourseFormFields } from '@/Components/Admin/Courses/CourseFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import type { ExistingFilePayload } from '@/Components/shared/file-upload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    course: Record<string, unknown> & { gallery?: ExistingFilePayload[] };
    statusOptions: { value: string; label: string }[];
    levelOptions: { value: string; label: string }[];
    categoryOptions: { value: number; label: string }[];
};

export default function CoursesEdit({ course, statusOptions, levelOptions, categoryOptions }: Props) {
    const [gallery, setGallery] = useGalleryUploadState(course.gallery ?? []);
    const form = useForm({
        title: String(course.title ?? ''),
        slug: String(course.slug ?? ''),
        code: String(course.code ?? ''),
        short_description: String(course.short_description ?? ''),
        description: String(course.description ?? ''),
        level: String(course.level ?? ''),
        duration_minutes: (course.duration_minutes as number | null) ?? '',
        status: String(course.status ?? 'draft'),
        is_featured: Boolean(course.is_featured),
        published_at: String(course.published_at ?? ''),
        category_ids: (course.category_ids as number[]) ?? [],
        meta_title: String(course.meta_title ?? ''),
        meta_description: String(course.meta_description ?? ''),
        meta_keywords: String(course.meta_keywords ?? ''),
        canonical: String(course.canonical ?? ''),
    });

    return (
        <AdminLayout>
            <Head title="ویرایش دوره" />
            <PageHeader title="ویرایش دوره" backHref={`/admin/courses/${course.id}`} />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(normalizeCourseFormPayload(data), gallery));
                    form.put(`/admin/courses/${course.id}`, { forceFormData: true });
                }}>
                <GalleryImagesField
                    title="گالری تصاویر دوره"
                    items={gallery}
                    onItemsChange={setGallery}
                    disabled={form.processing}
                />
                <CourseFormFields
                    data={form.data}
                    errors={form.errors}
                    statusOptions={statusOptions}
                    levelOptions={levelOptions}
                    categoryOptions={categoryOptions}
                    processing={form.processing}
                    onChange={(key, value) => form.setData(key, value as never)}
                />
                <AdminFormActions>

                    <AdminButton type="submit" disabled={form.processing}>به‌روزرسانی</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
