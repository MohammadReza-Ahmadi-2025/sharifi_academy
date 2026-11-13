import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { blogPostPath } from '@/lib/public-entity-urls';
import { Link } from '@inertiajs/react';
import { CalendarDays } from 'lucide-react';

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
        return (
            <p className="rounded-2xl border border-dashed border-secondary/70 px-6 py-14 text-center text-sm text-neutral-black/55">
                {emptyMessage}
            </p>
        );
    }

    return (
        <>
            <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {posts.data.map((post) => (
                    <article key={post.id} className="group flex h-full flex-col">
                        <Link href={blogPostPath(post.id, post.slug)} className="block overflow-hidden rounded-2xl bg-secondary/15">
                            <div className="aspect-[16/10] overflow-hidden">
                                <img
                                    src={post.image_url?.trim() || '/banner.png'}
                                    alt=""
                                    loading="lazy"
                                    className="size-full object-cover transition duration-500 group-hover:scale-[1.03]"
                                />
                            </div>
                        </Link>

                        <div className="flex flex-1 flex-col pt-4">
                            <div className="mb-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-neutral-black/45">
                                {post.categories[0] ? (
                                    <Link
                                        href={`/blog/categories/${post.categories[0].slug}`}
                                        className="font-bold text-primary hover:underline"
                                    >
                                        {post.categories[0].title}
                                    </Link>
                                ) : null}
                                {post.published_at_label ? (
                                    <span className="inline-flex items-center gap-1">
                                        <CalendarDays className="size-3.5" aria-hidden />
                                        {post.published_at_label}
                                    </span>
                                ) : null}
                            </div>

                            <h2 className="text-base font-extrabold leading-snug text-neutral-black sm:text-[1.05rem]">
                                <Link href={blogPostPath(post.id, post.slug)} className="transition hover:text-primary">
                                    {post.title}
                                </Link>
                            </h2>

                            {post.excerpt?.trim() ? (
                                <SiteRichHtml html={post.excerpt} className="site-rich-html--card-preview mt-2" />
                            ) : null}
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
                                    className="rounded-lg px-3 py-2 text-sm text-neutral-black/35"
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            );
                        }

                        return (
                            <Link
                                key={index}
                                href={link.url}
                                className={`rounded-lg px-3 py-2 text-sm font-semibold transition ${
                                    link.active
                                        ? 'bg-primary text-on-primary'
                                        : 'border border-secondary/55 text-neutral-black/70 hover:border-primary/30'
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
