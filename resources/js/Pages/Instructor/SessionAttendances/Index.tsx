import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link } from '@inertiajs/react';
import { AlertTriangle, ClipboardCheck } from 'lucide-react';

type SessionRow = {
    id: number;
    title: string;
    session_number: number;
    starts_at: string | null;
    course_title: string | null;
    class_title: string | null;
    attendances_count: number;
    present_count: number;
    absent_count: number;
    enrolled_count: number;
    attendance_rate: number | null;
    show_url: string;
    join_url?: string | null;
    join_label?: string | null;
};

type Summary = {
    sessions_total: number;
    sessions_with_records: number;
    present_count: number;
    absent_count: number;
    attendance_rate: number | null;
    avg_duration_minutes: number;
    low_attendance: {
        id: number;
        title: string;
        course_title: string | null;
        rate: number;
        present: number;
        enrolled: number;
    }[];
};

type Props = {
    summary: Summary;
    sessions: SessionRow[];
};

export default function InstructorSessionAttendancesIndex({ summary, sessions }: Props) {
    const cards = [
        {
            label: 'نرخ حضور کلاس‌های من',
            value: summary.attendance_rate === null ? '—' : `${summary.attendance_rate}%`,
        },
        { label: 'حاضر / غایب', value: `${summary.present_count} / ${summary.absent_count}` },
        {
            label: 'جلسات دارای رکورد',
            value: `${summary.sessions_with_records} از ${summary.sessions_total}`,
        },
        {
            label: 'میانگین مدت',
            value: summary.avg_duration_minutes > 0 ? `${summary.avg_duration_minutes} دقیقه` : '—',
        },
    ];

    return (
        <InstructorLayout
            title="حضور و غیاب"
            description="خلاصه حضور کلاس‌ها و جلسات با نرخ پایین"
            contentVariant="plain"
        >
            <Head title="حضور و غیاب" />

            <div className="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {cards.map((card) => (
                    <div key={card.label} className="instructor-stat rounded-2xl p-4">
                        <p className="text-xs font-medium text-slate-500">{card.label}</p>
                        <p className="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                            {card.value}
                        </p>
                    </div>
                ))}
            </div>

            {summary.low_attendance.length > 0 ? (
                <div className="mb-6 rounded-2xl border border-amber-200/80 bg-amber-50/70 p-4 md:p-5">
                    <div className="flex items-start gap-3">
                        <div className="flex size-9 shrink-0 items-center justify-center rounded-xl bg-amber-100 text-amber-800">
                            <AlertTriangle className="size-4" aria-hidden />
                        </div>
                        <div className="min-w-0 flex-1">
                            <h3 className="text-sm font-semibold text-amber-950">جلسات با حضور پایین</h3>
                            <ul className="mt-3 space-y-2 text-sm">
                                {summary.low_attendance.map((row) => (
                                    <li
                                        key={row.id}
                                        className="flex flex-wrap items-center justify-between gap-2 border-t border-amber-200/60 pt-2 first:border-0 first:pt-0"
                                    >
                                        <span className="text-amber-950/90">
                                            {row.title}
                                            {row.course_title ? ` · ${row.course_title}` : ''}
                                        </span>
                                        <Link
                                            href={`/instructor/session-attendances/${row.id}`}
                                            className="font-semibold text-amber-900 underline-offset-2 hover:underline"
                                        >
                                            {row.rate}%
                                        </Link>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </div>
                </div>
            ) : null}

            {sessions.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <ClipboardCheck className="size-6" aria-hidden />
                    </div>
                    <p className="text-sm font-semibold text-slate-800">جلسه‌ای ثبت نشده</p>
                    <p className="max-w-sm text-xs text-slate-500">
                        وقتی برای کلاس‌های شما جلسه تعریف شود، وضعیت حضور اینجاست.
                    </p>
                </div>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                    {sessions.map((session) => (
                        <li
                            key={session.id}
                            className="flex flex-col gap-3 px-4 py-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between md:px-5"
                        >
                            <div className="min-w-0">
                                <p className="font-semibold text-slate-900">{session.title}</p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {session.course_title}
                                    {session.class_title ? ` · ${session.class_title}` : ''}
                                    {session.starts_at
                                        ? ` · ${new Date(session.starts_at).toLocaleString('fa-IR')}`
                                        : ''}
                                </p>
                                <p className="mt-1 text-xs text-slate-600">
                                    {session.present_count} حاضر · {session.absent_count} غایب
                                    {session.attendance_rate !== null ? ` · ${session.attendance_rate}%` : ''}
                                </p>
                            </div>
                            <div className="flex shrink-0 flex-wrap items-center gap-2">
                                {session.join_url ? (
                                    <a
                                        href={session.join_url}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="rounded-xl border border-emerald-200 bg-emerald-50 px-3.5 py-2 text-sm font-semibold text-emerald-800 transition hover:bg-emerald-100"
                                    >
                                        {session.join_label ?? 'ورود به کلاس'}
                                    </a>
                                ) : null}
                                <Link
                                    href={session.show_url}
                                    className="rounded-xl bg-[rgb(11_31_58)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(16_42_74)]"
                                >
                                    مشاهده لیست
                                </Link>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </InstructorLayout>
    );
}
