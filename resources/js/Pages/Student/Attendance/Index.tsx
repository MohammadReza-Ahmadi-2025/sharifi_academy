import { attendanceStatusClass } from '@/Components/Student/SessionActions';
import StudentLayout from '@/Layouts/StudentLayout';
import { cn } from '@/lib/utils';
import { Head } from '@inertiajs/react';
import { ClipboardCheck } from 'lucide-react';

type AttendanceRow = {
    id: number;
    status: string;
    status_label: string;
    duration_minutes: number;
    session_title: string;
    starts_at: string | null;
    course_title: string;
    class_name: string;
    note: string;
};

type Summary = {
    attendance_rate: number | null;
    present_count: number;
    absent_count: number;
    records_total: number;
};

type Props = {
    summary: Summary;
    attendances: AttendanceRow[];
};

export default function StudentAttendanceIndex({ summary, attendances }: Props) {
    const cards = [
        {
            label: 'نرخ حضور',
            value: summary.attendance_rate === null ? '—' : `${summary.attendance_rate}%`,
        },
        { label: 'حاضر', value: String(summary.present_count) },
        { label: 'غایب', value: String(summary.absent_count) },
        { label: 'کل رکوردها', value: String(summary.records_total) },
    ];

    return (
        <StudentLayout
            title="حضور و غیاب"
            description="وضعیت حضور شما در جلسات کلاس‌های قطعی"
            contentVariant="plain"
        >
            <Head title="حضور و غیاب" />

            <div className="mb-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                {cards.map((card) => (
                    <div key={card.label} className="student-stat rounded-2xl p-4">
                        <p className="text-xs font-medium text-slate-500">{card.label}</p>
                        <p className="mt-1 text-2xl font-bold tabular-nums tracking-tight text-slate-900">
                            {card.value}
                        </p>
                    </div>
                ))}
            </div>

            {attendances.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-3xl border border-dashed border-slate-200 bg-white px-6 py-14 text-center shadow-sm">
                    <div className="flex size-12 items-center justify-center rounded-2xl bg-primary/10 text-primary">
                        <ClipboardCheck className="size-6" aria-hidden />
                    </div>
                    <p className="text-sm font-semibold text-slate-800">رکورد حضوری نیست</p>
                    <p className="max-w-sm text-xs text-slate-500">
                        پس از برگزاری جلسات و ثبت حضور، وضعیت شما اینجا نمایش داده می‌شود.
                    </p>
                </div>
            ) : (
                <div className="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-[0_12px_32px_rgb(15_42_74_/_0.05)]">
                    <table className="min-w-full text-sm">
                        <thead className="bg-slate-50 text-slate-600">
                            <tr>
                                <th className="px-4 py-3 text-start font-medium">جلسه</th>
                                <th className="px-4 py-3 text-start font-medium">وضعیت</th>
                                <th className="px-4 py-3 text-start font-medium">مدت</th>
                                <th className="px-4 py-3 text-start font-medium">یادداشت</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100">
                            {attendances.map((row) => (
                                <tr key={row.id} className="hover:bg-slate-50/80">
                                    <td className="px-4 py-3">
                                        <div className="font-medium text-slate-900">{row.session_title}</div>
                                        <div className="mt-0.5 text-xs text-slate-500">
                                            {row.course_title}
                                            {row.class_name ? ` · ${row.class_name}` : ''}
                                            {row.starts_at
                                                ? ` · ${new Date(row.starts_at).toLocaleString('fa-IR')}`
                                                : ''}
                                        </div>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span
                                            className={cn(
                                                'inline-flex rounded-md px-2 py-0.5 text-[11px] font-semibold ring-1 ring-inset',
                                                attendanceStatusClass(row.status),
                                            )}
                                        >
                                            {row.status_label}
                                        </span>
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">
                                        {row.duration_minutes > 0 ? `${row.duration_minutes} دقیقه` : '—'}
                                    </td>
                                    <td className="px-4 py-3 text-slate-600">{row.note || '—'}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </StudentLayout>
    );
}
