import { enrollmentStatusClass, SessionActions } from '@/Components/Student/SessionActions';
import StudentLayout from '@/Layouts/StudentLayout';
import { cn } from '@/lib/utils';
import { Head, Link } from '@inertiajs/react';

type Session = {
    id: number;
    title: string;
    session_number: number;
    starts_at: string | null;
    join_url: string | null;
    join_label: string | null;
    join_hint: string | null;
    adobe_login_hint: string | null;
    adobe_connect_url: string | null;
    recordings: { id: number; name: string; playback_url: string; download_url: string | null }[];
};

type Enrollment = {
    id: number;
    status: string;
    status_label: string;
    course: { title: string; public_url: string | null };
    class_name: string;
    class_code: string;
    meeting_summary_label: string | null;
    sessions: Session[];
};

type Props = { enrollment: Enrollment };

export default function StudentCourseShow({ enrollment }: Props) {
    return (
        <StudentLayout
            title={enrollment.course.title}
            description={
                enrollment.class_name
                    ? `کلاس ${enrollment.class_name}${enrollment.class_code ? ` · ${enrollment.class_code}` : ''}`
                    : 'جزئیات ثبت‌نام و جلسات'
            }
        >
            <Head title={enrollment.course.title} />

            <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                <div className="flex flex-wrap items-center gap-2">
                    <Link
                        href="/student/courses"
                        className="text-sm font-semibold text-primary transition hover:text-primary-hover"
                    >
                        ← دوره‌های من
                    </Link>
                    <span
                        className={cn(
                            'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
                            enrollmentStatusClass(enrollment.status),
                        )}
                    >
                        {enrollment.status_label}
                    </span>
                    {enrollment.meeting_summary_label ? (
                        <span className="rounded-md bg-primary/8 px-2 py-0.5 text-[11px] font-semibold text-primary">
                            {enrollment.meeting_summary_label}
                        </span>
                    ) : null}
                </div>
                {enrollment.course.public_url ? (
                    <Link
                        href={enrollment.course.public_url}
                        className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                    >
                        صفحه عمومی دوره
                    </Link>
                ) : null}
            </div>

            {enrollment.sessions.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-10 text-center text-sm text-slate-500">
                    هنوز جلسه‌ای برای این کلاس ثبت نشده است.
                </p>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200/90">
                    {enrollment.sessions.map((session) => (
                        <li
                            key={session.id}
                            className="flex flex-col gap-3 px-4 py-4 md:flex-row md:items-center md:justify-between md:px-5"
                        >
                            <div className="min-w-0">
                                <p className="font-semibold text-slate-900">
                                    {session.session_number > 0 ? `${session.session_number}. ` : ''}
                                    {session.title}
                                </p>
                                {session.starts_at ? (
                                    <p className="mt-1 text-xs text-slate-500">
                                        {new Date(session.starts_at).toLocaleString('fa-IR')}
                                    </p>
                                ) : null}
                            </div>
                            <SessionActions session={session} />
                        </li>
                    ))}
                </ul>
            )}
        </StudentLayout>
    );
}
