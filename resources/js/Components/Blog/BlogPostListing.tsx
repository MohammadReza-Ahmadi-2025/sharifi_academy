import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { Link } from '@inertiajs/react';
import { CalendarDays, ChevronLeft } from 'lucide-react';

export type BlogPostCard = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    published_at_label: string | null;
    image_url: string | null;
    categories: { id: number; title: string; slug: string }[];
};

type Paginator = {
    data: BlogPostCard[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    posts: Paginator;
    emptyMessage?: string;
};

export function BlogPostListing({ posts, emptyMessage = 'موردی برای نمایش نیست.' }: Props) {
    if (posts.data.length === 0) {
        return <p className="text-center text-sm text-neutral-black/55">{emptyMessage}</p>;
    }

    return (
        <>
            <div className="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 lg:gap-6">
                {posts.data.map((post) => (
                    <article
                        key={post.id}
                        className="flex h-full flex-col overflow-hidden rounded-2xl border border-secondary/50 bg-neutral-white shadow-[0_2px_12px_-4px_rgba(0,72,158,0.08)]"
                    >
                        <Link href={`/blog/${post.slug}`} className="block p-2">
                            <div className="aspect-[16/10] overflow-hidden rounded-xl bg-secondary/15">
                                <img
                                    src={post.image_url ?? '/banner.png'}
                                    alt=""
                                    className="size-full object-cover transition duration-500 hover:scale-[1.03]"
                                />
                            </div>
                        </Link>
                        <div className="flex flex-1 flex-col px-4 pb-4 pt-2">
                            {post.published_at_label ? (
                                <p className="mb-2 flex items-center gap-1.5 text-xs text-neutral-black/50">
                                    <CalendarDays className="size-3.5" aria-hidden />
                                    {post.published_at_label}
                                </p>
                            ) : null}
                            <h2 className="text-base font-bold leading-snug text-neutral-black">
                                <Link href={`/blog/${post.slug}`} className="transition hover:text-primary">
                                    {post.title}
                                </Link>
                            </h2>
                            {post.excerpt?.trim() ? (
                                <SiteRichHtml html={post.excerpt} className="site-rich-html--card-preview mt-2" />
                            ) : null}
                            <Link
                                href={`/blog/${post.slug}`}
                                className="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-primary"
                            >
                                ادامه مطلب
                                <ChevronLeft className="size-4" aria-hidden />
                            </Link>
                        </div>
                    </article>
                ))}
            </div>

            {posts.last_page > 1 ? (
                <nav className="mt-10 flex flex-wrap justify-center gap-2" aria-label="صفحه‌بندی">
                    {posts.links.map((link, index) => {
                        if (!link.url) {
                            return (
                                <span
                                    key={index}
                                    className="rounded-lg px-3 py-2 text-sm text-neutral-black/40"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            );
                        }

                        return (
                            <Link
                                key={index}
                                href={link.url}
                                className={`rounded-lg px-3 py-2 text-sm transition ${
                                    link.active
                                        ? 'bg-primary text-on-primary'
                                        : 'border border-secondary/50 text-neutral-black/70 hover:border-primary/30'
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
