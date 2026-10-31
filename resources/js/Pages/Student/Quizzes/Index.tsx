import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link } from '@inertiajs/react';
import { ListChecks } from 'lucide-react';

type QuizRow = {
    id: number;
    title: string;
    course_title: string | null;
    pass_score: number;
    latest_score: number | null;
    latest_passed: boolean | null;
    show_url: string;
};

type Props = { quizzes: QuizRow[] };

export default function StudentQuizzesIndex({ quizzes }: Props) {
    return (
        <StudentLayout title="آزمون‌ها" description="کوییز دوره‌هایی که در آن‌ها ثبت‌نام قطعی دارید">
            <Head title="آزمون‌ها" />

            {quizzes.length === 0 ? (
                <div className="flex flex-col items-center gap-3 rounded-2xl border border-dashed border-slate-200 bg-slate-50/60 px-6 py-14 text-center">
                    <ListChecks className="size-8 text-primary" aria-hidden />
                    <p className="text-sm font-semibold text-slate-800">آزمون فعالی نیست</p>
                </div>
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
                                    {quiz.course_title} · قبولی از {quiz.pass_score}
                                    {quiz.latest_score !== null
                                        ? ` · آخرین نمره ${quiz.latest_score}${quiz.latest_passed ? ' (قبول)' : ''}`
                                        : ''}
                                </p>
                            </div>
                            <Link
                                href={quiz.show_url}
                                className="rounded-xl bg-[rgb(12_42_74)] px-3.5 py-2 text-sm font-medium text-white"
                            >
                                شرکت در آزمون
                            </Link>
                        </li>
                    ))}
                </ul>
            )}
        </StudentLayout>
    );
}
