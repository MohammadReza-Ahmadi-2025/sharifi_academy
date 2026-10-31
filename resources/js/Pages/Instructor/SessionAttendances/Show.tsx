import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

type Row = {
    user_id: number;
    name: string;
    mobile: string | null;
    status: string;
    status_label: string;
    duration_minutes?: number;
    source_label?: string | null;
    note: string;
};

type Props = {
    session: {
        id: number;
        title: string;
        session_number: number;
        course_title: string | null;
        class_title: string | null;
        join_url?: string | null;
        join_label?: string | null;
    };
    rows: Row[];
    can_sync_adobe?: boolean;
};

export default function InstructorSessionAttendanceShow({ session, rows, can_sync_adobe = false }: Props) {
    const [syncing, setSyncing] = useState(false);

    const syncAdobe = () => {
        setSyncing(true);
        router.post(`/instructor/session-attendances/${session.id}/sync-adobe`, { mark_absent: true }, {
            preserveScroll: true,
            onFinish: () => setSyncing(false),
        });
    };

    return (
        <InstructorLayout
            title={session.title}
            description={[session.course_title, session.class_title].filter(Boolean).join(' · ') || undefined}
        >
            <Head title={`حضور — ${session.title}`} />
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <Link
                    href="/instructor/session-attendances"
                    className="text-sm font-medium text-primary transition hover:text-primary-hover"
                >
                    ← بازگشت به لیست جلسات
                </Link>
                <div className="flex flex-wrap gap-2">
                    {can_sync_adobe ? (
                        <button
                            type="button"
                            disabled={syncing}
                            onClick={syncAdobe}
                            className="inline-flex rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-50 disabled:opacity-60"
                        >
                            {syncing ? 'در حال همگام‌سازی…' : 'همگام‌سازی از Adobe'}
                        </button>
                    ) : null}
                    {session.join_url ? (
                        <a
                            href={session.join_url}
                            target="_blank"
                            rel="noreferrer"
                            className="inline-flex rounded-xl bg-emerald-700 px-3 py-2 text-sm font-semibold text-white hover:bg-emerald-800"
                        >
                            {session.join_label ?? 'ورود به کلاس'}
                        </a>
                    ) : null}
                </div>
            </div>
            <div className="overflow-hidden rounded-2xl border border-slate-200">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-slate-600">
                        <tr>
                            <th className="px-4 py-2.5 text-start font-medium">دانشجو</th>
                            <th className="px-4 py-2.5 text-start font-medium">وضعیت</th>
                            <th className="px-4 py-2.5 text-start font-medium">مدت</th>
                            <th className="px-4 py-2.5 text-start font-medium">یادداشت</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                        {rows.length === 0 ? (
                            <tr>
                                <td colSpan={4} className="px-4 py-8 text-center text-slate-500">
                                    دانشجوی قطعی برای این کلاس نیست.
                                </td>
                            </tr>
                        ) : (
                            rows.map((row) => (
                                <tr key={row.user_id} className="hover:bg-slate-50/80">
                                    <td className="px-4 py-3">
                                        <div className="font-medium text-slate-900">{row.name}</div>
                                        {row.mobile ? (
                                            <div className="text-xs text-slate-500" dir="ltr">
                                                {row.mobile}
                                            </div>
                                        ) : null}
                                        {row.source_label ? (
                                            <div className="text-xs text-slate-400">{row.source_label}</div>
                                        ) : null}
                                    </td>
                                    <td className="px-4 py-3">{row.status_label}</td>
                                    <td className="px-4 py-3 text-slate-600">
                                        {(row.duration_minutes ?? 0) > 0 ? `${row.duration_minutes} دقیقه` : '—'}
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">{row.note || '—'}</td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
            <p className="mt-3 text-xs text-slate-500">
                فقط جلسات کلاس‌هایی که به‌عنوان استاد به آن‌ها اختصاص داده‌اید نمایش داده می‌شود. ویرایش از پنل ادمین
                انجام می‌شود.
            </p>
        </InstructorLayout>
    );
}
