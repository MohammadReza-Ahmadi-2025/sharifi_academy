import { normalizeCourseFormPayload } from '@/Components/Admin/Courses/course-form-payload';
import { CourseFormFields } from '@/Components/Admin/Courses/CourseFormFields';
import { GalleryImagesField, useGalleryUploadState } from '@/Components/Admin/shared/GalleryImagesField';
import { withGalleryPayload } from '@/Components/shared/gallery-form-payload';
import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Props = {
    statusOptions: { value: string; label: string }[];
    levelOptions: { value: string; label: string }[];
    categoryOptions: { value: number; label: string }[];
};

export default function InstructorCoursesCreate({ statusOptions, levelOptions, categoryOptions }: Props) {
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

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.transform((data) => withGalleryPayload(normalizeCourseFormPayload(data), gallery));
        form.post('/instructor/courses', { forceFormData: true });
    };

    return (
        <InstructorLayout title="ایجاد دوره" description="اطلاعات دوره جدید را تکمیل کنید">
            <Head title="ایجاد دوره" />
            <form onSubmit={submit} className="space-y-6">
                <GalleryImagesField
                    title="گالری تصاویر دوره"
                    description="تصاویر معرفی دوره را آپلود کنید."
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
                <div className="flex gap-3">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary transition hover:bg-primary-hover disabled:opacity-60"
                    >
                        ذخیره دوره
                    </button>
                    <Link
                        href="/instructor/courses"
                        className="rounded-xl border border-slate-200 px-5 py-2.5 text-sm font-medium text-slate-700 transition hover:bg-slate-50"
                    >
                        انصراف
                    </Link>
                </div>
            </form>
        </InstructorLayout>
    );
}
