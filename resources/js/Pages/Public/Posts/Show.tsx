import {
    CourseCommentsSection,
    type PublicCourseComment,
} from '@/Components/Courses/CourseCommentsSection';
import { PostReactionsBar, type PostReactionsPayload } from '@/Components/Posts/PostReactionsBar';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';
import { communityPostPath } from '@/lib/public-entity-urls';
import { Link } from '@inertiajs/react';
import { UserRound } from 'lucide-react';

type Props = {
    post: {
        id: number;
        title: string;
        slug: string;
        excerpt: string | null;
        content: string | null;
        published_at_label: string | null;
        image_url: string | null;
        author_name: string | null;
        author_profile_url: string | null;
        author_avatar: { url: string; alt?: string | null } | null;
        seo: CatalogSeo;
        comments: PublicCourseComment[];
        comments_summary: { count: number; average_rating: number | null };
        reactions: PostReactionsPayload;
    };
    seo: CatalogSeo;
    structuredData: {
        breadcrumb: Record<string, unknown>;
        webPage: Record<string, unknown>;
    };
};

export default function CommunityPostShow({ post, seo, structuredData }: Props) {
    const postPath = communityPostPath(post.id, post.slug);

    return (
        <AppLayout fullWidth mainClassName="bg-surface-muted">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage]}
            />

            <article className="mx-auto max-w-xl px-4 py-8 sm:px-6 lg:py-10">
                <SitePublicBreadcrumb
                    className="mb-6"
                    items={[
                        { label: 'خانه', href: '/' },
                        { label: 'پست‌ها', href: '/posts' },
                        { label: post.title },
                    ]}
                />

                <div className="overflow-hidden rounded-2xl border border-secondary/40 bg-white shadow-sm">
                    <header className="flex items-center gap-3 px-4 pt-4 sm:px-5 sm:pt-5">
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
                                <UserRound className="size-5" aria-hidden />
                            </span>
                        )}
                        <div className="min-w-0">
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

                    <div className="space-y-3 px-4 py-4 sm:px-5">
                        <h1 className="text-xl font-extrabold leading-snug text-neutral-black">{post.title}</h1>
                        {post.excerpt?.trim() ? (
                            <SiteRichHtml html={post.excerpt} className="site-rich-html--card-preview" />
                        ) : null}
                    </div>

                    {post.image_url?.trim() ? (
                        <div className="bg-secondary/10">
                            <img src={post.image_url} alt="" className="aspect-[16/10] w-full object-cover" />
                        </div>
                    ) : null}

                    {post.content?.trim() ? (
                        <div className="px-4 py-5 sm:px-5">
                            <SiteRichHtml html={post.content} />
                        </div>
                    ) : null}

                    <div className="px-4 pb-4 sm:px-5">
                        <PostReactionsBar
                            submitUrl={`${postPath}/reactions`}
                            reactions={post.reactions}
                            only={['post']}
                        />
                    </div>
                </div>

                <section className="mt-8">
                    <CourseCommentsSection
                        submitUrl={`${postPath}/comments`}
                        comments={post.comments}
                        summary={post.comments_summary}
                        sectionId="post-comments"
                        title="نظرات"
                        showRating={false}
                        placeholder="نظر خود را درباره این پست بنویسید..."
                    />
                </section>
            </article>
        </AppLayout>
    );
}
