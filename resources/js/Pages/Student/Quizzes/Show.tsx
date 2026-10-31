import StudentLayout from '@/Layouts/StudentLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

type Option = { id: number; label: string };
type Question = { id: number; prompt: string; points: number; options: Option[] };

type Props = {
    quiz: {
        id: number;
        title: string;
        description: string | null;
        pass_score: number;
        course_title: string | null;
        questions: Question[];
        latest_attempt: { score: number; passed: boolean; submitted_at: string | null } | null;
    };
};

export default function StudentQuizShow({ quiz }: Props) {
    const { flash } = usePage().props as { flash?: { success?: string } };
    const [answers, setAnswers] = useState<Record<number, number>>({});
    const form = useForm({ answers: {} as Record<number, number> });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.setData('answers', answers);
        form.transform(() => ({ answers }));
        form.post(`/student/quizzes/${quiz.id}/attempts`);
    };

    return (
        <StudentLayout title={quiz.title} description={quiz.course_title ?? 'آزمون دوره'}>
            <Head title={quiz.title} />

            <div className="mb-5">
                <Link href="/student/quizzes" className="text-sm font-semibold text-primary hover:text-primary-hover">
                    ← آزمون‌ها
                </Link>
            </div>

            {flash?.success ? (
                <p className="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-900">
                    {flash.success}
                </p>
            ) : null}

            {quiz.latest_attempt ? (
                <p className="mb-4 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    آخرین نتیجه: نمره {quiz.latest_attempt.score}
                    {quiz.latest_attempt.passed ? ' — قبول' : ` — حد قبولی ${quiz.pass_score}`}
                </p>
            ) : null}

            {quiz.description ? <p className="mb-4 text-sm text-slate-600">{quiz.description}</p> : null}

            <form onSubmit={submit} className="space-y-5">
                {quiz.questions.map((question, index) => (
                    <fieldset key={question.id} className="rounded-2xl border border-slate-200 p-4">
                        <legend className="px-1 text-sm font-semibold text-slate-900">
                            {index + 1}. {question.prompt}
                        </legend>
                        <div className="mt-3 space-y-2">
                            {question.options.map((option) => (
                                <label key={option.id} className="flex items-center gap-2 text-sm text-slate-700">
                                    <input
                                        type="radio"
                                        name={`q-${question.id}`}
                                        checked={answers[question.id] === option.id}
                                        onChange={() => setAnswers((prev) => ({ ...prev, [question.id]: option.id }))}
                                    />
                                    {option.label}
                                </label>
                            ))}
                        </div>
                    </fieldset>
                ))}

                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary disabled:opacity-60"
                >
                    ارسال پاسخ‌ها
                </button>
            </form>
        </StudentLayout>
    );
}
