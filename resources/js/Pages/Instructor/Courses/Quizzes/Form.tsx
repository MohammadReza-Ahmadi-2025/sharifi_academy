import InstructorLayout from '@/Layouts/InstructorLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

type Option = { label: string; is_correct: boolean };
type Question = { prompt: string; points: number; options: Option[] };

type QuizForm = {
    id?: number;
    title: string;
    description: string;
    pass_score: number;
    is_published: boolean;
    time_limit_minutes: number | '';
    max_attempts: number | '';
    questions: Question[];
};

type Props = {
    course: { id: number; title: string };
    quiz: QuizForm | null;
};

const emptyQuestion = (): Question => ({
    prompt: '',
    points: 1,
    options: [
        { label: '', is_correct: true },
        { label: '', is_correct: false },
    ],
});

export default function InstructorQuizForm({ course, quiz }: Props) {
    const form = useForm<QuizForm>({
        title: quiz?.title ?? '',
        description: quiz?.description ?? '',
        pass_score: quiz?.pass_score ?? 70,
        is_published: quiz?.is_published ?? false,
        time_limit_minutes: quiz?.time_limit_minutes ?? '',
        max_attempts: quiz?.max_attempts ?? '',
        questions: quiz?.questions?.length ? quiz.questions : [emptyQuestion()],
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        const payload = {
            ...form.data,
            time_limit_minutes: form.data.time_limit_minutes === '' ? null : form.data.time_limit_minutes,
            max_attempts: form.data.max_attempts === '' ? null : form.data.max_attempts,
        };
        form.transform(() => payload);
        if (quiz?.id) {
            form.put(`/instructor/courses/${course.id}/quizzes/${quiz.id}`);
        } else {
            form.post(`/instructor/courses/${course.id}/quizzes`);
        }
    };

    const updateQuestion = (index: number, patch: Partial<Question>) => {
        const next = [...form.data.questions];
        next[index] = { ...next[index], ...patch };
        form.setData('questions', next);
    };

    const updateOption = (qIndex: number, oIndex: number, patch: Partial<Option>) => {
        const next = [...form.data.questions];
        const options = [...next[qIndex].options];
        if (patch.is_correct) {
            options.forEach((option, i) => {
                options[i] = { ...option, is_correct: i === oIndex };
            });
        } else {
            options[oIndex] = { ...options[oIndex], ...patch };
        }
        next[qIndex] = { ...next[qIndex], options };
        form.setData('questions', next);
    };

    return (
        <InstructorLayout
            title={quiz ? `ویرایش آزمون — ${course.title}` : `آزمون جدید — ${course.title}`}
            description="سؤالات چندگزینه‌ای با یک پاسخ صحیح"
        >
            <Head title={quiz ? 'ویرایش آزمون' : 'آزمون جدید'} />

            <div className="mb-5">
                <Link
                    href={`/instructor/courses/${course.id}/quizzes`}
                    className="text-sm font-semibold text-primary hover:text-primary-hover"
                >
                    ← لیست آزمون‌ها
                </Link>
            </div>

            <form onSubmit={submit} className="space-y-6">
                <div className="grid gap-4 md:grid-cols-2">
                    <label className="block text-sm">
                        <span className="mb-1 block font-medium text-slate-700">عنوان</span>
                        <input
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                        />
                        {form.errors.title ? <p className="mt-1 text-xs text-rose-600">{form.errors.title}</p> : null}
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block font-medium text-slate-700">نمره قبولی (%)</span>
                        <input
                            type="number"
                            min={1}
                            max={100}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={form.data.pass_score}
                            onChange={(e) => form.setData('pass_score', Number(e.target.value))}
                        />
                    </label>
                    <label className="block text-sm md:col-span-2">
                        <span className="mb-1 block font-medium text-slate-700">توضیحات</span>
                        <textarea
                            rows={2}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                        />
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block font-medium text-slate-700">محدودیت زمان (دقیقه)</span>
                        <input
                            type="number"
                            min={1}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={form.data.time_limit_minutes}
                            onChange={(e) =>
                                form.setData('time_limit_minutes', e.target.value ? Number(e.target.value) : '')
                            }
                        />
                    </label>
                    <label className="block text-sm">
                        <span className="mb-1 block font-medium text-slate-700">سقف تلاش</span>
                        <input
                            type="number"
                            min={1}
                            className="w-full rounded-xl border border-slate-200 px-3 py-2"
                            value={form.data.max_attempts}
                            onChange={(e) => form.setData('max_attempts', e.target.value ? Number(e.target.value) : '')}
                        />
                    </label>
                    <label className="flex items-center gap-2 text-sm md:col-span-2">
                        <input
                            type="checkbox"
                            checked={form.data.is_published}
                            onChange={(e) => form.setData('is_published', e.target.checked)}
                        />
                        انتشار برای دانشجویان ثبت‌نام‌شده
                    </label>
                </div>

                <div className="space-y-4">
                    <div className="flex items-center justify-between">
                        <h2 className="text-base font-bold text-slate-900">سؤالات</h2>
                        <button
                            type="button"
                            className="rounded-xl border border-slate-200 px-3 py-1.5 text-sm"
                            onClick={() => form.setData('questions', [...form.data.questions, emptyQuestion()])}
                        >
                            افزودن سؤال
                        </button>
                    </div>

                    {form.data.questions.map((question, qIndex) => (
                        <div key={qIndex} className="rounded-2xl border border-slate-200 p-4">
                            <div className="mb-3 flex items-start justify-between gap-3">
                                <label className="block flex-1 text-sm">
                                    <span className="mb-1 block font-medium">سؤال {qIndex + 1}</span>
                                    <textarea
                                        rows={2}
                                        className="w-full rounded-xl border border-slate-200 px-3 py-2"
                                        value={question.prompt}
                                        onChange={(e) => updateQuestion(qIndex, { prompt: e.target.value })}
                                    />
                                </label>
                                {form.data.questions.length > 1 ? (
                                    <button
                                        type="button"
                                        className="text-xs text-rose-600"
                                        onClick={() =>
                                            form.setData(
                                                'questions',
                                                form.data.questions.filter((_, i) => i !== qIndex),
                                            )
                                        }
                                    >
                                        حذف
                                    </button>
                                ) : null}
                            </div>
                            <div className="space-y-2">
                                {question.options.map((option, oIndex) => (
                                    <div key={oIndex} className="flex items-center gap-2">
                                        <input
                                            type="radio"
                                            name={`correct-${qIndex}`}
                                            checked={option.is_correct}
                                            onChange={() => updateOption(qIndex, oIndex, { is_correct: true })}
                                        />
                                        <input
                                            className="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm"
                                            placeholder={`گزینه ${oIndex + 1}`}
                                            value={option.label}
                                            onChange={(e) => updateOption(qIndex, oIndex, { label: e.target.value })}
                                        />
                                    </div>
                                ))}
                                <button
                                    type="button"
                                    className="text-xs font-medium text-primary"
                                    onClick={() =>
                                        updateQuestion(qIndex, {
                                            options: [...question.options, { label: '', is_correct: false }],
                                        })
                                    }
                                >
                                    + گزینه
                                </button>
                            </div>
                            {form.errors[`questions.${qIndex}.options`] ? (
                                <p className="mt-2 text-xs text-rose-600">{form.errors[`questions.${qIndex}.options`]}</p>
                            ) : null}
                        </div>
                    ))}
                </div>

                <button
                    type="submit"
                    disabled={form.processing}
                    className="rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary disabled:opacity-60"
                >
                    ذخیره آزمون
                </button>
            </form>
        </InstructorLayout>
    );
}
