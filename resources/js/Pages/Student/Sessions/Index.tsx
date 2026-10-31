import { SessionActions } from '@/Components/Student/SessionActions';
import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';

type SessionRow = {
    id: number;
    title: string;
    starts_at: string | null;
    course_title: string;
    class_name: string;
    show_url: string;
    is_upcoming: boolean;
    join_url: string | null;
    join_label: string | null;
    join_hint: string | null;
    adobe_login_hint: string | null;
    adobe_connect_url: string | null;
    recordings: { id: number; name: string; playback_url: string; download_url: string | null }[];
};

type Props = { sessions: SessionRow[] };

export default function StudentSessionsIndex({ sessions }: Props) {
    const upcoming = sessions.filter((session) => session.is_upcoming);
    const past = sessions.filter((session) => !session.is_upcoming);

    return (
        <StudentLayout
            title="جلسات"
            description="برنامه کلاس‌های دوره‌های قطعی شما"
            contentVariant="plain"
        >
            <Head title="جلسات من" />

            {sessions.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <CalendarDays className="size-6" aria-hidden />
                    </div>
                    <p className="text-sm font-semibold text-slate-800">جلسه‌ای ثبت نشده</p>
                    <p className="max-w-sm text-xs text-slate-500">
                        وقتی برای کلاس‌های تأییدشده شما جلسه تعریف شود، اینجا می‌آید.
                    </p>
                </div>
            ) : (
                <div className="space-y-6">
                    <SessionGroup title="پیش‌رو" empty="جلسهٔ پیش‌رویی نیست." rows={upcoming} />
                    <SessionGroup title="گذشته" empty="جلسهٔ گذشته‌ای نیست." rows={past} />
                </div>
            )}
        </StudentLayout>
    );
}

function SessionGroup({
    title,
    empty,
    rows,
}: {
    title: string;
    empty: string;
    rows: SessionRow[];
}) {
    return (
        <section className="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
            <div className="border-b border-slate-100 px-5 py-4">
                <h2 className="text-base font-bold text-slate-900">{title}</h2>
            </div>
            {rows.length === 0 ? (
                <p className="px-5 py-8 text-sm text-slate-500">{empty}</p>
            ) : (
                <ul className="divide-y divide-slate-100">
                    {rows.map((session) => (
                        <li
                            key={`${session.id}-${session.show_url}`}
                            className="flex flex-col gap-3 px-5 py-4 md:flex-row md:items-center md:justify-between"
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
                                <Link
                                    href={session.show_url}
                                    className="mt-1 inline-block text-xs font-semibold text-primary hover:text-primary-hover"
                                >
                                    جزئیات دوره
                                </Link>
                            </div>
                            <SessionActions session={session} />
                        </li>
                    ))}
                </ul>
            )}
        </section>
    );
}
