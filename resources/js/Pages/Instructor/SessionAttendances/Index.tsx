import { instructorPanelLinks } from '@/lib/instructor-panel-links';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';

type SessionRow = {
    id: number;
    title: string;
    session_number: number;
    starts_at: string | null;
    course_title: string | null;
    class_title: string | null;
    attendances_count: number;
    show_url: string;
};

type Props = { sessions: SessionRow[] };

export default function InstructorSessionAttendancesIndex({ sessions }: Props) {
    return (
        <PanelLayout title="حضور و غیاب" homeHref="/instructor" links={instructorPanelLinks}>
            <Head title="حضور و غیاب" />
            {sessions.length === 0 ? (
                <p className="text-sm text-slate-600">جلسه‌ای برای دوره‌های شما ثبت نشده است.</p>
            ) : (
                <ul className="divide-y rounded-xl border border-slate-200">
                    {sessions.map((session) => (
                        <li key={session.id} className="flex flex-wrap items-center justify-between gap-2 px-4 py-3 text-sm">
                            <div>
                                <p className="font-medium">{session.title}</p>
                                <p className="text-xs text-slate-500">
                                    {session.course_title}
                                    {session.class_title ? ` · ${session.class_title}` : ''}
                                    {session.starts_at
                                        ? ` · ${new Date(session.starts_at).toLocaleString('fa-IR')}`
                                        : ''}
                                </p>
                            </div>
                            <Link href={session.show_url} className="text-indigo-600 hover:underline">
                                مشاهده لیست
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </PanelLayout>
    );
}
