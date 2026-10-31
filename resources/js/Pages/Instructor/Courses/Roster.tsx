import InstructorLayout from '@/Layouts/InstructorLayout';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';

type EnrollmentRow = {
    id: number;
    status: string;
    status_label: string;
    class_name: string;
    confirmed_at: string | null;
    user: { id: number; name: string; mobile: string | null; email: string | null };
};

type Props = {
    course: { id: number; title: string };
    enrollments: EnrollmentRow[];
};

function statusClass(status: string): string {
    switch (status) {
        case 'confirmed':
            return 'bg-emerald-50 text-emerald-800 ring-emerald-600/15';
        case 'cancelled':
            return 'bg-slate-100 text-slate-600 ring-slate-500/10';
        default:
            return 'bg-amber-50 text-amber-900 ring-amber-600/15';
    }
}

export default function InstructorCourseRoster({ course, enrollments }: Props) {
    return (
        <InstructorLayout
            title={`ثبت‌نام‌شدگان — ${course.title}`}
            description="همه ثبت‌نام‌های کلاس‌های این دوره"
        >
            <Head title={`ثبت‌نام‌شدگان — ${course.title}`} />

            <div className="mb-5">
                <Link
                    href={`/instructor/courses/${course.id}`}
                    className="text-sm font-semibold text-primary transition hover:text-primary-hover"
                >
                    ← بازگشت به نمای دوره
                </Link>
            </div>

            {enrollments.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-10 text-center text-sm text-slate-500">
                    هنوز ثبت‌نامی برای این دوره نیست.
                </p>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200">
                    {enrollments.map((row) => (
                        <li
                            key={row.id}
                            className="flex flex-col gap-2 px-4 py-3 text-sm sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div className="min-w-0">
                                <div className="flex flex-wrap items-center gap-2">
                                    <span className="font-semibold text-slate-900">{row.user.name}</span>
                                    <span
                                        className={cn(
                                            'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
                                            statusClass(row.status),
                                        )}
                                    >
                                        {row.status_label}
                                    </span>
                                </div>
                                <p className="mt-1 text-xs text-slate-500">
                                    {row.class_name ? `کلاس ${row.class_name}` : 'کلاس'}
                                    {row.user.mobile ? ` · ${row.user.mobile}` : ''}
                                </p>
                            </div>
                            {row.user.email ? (
                                <span className="text-slate-500" dir="ltr">
                                    {row.user.email}
                                </span>
                            ) : null}
                        </li>
                    ))}
                </ul>
            )}
        </InstructorLayout>
    );
}
