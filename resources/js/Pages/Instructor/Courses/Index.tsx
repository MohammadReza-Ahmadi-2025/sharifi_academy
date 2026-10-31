import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link } from '@inertiajs/react';
import { BookOpen, Layers, Plus } from 'lucide-react';

type CourseRow = {
    id: number;
    title: string;
    status_label: string;
    classes_count: number;
};

type Props = {
    courses: CourseRow[];
    canCreate: boolean;
};

export default function InstructorCoursesIndex({ courses, canCreate }: Props) {
    return (
        <InstructorLayout
            title="دوره‌های من"
            description="مدیریت محتوا، وضعیت انتشار و کلاس‌های هر دوره"
        >
            <Head title="دوره‌های من" />

            <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                <p className="text-sm text-slate-600">
                    {courses.length > 0 ? `${courses.length} دوره` : 'هنوز دوره‌ای ندارید'}
                </p>
                {canCreate && (
                    <Link
                        href="/instructor/courses/create"
                        className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition hover:bg-primary-hover"
                    >
                        <Plus className="size-4" aria-hidden />
                        دوره جدید
                    </Link>
                )}
            </div>

            {courses.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-14 text-center">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <BookOpen className="size-6" aria-hidden />
                    </div>
                    <p className="text-sm font-semibold text-slate-800">لیست دوره‌ها خالی است</p>
                    <p className="max-w-sm text-xs text-slate-500">
                        دوره جدید بسازید تا بتوانید کلاس و جلسه تعریف کنید.
                    </p>
                </div>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200/90">
                    {courses.map((course) => (
                        <li
                            key={course.id}
                            className="flex flex-col gap-3 px-4 py-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between md:px-5"
                        >
                            <div className="min-w-0">
                                <p className="font-semibold text-slate-900">{course.title}</p>
                                <p className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span>{course.status_label}</span>
                                    <span className="inline-flex items-center gap-1">
                                        <Layers className="size-3.5 opacity-60" aria-hidden />
                                        {course.classes_count} کلاس
                                    </span>
                                </p>
                            </div>
                            <div className="flex shrink-0 flex-wrap gap-2">
                                <Link
                                    href={`/instructor/courses/${course.id}`}
                                    className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                                >
                                    نمای کلی
                                </Link>
                                <Link
                                    href={`/instructor/courses/${course.id}/edit`}
                                    className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                                >
                                    ویرایش
                                </Link>
                                <Link
                                    href={`/instructor/courses/${course.id}/classes`}
                                    className="rounded-xl bg-[rgb(11_31_58)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(16_42_74)]"
                                >
                                    کلاس‌ها
                                </Link>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </InstructorLayout>
    );
}
