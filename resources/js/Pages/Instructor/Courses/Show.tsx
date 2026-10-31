import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link } from '@inertiajs/react';
import { BookOpen, ClipboardList, Layers, Pencil } from 'lucide-react';

type ClassRow = {
    id: number;
    name: string;
    code: string;
    status_label: string;
    sessions_count: number;
    enrollments_count: number;
    meeting_summary_label: string | null;
    edit_url: string;
    sessions_url: string;
};

type Props = {
    course: {
        id: number;
        title: string;
        status_label: string;
        edit_url: string;
        classes_url: string;
        roster_url: string;
        quizzes_url: string;
    };
    classes: ClassRow[];
    stats: {
        classes_total: number;
        sessions_total: number;
        enrollments_confirmed: number;
        quizzes_total: number;
    };
};

export default function InstructorCourseShow({ course, classes, stats }: Props) {
    return (
        <InstructorLayout
            title={course.title}
            description="نمای کلی کلاس‌ها، ثبت‌نام‌شدگان و آزمون‌های این دوره"
            contentVariant="plain"
        >
            <Head title={course.title} />

            <div className="mb-6 flex flex-wrap gap-2">
                <Link
                    href={course.edit_url}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                >
                    <Pencil className="size-4" aria-hidden />
                    ویرایش دوره
                </Link>
                <Link
                    href={course.classes_url}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                >
                    <Layers className="size-4" aria-hidden />
                    مدیریت کلاس‌ها
                </Link>
                <Link
                    href={course.roster_url}
                    className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                >
                    <ClipboardList className="size-4" aria-hidden />
                    ثبت‌نام‌شدگان
                </Link>
                <Link
                    href={course.quizzes_url}
                    className="inline-flex items-center gap-1.5 rounded-xl bg-[rgb(11_31_58)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(16_42_74)]"
                >
                    آزمون‌ها
                </Link>
            </div>

            <div className="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {[
                    { label: 'کلاس‌ها', value: stats.classes_total },
                    { label: 'جلسات', value: stats.sessions_total },
                    { label: 'ثبت‌نام قطعی', value: stats.enrollments_confirmed },
                    { label: 'آزمون‌ها', value: stats.quizzes_total },
                ].map((card) => (
                    <div key={card.label} className="instructor-stat rounded-2xl p-4">
                        <p className="text-xs font-medium text-slate-500">{card.label}</p>
                        <p className="mt-1 text-2xl font-bold tabular-nums text-slate-900">{card.value}</p>
                    </div>
                ))}
            </div>

            <section className="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                    <div>
                        <h2 className="text-base font-bold text-slate-900">کلاس‌های دوره</h2>
                        <p className="mt-0.5 text-xs text-slate-500">وضعیت: {course.status_label}</p>
                    </div>
                    <Link href={course.classes_url} className="text-sm font-semibold text-primary hover:text-primary-hover">
                        همه کلاس‌ها
                    </Link>
                </div>

                {classes.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 px-6 py-14 text-center">
                        <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                            <BookOpen className="size-6" aria-hidden />
                        </div>
                        <p className="text-sm font-semibold text-slate-800">هنوز کلاسی تعریف نشده</p>
                        <Link
                            href={`${course.classes_url}/create`}
                            className="rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-on-primary"
                        >
                            ایجاد کلاس
                        </Link>
                    </div>
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {classes.map((row) => (
                            <li
                                key={row.id}
                                className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div className="min-w-0">
                                    <p className="font-semibold text-slate-900">{row.name}</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        {row.code} · {row.status_label} · {row.sessions_count} جلسه ·{' '}
                                        {row.enrollments_count} ثبت‌نام
                                    </p>
                                    {row.meeting_summary_label ? (
                                        <p className="mt-1 text-xs font-medium text-primary/90">{row.meeting_summary_label}</p>
                                    ) : null}
                                </div>
                                <div className="flex shrink-0 flex-wrap gap-2">
                                    <Link
                                        href={row.edit_url}
                                        className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700"
                                    >
                                        ویرایش
                                    </Link>
                                    <Link
                                        href={row.sessions_url}
                                        className="rounded-xl bg-[rgb(11_31_58)] px-3.5 py-2 text-sm font-medium text-white"
                                    >
                                        جلسات
                                    </Link>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </section>
        </InstructorLayout>
    );
}
