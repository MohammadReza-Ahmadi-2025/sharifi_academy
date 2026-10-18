import { instructorPanelLinks } from '@/lib/instructor-panel-links';
import PanelLayout from '@/Layouts/PanelLayout';
import { Head, Link } from '@inertiajs/react';

type Row = {
    user_id: number;
    name: string;
    mobile: string | null;
    status: string;
    status_label: string;
    note: string;
};

type Props = {
    session: {
        id: number;
        title: string;
        session_number: number;
        course_title: string | null;
        class_title: string | null;
    };
    rows: Row[];
};

export default function InstructorSessionAttendanceShow({ session, rows }: Props) {
    return (
        <PanelLayout title={session.title} homeHref="/instructor" links={instructorPanelLinks}>
            <Head title={`حضور — ${session.title}`} />
            <p className="mb-4 text-sm text-slate-600">
                {session.course_title}
                {session.class_title ? ` · ${session.class_title}` : ''}
            </p>
            <Link href="/instructor/session-attendances" className="mb-4 inline-block text-sm text-indigo-600 hover:underline">
                ← بازگشت به لیست جلسات
            </Link>
            <div className="overflow-hidden rounded-xl border border-slate-200">
                <table className="min-w-full text-sm">
                    <thead className="bg-slate-50 text-slate-600">
                        <tr>
                            <th className="px-4 py-2 text-start font-medium">دانشجو</th>
                            <th className="px-4 py-2 text-start font-medium">وضعیت</th>
                            <th className="px-4 py-2 text-start font-medium">یادداشت</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y">
                        {rows.length === 0 ? (
                            <tr>
                                <td colSpan={3} className="px-4 py-6 text-center text-slate-500">
                                    دانشجوی قطعی برای این کلاس نیست.
                                </td>
                            </tr>
                        ) : (
                            rows.map((row) => (
                                <tr key={row.user_id}>
                                    <td className="px-4 py-3">
                                        <div className="font-medium">{row.name}</div>
                                        {row.mobile ? (
                                            <div className="text-xs text-slate-500" dir="ltr">{row.mobile}</div>
                                        ) : null}
                                    </td>
                                    <td className="px-4 py-3">{row.status_label}</td>
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
        </PanelLayout>
    );
}
