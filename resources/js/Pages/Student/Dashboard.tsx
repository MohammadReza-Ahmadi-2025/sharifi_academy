import { enrollmentStatusClass, SessionActions } from '@/Components/Student/SessionActions';
import StudentLayout from '@/Layouts/StudentLayout';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import { BookOpen, CalendarDays, ClipboardCheck, Clock3 } from 'lucide-react';

type Enrollment = {
    id: number;
    status: string;
    status_label: string;
    show_url: string;
    course: { id: number; title: string; slug: string; public_url: string | null };
    class_name: string;
    sessions_count: number;
    upcoming_sessions_count: number;
};

type UpcomingSession = {
    id: number;
    title: string;
    starts_at: string | null;
    course_title: string;
    class_name: string;
    show_url: string;
    join_url: string | null;
    join_label: string | null;
    join_hint: string | null;
    adobe_login_hint: string | null;
    adobe_connect_url: string | null;
    recordings: { id: number; name: string; playback_url: string; download_url: string | null }[];
};

type Stats = {
    courses_confirmed: number;
    courses_pending: number;
    sessions_upcoming: number;
    attendance_rate: number | null;
    attendance_present: number;
    attendance_absent: number;
};

type Props = {
    stats: Stats;
    upcomingSessions: UpcomingSession[];
    enrollments: Enrollment[];
};

export default function Dashboard({ stats, upcomingSessions, enrollments }: Props) {
    const { auth } = usePage<PageProps>().props;
    const firstName = auth.account?.display_name?.split(/\s+/)[0] ?? 'دانشجو';

    const statCards = [
        {
            label: 'دوره‌های قطعی',
            value: stats.courses_confirmed,
            hint: stats.courses_pending > 0 ? `${stats.courses_pending} در انتظار` : 'ثبت‌نام تأییدشده',
            icon: BookOpen,
            tone: 'text-primary bg-primary/10',
        },
        {
            label: 'جلسات پیش‌رو',
            value: stats.sessions_upcoming,
            hint: 'برنامه کلاس‌های شما',
            icon: CalendarDays,
            tone: 'text-sky-800 bg-sky-500/10',
        },
        {
            label: 'نرخ حضور',
            value: stats.attendance_rate === null ? '—' : `${stats.attendance_rate}%`,
            hint:
                stats.attendance_rate === null
                    ? 'هنوز رکوردی نیست'
                    : `${stats.attendance_present} حاضر · ${stats.attendance_absent} غایب`,
            icon: ClipboardCheck,
            tone: 'text-teal-800 bg-teal-500/10',
        },
        {
            label: 'در انتظار تأیید',
            value: stats.courses_pending,
            hint: 'منتظر بررسی آموزش',
            icon: Clock3,
            tone: 'text-amber-800 bg-amber-500/10',
        },
    ];

    return (
        <StudentLayout
            title="داشبورد یادگیری"
            description="خلاصه دوره‌ها، جلسات بعدی و وضعیت حضور"
            contentVariant="plain"
        >
            <Head title="پنل دانشجو" />

            <div className="space-y-6">
                <section className="student-hero student-fade-up relative rounded-3xl px-5 py-7 md:px-8 md:py-9">
                    <div className="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-xl space-y-3">
                            <p className="text-[11px] font-semibold tracking-[0.18em] text-sky-200/90 uppercase">
                                Learning workspace
                            </p>
                            <h2 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                                سلام، {firstName}
                            </h2>
                            <p className="text-sm leading-relaxed text-slate-200/90">
                                از اینجا به کلاس‌ها وارد شوید، ضبط جلسات را ببینید و وضعیت حضور خود را دنبال کنید.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            <Link
                                href="/student/courses"
                                className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-sky-50"
                            >
                                دوره‌های من
                            </Link>
                            <Link
                                href="/student/sessions"
                                className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15"
                            >
                                برنامه جلسات
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card, index) => {
                        const Icon = card.icon;

                        return (
                            <div
                                key={card.label}
                                className={cn(
                                    'student-stat student-fade-up rounded-2xl p-4',
                                    index === 1 && 'student-fade-up-delay-1',
                                    index === 2 && 'student-fade-up-delay-2',
                                    index === 3 && 'student-fade-up-delay-3',
                                )}
                            >
                                <div className="flex items-start justify-between gap-3">
                                    <div>
                                        <p className="text-xs font-medium text-slate-500">{card.label}</p>
                                        <p className="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                                            {card.value}
                                        </p>
                                        <p className="mt-1 text-[11px] text-slate-500">{card.hint}</p>
                                    </div>
                                    <div
                                        className={cn(
                                            'flex size-10 shrink-0 items-center justify-center rounded-xl',
                                            card.tone,
                                        )}
                                    >
                                        <Icon className="size-5" aria-hidden />
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </section>

                <section className="student-fade-up student-fade-up-delay-1 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 md:px-6">
                        <div>
                            <h3 className="text-base font-bold text-slate-900">جلسات بعدی</h3>
                            <p className="mt-0.5 text-xs text-slate-500">نزدیک‌ترین کلاس‌های برنامه‌ریزی‌شده</p>
                        </div>
                        <Link
                            href="/student/sessions"
                            className="text-sm font-semibold text-primary transition hover:text-primary-hover"
                        >
                            همه جلسات
                        </Link>
                    </div>
                    {upcomingSessions.length === 0 ? (
                        <p className="px-5 py-10 text-center text-sm text-slate-500 md:px-6">
                            جلسهٔ پیش‌رویی ندارید. پس از تأیید ثبت‌نام و تعریف جلسه، اینجا نمایش داده می‌شود.
                        </p>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {upcomingSessions.map((session) => (
                                <li
                                    key={`${session.id}-${session.show_url}`}
                                    className="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between md:px-6"
                                >
                                    <div className="min-w-0">
                                        <p className="font-semibold text-slate-900">{session.title}</p>
                                        <p className="mt-1 text-xs text-slate-500">
                                            {session.course_title}
                                            {session.class_name ? ` · ${session.class_name}` : ''}
                                            {session.starts_at
                                                ? ` · ${new Date(session.starts_at).toLocaleString('fa-IR')}`
                                                : ''}
                                        </p>
                                    </div>
                                    <SessionActions session={session} />
                                </li>
                            ))}
                        </ul>
                    )}
                </section>

                <section className="student-fade-up student-fade-up-delay-2 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 md:px-6">
                        <div>
                            <h3 className="text-base font-bold text-slate-900">دوره‌های من</h3>
                            <p className="mt-0.5 text-xs text-slate-500">آخرین ثبت‌نام‌های شما</p>
                        </div>
                        <Link
                            href="/student/courses"
                            className="text-sm font-semibold text-primary transition hover:text-primary-hover"
                        >
                            مشاهده همه
                        </Link>
                    </div>
                    {enrollments.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 px-6 py-14 text-center">
                            <div className="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <BookOpen className="size-7" aria-hidden />
                            </div>
                            <p className="text-sm font-semibold text-slate-800">هنوز ثبت‌نامی ندارید</p>
                            <p className="max-w-sm text-xs leading-relaxed text-slate-500">
                                از صفحه دوره‌ها ثبت‌نام کنید؛ پس از تأیید آموزش، کلاس‌ها اینجا فعال می‌شوند.
                            </p>
                            <Link
                                href="/"
                                className="mt-1 inline-flex rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary"
                            >
                                مشاهده سایت
                            </Link>
                        </div>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {enrollments.map((enrollment) => (
                                <li
                                    key={enrollment.id}
                                    className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between md:px-6"
                                >
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="truncate font-semibold text-slate-900">
                                                {enrollment.course.title}
                                            </p>
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
                                            {enrollment.class_name ? `کلاس ${enrollment.class_name} · ` : ''}
                                            {enrollment.sessions_count} جلسه
                                            {enrollment.upcoming_sessions_count > 0
                                                ? ` · ${enrollment.upcoming_sessions_count} پیش‌رو`
                                                : ''}
                                        </p>
                                    </div>
                                    <Link
                                        href={enrollment.show_url}
                                        className="rounded-xl bg-[rgb(12_42_74)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(18_58_99)]"
                                    >
                                        جزئیات
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </StudentLayout>
    );
}
