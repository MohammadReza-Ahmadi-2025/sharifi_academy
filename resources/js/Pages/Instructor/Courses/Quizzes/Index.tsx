import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { Plus } from 'lucide-react';

type QuizRow = {
    id: number;
    title: string;
    is_published: boolean;
    pass_score: number;
    questions_count: number;
    attempts_count: number;
    edit_url: string;
};

type Props = {
    course: {
        id: number;
        title: string;
        show_url: string;
        create_url: string;
        certificate_enabled: boolean;
        certificate_title: string | null;
        certificate_min_attendance_percent: number | null;
        certificate_min_quiz_score: number | null;
    };
    quizzes: QuizRow[];
    canCreate: boolean;
};

export default function InstructorQuizzesIndex({ course, quizzes, canCreate }: Props) {
    const certForm = useForm({
        certificate_enabled: course.certificate_enabled,
        certificate_title: course.certificate_title ?? '',
        certificate_min_attendance_percent: course.certificate_min_attendance_percent ?? ('' as number | ''),
        certificate_min_quiz_score: course.certificate_min_quiz_score ?? ('' as number | ''),
    });

    const saveCert = (e: FormEvent) => {
        e.preventDefault();
        certForm.transform((data) => ({
            ...data,
            certificate_min_attendance_percent:
                data.certificate_min_attendance_percent === '' ? null : data.certificate_min_attendance_percent,
            certificate_min_quiz_score: data.certificate_min_quiz_score === '' ? null : data.certificate_min_quiz_score,
        }));
        certForm.put(`/instructor/courses/${course.id}/certificate-settings`);
    };

    return (
        <InstructorLayout title={`آزمون‌ها — ${course.title}`} description="کوییز و تنظیمات گواهینامه این دوره">
            <Head title={`آزمون‌ها — ${course.title}`} />

            <div className="mb-5 flex flex-wrap items-center justify-between gap-3">
                <Link href={course.show_url} className="text-sm font-semibold text-primary hover:text-primary-hover">
                    ← نمای دوره
                </Link>
                {canCreate ? (
                    <Link
                        href={course.create_url}
                        className="inline-flex items-center gap-2 rounded-xl bg-primary px-4 py-2.5 text-sm font-semibold text-on-primary"
                    >
                        <Plus className="size-4" aria-hidden />
                        آزمون جدید
                    </Link>
                ) : null}
            </div>

            <form onSubmit={saveCert} className="mb-8 space-y-4 rounded-2xl border border-slate-200 p-5">
                <h2 className="text-base font-bold text-slate-900">گواهینامه پایان دوره</h2>
                <label className="flex items-center gap-2 text-sm">
                    <input
                        type="checkbox"
                        checked={certForm.data.certificate_enabled}
                        onChange={(e) => certForm.setData('certificate_enabled', e.target.checked)}
                    />
                    فعال‌سازی صدور گواهینامه
                </label>
                <div className="grid gap-3 md:grid-cols-3">
                    <label className="block text-sm md:col-span-3">
                        <span className="mb-1 block text-slate-600">عنوان گواهینامه</span>
                        <input
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={certForm.data.certificate_title}
                            onChange={(e) => certForm.setData('certificate_title', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block text-slate-600">حداقل حضور (%)</span>
                        <input
                            type="number"
                            min={0}
                            max={100}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={certForm.data.certificate_min_attendance_percent}
                            onChange={(e) =>
                                certForm.setData(
                                    'certificate_min_attendance_percent',
                                    e.target.value ? Number(e.target.value) : '',
                                )
                            }
                        />
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block text-slate-600">حداقل نمره آزمون</span>
                        <input
                            type="number"
                            min={0}
                            max={100}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={certForm.data.certificate_min_quiz_score}
                            onChange={(e) =>
                                certForm.setData(
                                    'certificate_min_quiz_score',
                                    e.target.value ? Number(e.target.value) : '',
                                )
                            }
                        />
                    </label>
                </div>
                <button
                    type="submit"
                    disabled={certForm.processing}
                    className="rounded-xl border border-slate-200 px-4 py-2 text-sm font-semibold text-slate-800"
                >
                    ذخیره تنظیمات گواهینامه
                </button>
            </form>

            {quizzes.length === 0 ? (
                <p className="rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-4 py-10 text-center text-sm text-slate-500">
                    هنوز آزمونی تعریف نشده است.
                </p>
            ) : (
                <ul className="divide-y divide-slate-100 overflow-hidden rounded-2xl border border-slate-200">
                    {quizzes.map((quiz) => (
                        <li
                            key={quiz.id}
                            className="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                        >
                            <div>
                                <p className="font-semibold text-slate-900">{quiz.title}</p>
                                <p className="mt-1 text-xs text-slate-500">
                                    {quiz.is_published ? 'منتشرشده' : 'پیش‌نویس'} · نمره قبولی {quiz.pass_score} ·{' '}
                                    {quiz.questions_count} سؤال · {quiz.attempts_count} تلاش
                                </p>
                            </div>
                            <div className="flex gap-2">
                                <Link
                                    href={quiz.edit_url}
                                    className="rounded-xl border border-slate-200 px-3.5 py-2 text-sm font-medium text-slate-700"
                                >
                                    ویرایش
                                </Link>
                                <button
                                    type="button"
                                    className="rounded-xl border border-rose-200 px-3.5 py-2 text-sm font-medium text-rose-700"
                                    onClick={() => {
                                        if (confirm('آزمون حذف شود؟')) {
                                            router.delete(`/instructor/courses/${course.id}/quizzes/${quiz.id}`);
                                        }
                                    }}
                                >
                                    حذف
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            )}
        </InstructorLayout>
    );
}
