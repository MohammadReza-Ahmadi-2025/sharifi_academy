import { instructorPanelLinks } from '@/lib/instructor-panel-links';
import { cn } from '@/lib/utils';
import PanelLayout from '@/Layouts/PanelLayout';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';
import {
    BookOpen,
    CalendarDays,
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
            icon: BookOpen,
            tone: 'text-primary bg-primary/10',
        },
        {
            label: 'منتشرشده',
            value: stats.courses_published,
            icon: Sparkles,
            tone: 'text-emerald-700 bg-emerald-500/10',
        },
        {
            label: 'پیش‌نویس',
            value: stats.courses_draft,
            icon: FileText,
            tone: 'text-amber-800 bg-amber-500/10',
        },
        {
            label: 'کلاس‌ها',
            value: stats.classes_total,
            icon: Layers,
            tone: 'text-violet-700 bg-violet-500/10',
        },
    ];

    return (
        <PanelLayout
            title="داشبورد"
            description="خلاصه فعالیت آموزشی و دسترسی سریع به دوره‌ها"
            homeHref="/"
            links={instructorPanelLinks}
            contentVariant="plain"
        >
            <Head title="پنل استاد" />

            <div className="space-y-6">
                <section
                    className="relative overflow-hidden rounded-2xl border border-slate-200/80 bg-gradient-to-l from-primary/[0.07] via-white to-white px-5 py-6 shadow-sm md:px-8"
                >
                    <div className="relative flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-2">
                            <p className="text-sm font-medium text-primary">پنل استاد</p>
                            <h2 className="text-xl font-bold text-slate-900 md:text-2xl">
                                سلام، {firstName} عزیز
                            </h2>
                            <p className="max-w-lg text-sm leading-relaxed text-slate-600">
                                دوره‌ها، کلاس‌ها و رزومهٔ حرفه‌ای خود را از یکجا مدیریت کنید. تغییرات فقط روی
                                محتوای متعلق به شما اعمال می‌شود.
                            </p>
                        </div>
                        <div className="flex flex-wrap gap-2">
                            {canCreateCourse && (
                                <Link
                                    href="/instructor/courses/create"
                                    className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary shadow-sm transition hover:bg-primary-hover"
                                >
                                    <Plus className="size-4" aria-hidden />
                                    دوره جدید
                                </Link>
                            )}
                            <Link
                                href="/instructor/resume"
                                className="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 shadow-sm transition hover:bg-slate-50"
                            >
                                <FileText className="size-4 text-slate-500" aria-hidden />
                                رزومه من
                            </Link>
                        </div>
                    </div>
                </section>

                <section className="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    {statCards.map((card) => {
                        const Icon = card.icon;

                        return (
                            <div
                                key={card.label}
                                className="flex items-center gap-4 rounded-2xl border border-slate-200/80 bg-white p-4 shadow-sm"
                            >
                                <div className={cn('flex size-11 shrink-0 items-center justify-center rounded-xl', card.tone)}>
                                    <Icon className="size-5" aria-hidden />
                                </div>
                                <div>
                                    <p className="text-xs font-medium text-slate-500">{card.label}</p>
                                    <p className="text-2xl font-bold tabular-nums text-slate-900">{card.value}</p>
                                </div>
                            </div>
                        );
                    })}
                </section>

                <section className="rounded-2xl border border-slate-200/80 bg-white shadow-sm">
                    <div className="flex flex-wrap items-center justify-between gap-3 border-b border-slate-100 px-5 py-4">
                        <div>
                            <h3 className="text-base font-bold text-slate-900">دوره‌های اخیر</h3>
                            <p className="mt-0.5 text-xs text-slate-500">آخرین به‌روزرسانی‌های شما</p>
                        </div>
                        <Link href="/instructor/courses" className="text-sm font-medium text-primary hover:text-primary-hover">
                            مشاهده همه
                        </Link>
                    </div>

                    {courses.length === 0 ? (
                        <div className="flex flex-col items-center gap-3 px-6 py-16 text-center">
                            <div className="flex size-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                <BookOpen className="size-7" aria-hidden />
                            </div>
                            <p className="text-sm font-medium text-slate-700">هنوز دوره‌ای ثبت نکرده‌اید</p>
                            <p className="max-w-sm text-xs text-slate-500">
                                با ایجاد اولین دوره، کلاس و جلسه را زیرمجموعه همان دوره تنظیم کنید.
                            </p>
                            {canCreateCourse && (
                                <Link
                                    href="/instructor/courses/create"
                                    className="mt-2 inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2 text-sm font-semibold text-on-primary"
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
                                    className="flex flex-col gap-4 px-5 py-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="truncate font-semibold text-slate-900">{course.title}</p>
                                            <span
                                                className={cn(
                                                    'inline-flex rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
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
                                            className="rounded-xl border border-slate-200 px-3 py-2 text-sm font-medium text-slate-700 transition hover:bg-white"
                                        >
                                            ویرایش
                                        </Link>
                                        <Link
                                            href={course.classes_url}
                                            className="inline-flex items-center gap-1.5 rounded-xl bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800"
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
        </PanelLayout>
    );
}
