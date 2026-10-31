import { enrollmentStatusClass } from '@/Components/Student/SessionActions';
import StudentLayout from '@/Layouts/StudentLayout';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';
import { BookOpen } from 'lucide-react';

type Enrollment = {
    id: number;
    status: string;
    status_label: string;
    show_url: string;
    course: { title: string; public_url: string | null };
    class_name: string;
    class_code: string;
    meeting_summary_label: string | null;
    sessions_count: number;
    upcoming_sessions_count: number;
};

type Props = { enrollments: Enrollment[] };

export default function StudentCoursesIndex({ enrollments }: Props) {
    return (
        <StudentLayout title="دوره‌های من" description="ثبت‌نام‌های قطعی و در انتظار تأیید">
            <Head title="دوره‌های من" />

            {enrollments.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-14 text-center">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <BookOpen className="size-6" aria-hidden />
                    </div>
                    <p className="text-sm font-semibold text-slate-800">هنوز دوره‌ای ندارید</p>
                    <p className="max-w-sm text-xs text-slate-500">
                        از سایت در دوره ثبت‌نام کنید تا وضعیت آن اینجا نمایش داده شود.
                    </p>
                </div>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200/90">
                    {enrollments.map((enrollment) => (
                        <li
                            key={enrollment.id}
                            className="flex flex-col gap-3 px-4 py-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between md:px-5"
                        >
                            <div className="min-w-0">
                                <div className="flex flex-wrap items-center gap-2">
                                    <p className="font-semibold text-slate-900">{enrollment.course.title}</p>
                                    <span
                                        className={cn(
                                            'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
                                            enrollmentStatusClass(enrollment.status),
                                        )}
                                    >
                                        {enrollment.status_label}
                                    </span>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">
                                    {enrollment.class_name ? `کلاس ${enrollment.class_name}` : 'کلاس'}
                                    {enrollment.class_code ? ` · ${enrollment.class_code}` : ''}
                                    {` · ${enrollment.sessions_count} جلسه`}
                                </p>
                                {enrollment.meeting_summary_label ? (
                                    <p className="mt-1 text-xs font-medium text-primary/90">
                                        {enrollment.meeting_summary_label}
                                    </p>
                                ) : null}
                            </div>
                            <div className="flex shrink-0 flex-wrap gap-2">
                                {enrollment.course.public_url ? (
                                    <Link
                                        href={enrollment.course.public_url}
                                        className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                                    >
                                        صفحه دوره
                                    </Link>
                                ) : null}
                                <Link
                                    href={enrollment.show_url}
                                    className="rounded-xl bg-[rgb(12_42_74)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(18_58_99)]"
                                >
                                    جلسات کلاس
                                </Link>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </StudentLayout>
    );
}
