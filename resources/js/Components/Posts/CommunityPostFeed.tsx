import { PostReactionsBar, type PostReactionsPayload } from '@/Components/Posts/PostReactionsBar';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { formatPersianDateTime } from '@/lib/format-persian-datetime';
import { communityPostPath } from '@/lib/public-entity-urls';
import type { PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import { MessageSquare, UserRound } from 'lucide-react';
import { useState } from 'react';

export type FeedCommentPreview = {
    id: number;
    body: string;
    author_name: string;
    created_at: string | null;
};

export type CommunityPostCard = {
    id: number;
    title: string;
    slug: string;
    url: string;
    excerpt: string | null;
    preview_html?: string | null;
    published_at_label: string | null;
    image_url: string | null;
    author_name: string | null;
    author_avatar: { url: string; alt?: string | null } | null;
    author_profile_url?: string | null;
    reactions?: PostReactionsPayload;
    reactions_total?: number;
    comments_count?: number;
    comments_preview?: FeedCommentPreview[];
};

type Paginator = {
    data: CommunityPostCard[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    posts: Paginator;
    emptyMessage?: string;
    /** Inertia partial reload key that holds this feed (Index: posts, Profile: profile). */
    reloadOnly?: string[];
};

function formatCommentDate(iso: string | null) {
    if (!iso) {
        return '';
    }

    const label = formatPersianDateTime(iso, 'short');

    return label === '—' ? '' : label;
}

function FeedCommentComposer({
    submitUrl,
    only,
}: {
    submitUrl: string;
    only?: string[];
}) {
    const form = useForm({ body: '' });

    return (
        <form
            className="mt-3 space-y-2"
            onSubmit={(event) => {
                event.preventDefault();
                form.post(submitUrl, {
                    preserveScroll: true,
                    ...(only?.length ? { only } : {}),
                    onSuccess: () => form.reset('body'),
                });
            }}
        >
            <textarea
                rows={2}
                value={form.data.body}
                disabled={form.processing}
                placeholder="نظر خود را بنویسید..."
                className="w-full resize-y rounded-xl border border-secondary/70 bg-surface-muted/40 px-3 py-2 text-sm text-neutral-black outline-none transition focus:border-primary/40"
                onChange={(event) => form.setData('body', event.target.value)}
            />
            {form.errors.body ? <p className="text-xs text-rose-600">{form.errors.body}</p> : null}
            <div className="flex items-center justify-between gap-2">
                <p className="text-[11px] text-neutral-black/45">بعد از تأیید نمایش داده می‌شود.</p>
                <button
                    type="submit"
                    disabled={form.processing || form.data.body.trim().length < 3}
                    className="rounded-lg bg-primary px-3 py-1.5 text-xs font-bold text-on-primary disabled:opacity-50"
                >
                    ارسال نظر
                </button>
            </div>
        </form>
    );
}

function CommunityFeedCard({
    post,
    reloadOnly,
}: {
    post: CommunityPostCard;
    reloadOnly?: string[];
}) {
    const { auth } = usePage<PageProps>().props;
    const [commentsOpen, setCommentsOpen] = useState(false);
    const postPath = communityPostPath(post.id, post.slug);
    const preview = post.preview_html?.trim() || post.excerpt?.trim() || '';
    const reactions = post.reactions ?? {
        total: post.reactions_total ?? 0,
        viewer_type: null,
        counts: [],
        types: [],
    };
    const commentsPreview = post.comments_preview ?? [];
    const commentsCount = post.comments_count ?? commentsPreview.length;

    return (
        <article className="overflow-hidden rounded-2xl border border-secondary/40 bg-white shadow-sm">
            <header className="flex items-center gap-3 px-4 pt-4">
                {post.author_profile_url ? (
                    <Link
                        href={post.author_profile_url}
                        className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-primary"
                    >
                        {post.author_avatar?.url ? (
                            <img
                                src={post.author_avatar.url}
                                alt={post.author_avatar.alt ?? post.author_name ?? ''}
                                className="size-full object-cover"
                            />
                        ) : (
                            <UserRound className="size-5" aria-hidden />
                        )}
                    </Link>
                ) : (
                    <span className="flex size-11 shrink-0 items-center justify-center overflow-hidden rounded-full bg-primary/10 text-primary">
                        {post.author_avatar?.url ? (
                            <img
                                src={post.author_avatar.url}
                                alt={post.author_avatar.alt ?? post.author_name ?? ''}
                                className="size-full object-cover"
                            />
                        ) : (
                            <UserRound className="size-5" aria-hidden />
                        )}
                    </span>
                )}
                <div className="min-w-0 flex-1">
                    {post.author_profile_url ? (
                        <Link
                            href={post.author_profile_url}
                            className="truncate text-sm font-bold text-neutral-black hover:text-primary"
                        >
                            {post.author_name || 'کاربر'}
                        </Link>
                    ) : (
                        <p className="truncate text-sm font-bold text-neutral-black">
                            {post.author_name || 'کاربر'}
                        </p>
                    )}
                    {post.published_at_label ? (
                        <p className="text-xs text-neutral-black/45">{post.published_at_label}</p>
                    ) : null}
                </div>
            </header>

            <div className="space-y-3 px-4 py-3">
                <h2 className="text-base font-extrabold leading-snug text-neutral-black">{post.title}</h2>
                {preview ? <SiteRichHtml html={preview} className="site-rich-html--card-preview" /> : null}
            </div>

            {post.image_url?.trim() ? (
                <div className="bg-secondary/10">
                    <img
                        src={post.image_url}
                        alt=""
                        loading="lazy"
                        className="aspect-[16/10] w-full object-cover"
                    />
                </div>
            ) : null}

            <div className="space-y-2 px-4 py-3">
                <PostReactionsBar
                    submitUrl={`${postPath}/reactions`}
                    reactions={reactions}
                    compact
                    only={reloadOnly}
                />

                <div className="flex items-center gap-2 border-t border-secondary/30 pt-2">
                    <button
                        type="button"
                        className={`inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold transition ${
                            commentsOpen
                                ? 'bg-primary/10 text-primary'
                                : 'text-neutral-black/65 hover:bg-surface-muted'
                        }`}
                        onClick={() => setCommentsOpen((value) => !value)}
                    >
                        <MessageSquare className="size-4" aria-hidden />
                        نظر
                        {commentsCount > 0 ? (
                            <span className="text-xs opacity-70">
                                ({commentsCount.toLocaleString('fa-IR')})
                            </span>
                        ) : null}
                    </button>
                    <Link
                        href={post.url}
                        className="rounded-lg px-3 py-2 text-sm font-semibold text-neutral-black/55 hover:bg-surface-muted hover:text-primary"
                    >
                        جزئیات
                    </Link>
                </div>

                {commentsOpen ? (
                    <div className="rounded-xl border border-secondary/40 bg-surface-muted/30 p-3">
                        {commentsPreview.length > 0 ? (
                            <ul className="space-y-2.5">
                                {commentsPreview.map((comment) => (
                                    <li key={comment.id} className="text-sm">
                                        <p className="font-bold text-neutral-black">{comment.author_name}</p>
                                        <p className="mt-0.5 whitespace-pre-wrap text-neutral-black/75">
                                            {comment.body}
                                        </p>
                                        {comment.created_at ? (
                                            <p className="mt-1 text-[11px] text-neutral-black/40">
                                                {formatCommentDate(comment.created_at)}
                                            </p>
                                        ) : null}
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p className="text-xs text-neutral-black/50">هنوز نظری تأیید نشده است.</p>
                        )}

                        {auth.user ? (
                            <FeedCommentComposer submitUrl={`${postPath}/comments`} only={reloadOnly} />
                        ) : (
                            <p className="mt-3 text-xs text-neutral-black/55">
                                برای نظر دادن{' '}
                                <Link href="/login" className="font-bold text-primary hover:underline">
                                    وارد شوید
                                </Link>
                            </p>
                        )}

                        {commentsCount > commentsPreview.length ? (
                            <Link
                                href={`${post.url}#post-comments`}
                                className="mt-3 inline-block text-xs font-semibold text-primary hover:underline"
                            >
                                مشاهده همه نظرات
                            </Link>
                        ) : null}
                    </div>
                ) : null}
            </div>
        </article>
    );
}

export function CommunityPostFeed({
    posts,
    emptyMessage = 'هنوز پستی تأیید نشده است.',
    reloadOnly = ['posts'],
}: Props) {
    if (posts.data.length === 0) {
        return (
            <p className="rounded-2xl border border-dashed border-secondary/70 px-6 py-14 text-center text-sm text-neutral-black/55">
                {emptyMessage}
            </p>
        );
    }

    return (
        <>
            <div className="mx-auto flex max-w-xl flex-col gap-4">
                {posts.data.map((post) => (
                    <CommunityFeedCard key={post.id} post={post} reloadOnly={reloadOnly} />
                ))}
            </div>

            {posts.last_page > 1 ? (
                <nav className="mt-8 flex flex-wrap justify-center gap-2" aria-label="صفحه‌بندی">
                    {posts.links.map((link, index) => {
                        if (!link.url) {
                            return (
                                <span
                                    key={`${link.label}-${index}`}
                                    className="rounded-lg px-3 py-1.5 text-sm text-neutral-black/35"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            );
                        }

                        return (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url}
                                className={`rounded-lg px-3 py-1.5 text-sm font-medium transition ${
                                    link.active
                                        ? 'bg-primary text-on-primary'
                                        : 'bg-secondary/20 text-neutral-black hover:bg-secondary/35'
                                }`}
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        );
                    })}
                </nav>
            ) : null}
        </>
    );
}
