import { normalizeCourseFormPayload } from '@/Components/Admin/Courses/course-form-payload';
import { CourseFormFields } from '@/Components/Admin/Courses/CourseFormFields';
import { AdminButton } from '@/Components/Admin/shared/ui/button';
import { PageHeader } from '@/Components/Admin/shared/ui/page-header';
import AdminLayout from '@/Layouts/AdminLayout';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { Head, useForm } from '@inertiajs/react';
import { AdminFormActions, AdminFormPage } from '@/Components/Admin/shared/layout/admin-form-page';

type Props = {
    statusOptions: { value: string; label: string }[];
    levelOptions: { value: string; label: string }[];
    categoryOptions: { value: number; label: string }[];
};

export default function CoursesCreate({ statusOptions, levelOptions, categoryOptions }: Props) {
    const [gallery, setGallery] = useGalleryUploadState();
    const form = useForm({
        title: '',
        slug: '',
        code: '',
        short_description: '',
        description: '',
        level: '',
        duration_minutes: '' as number | '',
        status: 'draft',
        is_featured: false,
        published_at: '',
        category_ids: [] as number[],
        meta_title: '',
        meta_description: '',
        meta_keywords: '',
        canonical: '',
    });

    return (
        <AdminLayout>
            <Head title="ایجاد دوره" />
            <PageHeader title="ایجاد دوره" backHref="/admin/courses" />
            <AdminFormPage onSubmit={(e) => {
                    e.preventDefault();
                    form.transform((data) => withGalleryPayload(normalizeCourseFormPayload(data), gallery));
                    form.post('/admin/courses', { forceFormData: true });
                }}>
                <GalleryImagesField
                    title="گالری تصاویر دوره"
                    description="می‌توانید چند تصویر برای معرفی دوره آپلود کنید. با دستگیره ترتیب را عوض کنید."
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

                    <AdminButton type="submit" disabled={form.processing}>ذخیره</AdminButton>
                </AdminFormActions>
</AdminFormPage>
        </AdminLayout>
    );
}
