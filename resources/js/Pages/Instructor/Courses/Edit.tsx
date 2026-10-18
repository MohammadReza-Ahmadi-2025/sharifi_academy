import { normalizeCourseFormPayload } from '@/Components/Admin/Courses/course-form-payload';
import { CourseFormFields } from '@/Components/Admin/Courses/CourseFormFields';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import { instructorPanelLinks } from '@/lib/instructor-panel-links';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = {
    course: {
        id: number;
        title: string;
        slug: string;
        code: string;
        short_description: string;
        description: string;
        level: string;
        duration_minutes: number | null;
        status: string;
        is_featured: boolean;
        published_at: string | null;
        category_ids: number[];
        meta_title: string;
        meta_description: string;
        meta_keywords: string;
        canonical: string;
        gallery: { id: number; url: string; sort: number }[];
    };
    statusOptions: { value: string; label: string }[];
    levelOptions: { value: string; label: string }[];
    categoryOptions: { value: number; label: string }[];
};

export default function InstructorCoursesEdit({ course, statusOptions, levelOptions, categoryOptions }: Props) {
    const [gallery, setGallery] = useGalleryUploadState(course.gallery);
    const form = useForm({
        title: course.title,
        slug: course.slug,
        code: course.code ?? '',
        short_description: course.short_description ?? '',
        description: course.description ?? '',
        level: course.level ?? '',
        duration_minutes: course.duration_minutes ?? ('' as number | ''),
        status: course.status,
        is_featured: course.is_featured,
        published_at: course.published_at ?? '',
        category_ids: course.category_ids,
        meta_title: course.meta_title ?? '',
        meta_description: course.meta_description ?? '',
        meta_keywords: course.meta_keywords ?? '',
        canonical: course.canonical ?? '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => withGalleryPayload(normalizeCourseFormPayload(data), gallery));
        form.put(`/instructor/courses/${course.id}`, { forceFormData: true });
    };

    return (
        <PanelLayout title={`ویرایش: ${course.title}`} homeHref="/instructor" links={instructorPanelLinks}>
            <Head title={`ویرایش ${course.title}`} />
            <div className="mb-4">
                <Link href={`/instructor/courses/${course.id}/classes`} className="text-sm text-indigo-600 hover:underline">
                    مدیریت کلاس‌ها و جلسات
                </Link>
            </div>
            <form onSubmit={submit} className="space-y-6">
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
                    showFeatured={false}
                    onChange={(key, value) => form.setData(key, value as never)}
                />
                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white disabled:opacity-60"
                >
                    ذخیره تغییرات
                </button>
            </form>
        </PanelLayout>
    );
}
