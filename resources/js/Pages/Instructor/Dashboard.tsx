import { cn } from '@/lib/utils';
import InstructorLayout from '@/Layouts/InstructorLayout';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
    ClipboardCheck,
    FileText,
    Layers,
    Plus,
    Sparkles,
} from 'lucide-react';

type CourseRow = {
    id: number;
    title: string;
    status: string;
    status_label: string;
    level_label: string | null;
    classes_count: number;
    edit_url: string;
    classes_url: string;
};

type Stats = {
    courses_total: number;
    courses_published: number;
    courses_draft: number;
    classes_total: number;
    attendance_rate: number | null;
    attendance_present: number;
    attendance_absent: number;
};

type Props = {
    courses: CourseRow[];
    stats: Stats;
    canCreateCourse: boolean;
};

function statusBadgeClass(status: string): string {
    switch (status) {
        case 'published':
            return 'bg-emerald-50 text-emerald-800 ring-emerald-600/15';
        case 'archived':
            return 'bg-slate-100 text-slate-600 ring-slate-500/10';
        default:
            return 'bg-amber-50 text-amber-900 ring-amber-600/15';
    }
}

export default function InstructorDashboard({ courses, stats, canCreateCourse }: Props) {
    const { auth } = usePage<PageProps>().props;
    const firstName = auth.account?.display_name?.split(/\s+/)[0] ?? 'استاد';

    const statCards = [
        {
            label: 'دوره‌های من',
            value: stats.courses_total,
            hint: `${stats.courses_draft} پیش‌نویس`,
            icon: BookOpen,
            tone: 'text-primary bg-primary/10',
        },
        {
            label: 'منتشرشده',
            value: stats.courses_published,
            hint: 'قابل ثبت‌نام',
            icon: Sparkles,
            tone: 'text-emerald-700 bg-emerald-500/10',
        },
        {
            label: 'کلاس‌ها',
            value: stats.classes_total,
            hint: 'زیرمجموعه دوره‌ها',
            icon: Layers,
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
    ];

    return (
        <InstructorLayout
            title="داشبورد تدریس"
            description="نمای عملیاتی دوره‌ها، کلاس‌ها و حضور دانشجویان"
            contentVariant="plain"
        >
            <Head title="پنل استاد" />

            <div className="space-y-6">
                <section className="instructor-hero instructor-fade-up relative rounded-3xl px-5 py-7 md:px-8 md:py-9">
                    <div className="relative z-10 flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                        <div className="max-w-xl space-y-3">
                            <p className="text-[11px] font-semibold tracking-[0.18em] text-sky-200/90 uppercase">
                                Teaching workspace
                            </p>
                            <h2 className="text-2xl font-bold tracking-tight text-white md:text-3xl">
                                سلام، {firstName}
                            </h2>
                            <p className="text-sm leading-relaxed text-slate-200/90">
                                محتوای آموزشی، جلسات و حضور کلاس‌های خود را از یک میزکار متمرکز مدیریت کنید.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {canCreateCourse && (
                                <Link
                                    href="/instructor/courses/create"
                                    className="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-slate-900 shadow-sm transition hover:bg-sky-50"
                                >
                                    <Plus className="size-4" aria-hidden />
                                    دوره جدید
                                </Link>
                            )}
                            <Link
                                href="/instructor/resume"
                                className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15"
                            >
                                <FileText className="size-4 opacity-80" aria-hidden />
                                رزومه
                            </Link>
                            <Link
                                href="/instructor/session-attendances"
                                className="inline-flex items-center gap-2 rounded-xl border border-white/20 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white backdrop-blur transition hover:bg-white/15"
                            >
                                <CalendarDays className="size-4 opacity-80" aria-hidden />
                                حضور و غیاب
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
                                    'instructor-stat instructor-fade-up rounded-2xl p-4',
                                    index === 1 && 'instructor-fade-up-delay-1',
                                    index === 2 && 'instructor-fade-up-delay-2',
                                    index === 3 && 'instructor-fade-up-delay-3',
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

                <section className="instructor-fade-up instructor-fade-up-delay-2 overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4 md:px-6">
                        <div>
                            <h3 className="text-base font-bold text-slate-900">دوره‌های اخیر</h3>
                            <p className="mt-0.5 text-xs text-slate-500">آخرین دوره‌هایی که روی آن‌ها کار می‌کنید</p>
                        </div>
                        <Link
                            href="/instructor/courses"
                            className="text-sm font-semibold text-primary transition hover:text-primary-hover"
                        >
                            مشاهده همه
                        </Link>
                    </div>

                    {courses.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                            <div className="flex size-14 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                                <BookOpen className="size-7" aria-hidden />
                            </div>
                            <p className="text-sm font-semibold text-slate-800">هنوز دوره‌ای ثبت نکرده‌اید</p>
                            <p className="max-w-sm text-xs leading-relaxed text-slate-500">
                                با ایجاد اولین دوره، کلاس و جلسه را زیرمجموعه همان دوره تنظیم کنید.
                            </p>
                            {canCreateCourse && (
                                <Link
                                    href="/instructor/courses/create"
                                    className="mt-2 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary"
                                >
                                    <Plus className="size-4" aria-hidden />
                                    شروع با دوره جدید
                                </Link>
                            )}
                        </div>
                    ) : (
                        <ul className="divide-y divide-slate-100">
                            {courses.map((course) => (
                                <li
                                    key={course.id}
                                    className="flex flex-col gap-4 px-5 py-4 transition hover:bg-slate-50/90 sm:flex-row sm:items-center sm:justify-between md:px-6"
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="truncate font-semibold text-slate-900">{course.title}</p>
                                            <span
                                                className={cn(
                                                    'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
                                                    statusBadgeClass(course.status),
                                                )}
                                            >
                                                {course.status_label}
                                            </span>
                                        </div>
                                        <p className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-slate-500">
                                            {course.level_label && <span>{course.level_label}</span>}
                                            <span className="inline-flex items-center gap-1">
                                                <Layers className="size-3.5 opacity-60" aria-hidden />
                                                {course.classes_count} کلاس
                                            </span>
                                        </p>
                                    </div>
                                    <div className="flex shrink-0 flex-wrap gap-2">
                                        <Link
                                            href={course.edit_url}
                                            className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700 transition hover:border-primary/30 hover:text-primary"
                                        >
                                            ویرایش
                                        </Link>
                                        <Link
                                            href={course.classes_url}
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-[rgb(11_31_58)] px-3.5 py-2 text-sm font-medium text-white transition hover:bg-[rgb(16_42_74)]"
                                        >
                                            <CalendarDays className="size-4 opacity-80" aria-hidden />
                                            کلاس‌ها
                                        </Link>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </InstructorLayout>
    );
}
