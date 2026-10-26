import { studentPanelLinks } from '@/lib/student-panel-links';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';

type Enrollment = {
    id: number;
    course: { id: number; title: string; slug: string };
    sessions: {
        id: number;
        title: string;
        starts_at: string | null;
        join_url: string | null;
        join_label: string | null;
        adobe_connect_url: string | null;
        recordings: { id: number; name: string; playback_url: string; download_url: string | null }[];
    }[];
};

type Props = { enrollments: Enrollment[] };

export default function Dashboard({ enrollments }: Props) {
    return (
        <PanelLayout title="پنل دانشجو" homeHref="/" links={studentPanelLinks}>
            <Head title="پنل دانشجو" />
            <h2 className="mb-4 text-lg font-semibold">دوره‌های فعال</h2>
            {enrollments.length === 0 ? (
                <p className="text-sm text-zinc-600">هنوز در دوره‌ای ثبت‌نام قطعی ندارید. پس از تأیید ثبت‌نام توسط آموزش، دوره‌ها اینجا نمایش داده می‌شوند.</p>
            ) : (
                <div className="space-y-6">
                    {enrollments.map((enrollment) => (
                        <div key={enrollment.id} className="rounded-lg border p-4">
                            <h3 className="font-medium">
                                {enrollment.course.slug ? (
                                    <Link
                                        href={`/courses/${enrollment.course.slug}`}
                                        className="text-indigo-700 hover:underline"
                                    >
                                        {enrollment.course.title}
                                    </Link>
                                ) : (
                                    enrollment.course.title
                                )}
                            </h3>
                            {enrollment.sessions.length === 0 ? (
                                <p className="mt-2 text-sm text-zinc-500">جلسه‌ای برای این کلاس ثبت نشده است.</p>
                            ) : (
                                <ul className="mt-3 space-y-2 text-sm">
                                    {enrollment.sessions.map((session) => (
                                        <li
                                            key={session.id}
                                            className="flex flex-wrap items-center justify-between gap-2 border-t pt-2"
                                        >
                                            <span>
                                                {session.title}
                                                {session.starts_at && (
                                                    <span className="ms-2 text-zinc-500">
                                                        {new Date(session.starts_at).toLocaleString('fa-IR')}
                                                    </span>
                                                )}
                                            </span>
                                            <span className="flex flex-wrap gap-2">
                                                {(session.join_url ?? session.adobe_connect_url) && (
                                                    <a
                                                        href={session.join_url ?? session.adobe_connect_url ?? '#'}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        className="rounded-lg bg-indigo-600 px-3 py-1 text-white hover:bg-indigo-700"
                                                    >
                                                        {session.join_label ?? 'ورود به کلاس'}
                                                    </a>
                                                )}
                                                {session.recordings.map((recording) => (
                                                    <span key={recording.id} className="flex gap-1">
                                                        <a
                                                            href={recording.playback_url}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="rounded-lg border border-indigo-200 px-3 py-1 text-indigo-700 hover:bg-indigo-50"
                                                        >
                                                            {recording.name}
                                                        </a>
                                                        {recording.download_url ? (
                                                            <a
                                                                href={recording.download_url}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="rounded-lg border border-slate-200 px-3 py-1 text-slate-600 hover:bg-slate-50"
                                                            >
                                                                دانلود MP4
                                                            </a>
                                                        ) : null}
                                                    </span>
                                                ))}
                                            </span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                    ))}
                </div>
            )}
        </PanelLayout>
    );
}
