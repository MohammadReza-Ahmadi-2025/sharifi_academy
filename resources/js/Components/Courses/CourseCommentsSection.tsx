import { Textarea } from '@/Components/Admin/shared/ui/textarea';
import type { PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { MessageSquare, Star } from 'lucide-react';
import { useState } from 'react';

export type PublicCourseComment = {
    id: number;
    body: string;
    author_name: string;
    rating: number | null;
    created_at: string | null;
    children: PublicCourseComment[];
};

type Props = {
    courseSlug: string;
    comments: PublicCourseComment[];
    summary: { count: number; average_rating: number | null };
};

function formatDate(iso: string | null) {
    if (!iso) {
        return '';
    }

    return new Date(iso).toLocaleDateString('fa-IR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function RatingStars({ value, size = 'sm' }: { value: number; size?: 'sm' | 'lg' }) {
    const starClass = size === 'lg' ? 'size-4' : 'size-3.5';

    return (
        <span className="inline-flex gap-0.5 text-amber-500" aria-label={`امتیاز ${value} از ۵`}>
            {Array.from({ length: 5 }).map((_, index) => (
                <Star
                    key={index}
                    className={`${starClass} ${index < value ? 'fill-current' : 'fill-none opacity-35'}`}
                />
            ))}
        </span>
    );
}

function CommentItem({ comment, depth = 0 }: { comment: PublicCourseComment; depth?: number }) {
    return (
        <article
            className={`rounded-2xl border border-secondary/80 bg-neutral-white p-4 shadow-sm sm:p-5 lg:p-6 ${
                depth > 0 ? 'mr-4 mt-3 border-dashed bg-surface-muted/40 lg:mr-6' : ''
            }`}
        >
            <div className="flex flex-wrap items-center justify-between gap-2">
                <p className="text-base font-bold text-neutral-black lg:text-lg">{comment.author_name}</p>
                <div className="flex items-center gap-2 text-xs text-neutral-black/60 sm:text-sm">
                    {comment.rating ? <RatingStars value={comment.rating} size="lg" /> : null}
                    <time dateTime={comment.created_at ?? undefined}>{formatDate(comment.created_at)}</time>
                </div>
            </div>
            <p className="mt-3 whitespace-pre-wrap text-sm leading-relaxed text-neutral-black/80 sm:text-base lg:leading-8">
                {comment.body}
            </p>
            {comment.children.length > 0 && (
                <div className="mt-2 space-y-0">
                    {comment.children.map((child) => (
                        <CommentItem key={child.id} comment={child} depth={depth + 1} />
                    ))}
                </div>
            )}
        </article>
    );
}

function CommentForm({
    courseSlug,
    rating,
    setRating,
}: {
    courseSlug: string;
    rating: number | null;
    setRating: (value: number | null) => void;
}) {
    const form = useForm({ body: '', rating: null as number | null });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        form.transform((data) => ({ ...data, rating }));
        form.post(`/courses/${courseSlug}/comments`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setRating(null);
            },
        });
    };

    return (
        <form onSubmit={submit} className="rounded-2xl border border-secondary bg-neutral-white p-5 shadow-sm lg:p-6">
            <p className="mb-4 text-base font-extrabold text-neutral-black">ثبت نظر شما</p>
            <div className="mb-4 flex flex-wrap items-center gap-2">
                <span className="text-xs text-neutral-black/60 sm:text-sm">امتیاز (اختیاری):</span>
                {Array.from({ length: 5 }).map((_, index) => {
                    const star = index + 1;
                    const active = rating !== null && star <= rating;

                    return (
                        <button
                            key={star}
                            type="button"
                            aria-label={`امتیاز ${star}`}
                            className="rounded p-0.5 text-amber-500 transition hover:scale-110"
                            onClick={() => setRating(star)}
                        >
                            <Star className={`size-6 ${active ? 'fill-current' : 'fill-none opacity-35'}`} />
                        </button>
                    );
                })}
            </div>
            <Textarea
                plain
                rows={5}
                value={form.data.body}
                disabled={form.processing}
                placeholder="تجربه خود از این دوره را بنویسید..."
                className="min-h-[8rem] text-base"
                onChange={(e) => form.setData('body', e.target.value)}
            />
            {form.errors.body && <p className="mt-2 text-sm text-rose-600">{form.errors.body}</p>}
            <button
                type="submit"
                disabled={form.processing}
                className="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-primary px-5 py-3 text-sm font-bold text-on-primary shadow-md transition hover:opacity-95 disabled:opacity-60"
            >
                ارسال نظر
            </button>
            <p className="mt-3 text-center text-xs text-neutral-black/55 sm:text-sm">
                نظر پس از بررسی تیم آموزش منتشر می‌شود.
            </p>
        </form>
    );
}

export function CourseCommentsSection({ courseSlug, comments, summary }: Props) {
    const { auth } = usePage<PageProps>().props;
    const [rating, setRating] = useState<number | null>(null);

    return (
        <section id="course-comments" className="scroll-mt-24">
            <div className="mb-8">
                <h2 className="flex items-center gap-2 text-xl font-extrabold text-neutral-black sm:text-2xl">
                    <MessageSquare className="size-6 text-primary" />
                    نظرات شرکت‌کنندگان
                </h2>
                {summary.count > 0 && (
                    <p className="mt-2 text-sm text-neutral-black/65 sm:text-base">
                        {summary.count.toLocaleString('fa-IR')} نظر
                        {summary.average_rating !== null
                            ? ` · میانگین ${summary.average_rating.toLocaleString('fa-IR')} از ۵`
                            : ''}
                    </p>
                )}
            </div>

            <div className="flex flex-col gap-8 lg:grid lg:grid-cols-[minmax(17rem,20rem)_minmax(0,1fr)] lg:items-start lg:gap-10">
                {/* راست در RTL: فرم ثبت نظر */}
                <aside className="order-2 lg:order-none lg:sticky lg:top-24">
                    {auth.user ? (
                        <CommentForm courseSlug={courseSlug} rating={rating} setRating={setRating} />
                    ) : (
                        <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-8 text-center text-sm text-neutral-black/70 sm:text-base">
                            برای ثبت نظر{' '}
                            <Link href="/login" className="font-bold text-primary hover:underline">
                                وارد حساب
                            </Link>{' '}
                            شوید.
                        </p>
                    )}
                </aside>

                {/* چپ در RTL: لیست نظرات — عرض کامل ستون */}
                <div className="order-1 min-w-0 lg:order-none">
                    {comments.length === 0 ? (
                        <p className="rounded-2xl border border-dashed border-secondary bg-neutral-white px-4 py-14 text-center text-sm text-neutral-black/65 sm:text-base">
                            هنوز نظری ثبت نشده است.
                        </p>
                    ) : (
                        <div className="space-y-5 lg:space-y-6">
                            {comments.map((comment) => (
                                <CommentItem key={comment.id} comment={comment} />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </section>
    );
}
