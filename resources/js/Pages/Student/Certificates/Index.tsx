import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Award } from 'lucide-react';

type CertificateRow = {
    id: number;
    code: string;
    title: string;
    course_title: string | null;
    issued_at: string | null;
    show_url: string;
};

type EligibleCourse = {
    id: number;
    title: string;
    eligible: boolean;
    reasons: string[];
    claim_url: string;
};

type Props = {
    certificates: CertificateRow[];
    eligibleCourses: EligibleCourse[];
};

export default function StudentCertificatesIndex({ certificates, eligibleCourses }: Props) {
    return (
        <StudentLayout title="گواهینامه‌ها" description="مدارک پایان دوره و درخواست صدور" contentVariant="plain">
            <Head title="گواهینامه‌ها" />

            <section className="mb-6 overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-sm">
                <div className="border-b border-slate-100 px-5 py-4">
                    <h2 className="text-base font-bold text-slate-900">گواهینامه‌های صادرشده</h2>
                </div>
                {certificates.length === 0 ? (
                    <div className="flex flex-col items-center gap-2 px-6 py-12 text-center">
                        <Award className="size-8 text-primary" aria-hidden />
                        <p className="text-sm text-slate-500">هنوز گواهینامه‌ای ندارید.</p>
                    </div>
                ) : (
                    <ul className="divide-y divide-slate-100">
                        {certificates.map((row) => (
                            <li
                                key={row.id}
                                className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p className="font-semibold text-slate-900">{row.title}</p>
                                    <p className="mt-1 text-xs text-slate-500">
                                        {row.course_title} · کد {row.code}
                                    </p>
                                </div>
                                <Link
                                    href={row.show_url}
                                    className="rounded-xl bg-[rgb(12_42_74)] px-3.5 py-2 text-sm font-medium text-white"
                                >
                                    مشاهده
                                </Link>
                            </li>
                        ))}
                    </ul>
                )}
            </section>

            {eligibleCourses.length > 0 ? (
                <section className="overflow-hidden rounded-3xl border border-slate-200/90 bg-white shadow-sm">
                    <div className="border-b border-slate-100 px-5 py-4">
                        <h2 className="text-base font-bold text-slate-900">دوره‌های دارای گواهینامه</h2>
                    </div>
                    <ul className="divide-y divide-slate-100">
                        {eligibleCourses.map((course) => (
                            <li
                                key={course.id}
                                className="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between"
                            >
                                <div>
                                    <p className="font-semibold text-slate-900">{course.title}</p>
                                    {!course.eligible && course.reasons.length > 0 ? (
                                        <p className="mt-1 text-xs text-amber-800">{course.reasons[0]}</p>
                                    ) : (
                                        <p className="mt-1 text-xs text-emerald-700">شرایط صدور برقرار است.</p>
                                    )}
                                </div>
                                <button
                                    type="button"
                                    disabled={!course.eligible}
                                    className="rounded-xl bg-primary px-3.5 py-2 text-sm font-semibold text-on-primary disabled:opacity-50"
                                    onClick={() => router.post(course.claim_url)}
                                >
                                    دریافت گواهینامه
                                </button>
                            </li>
                        ))}
                    </ul>
                </section>
            ) : null}
        </StudentLayout>
    );
}
