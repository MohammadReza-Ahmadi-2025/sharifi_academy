import { BlogPostContent, estimateReadingMinutes } from '@/Components/Blog/BlogPostContent';
import {
    CourseCommentsSection,
    type PublicCourseComment,
} from '@/Components/Courses/CourseCommentsSection';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';
import { blogPostPath } from '@/lib/public-entity-urls';
import { Link } from '@inertiajs/react';
import { ArrowRight, CalendarDays, Clock3, Tag } from 'lucide-react';
import { useMemo } from 'react';

type Props = {
    blog_list_label: string;
    post: {
        id: number;
        title: string;
        slug: string;
        excerpt: string | null;
        content: string | null;
        published_at_label: string | null;
        image_url: string | null;
        author_name: string | null;
        categories: { id: number; title: string; slug: string }[];
        seo: CatalogSeo;
        comments: PublicCourseComment[];
        comments_summary: { count: number; average_rating: number | null };
    };
    structuredData: {
        breadcrumb: Record<string, unknown>;
        blogPosting: Record<string, unknown>;
        webPage: Record<string, unknown>;
    };
};

export default function BlogShow({ blog_list_label, post, structuredData }: Props) {
    const primaryCategory = post.categories[0];
    const breadcrumbItems = [
        { label: 'خانه', href: '/' },
        { label: blog_list_label, href: '/blog' },
    ];
    if (primaryCategory) {
        breadcrumbItems.push({
            label: primaryCategory.title,
            href: `/blog/categories/${primaryCategory.slug}`,
        });
    }
    breadcrumbItems.push({ label: post.title });

    const bodyHtml = post.content?.trim() || post.excerpt?.trim() || '';
    const readingMinutes = useMemo(
        () => (bodyHtml ? estimateReadingMinutes(bodyHtml) : 1),
        [bodyHtml],
    );

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={post.seo.title} description={post.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={post.seo}
                structuredData={[structuredData.breadcrumb, structuredData.blogPosting, structuredData.webPage]}
            />

            <article className="min-h-full">
                <div className="mx-auto w-full min-w-0 max-w-6xl overflow-x-clip px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                    <SitePublicBreadcrumb className="mb-6" items={breadcrumbItems} />

                    <header className="mb-6 max-w-4xl">
                        <h1 className="text-2xl font-black leading-snug text-neutral-black sm:text-3xl lg:text-[2.15rem]">
                            {post.title}
                        </h1>

                        <div className="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-neutral-black/55">
                            {post.published_at_label ? (
                                <span className="inline-flex items-center gap-1.5">
                                    <CalendarDays className="size-4 text-primary/75" aria-hidden />
                                    {post.published_at_label}
                                </span>
                            ) : null}
                            <span className="inline-flex items-center gap-1.5">
                                <Clock3 className="size-4 text-primary/75" aria-hidden />
                                {readingMinutes.toLocaleString('fa-IR')} دقیقه مطالعه
                            </span>
                            {primaryCategory ? (
                                <Link
                                    href={`/blog/categories/${primaryCategory.slug}`}
                                    className="inline-flex items-center gap-1.5 font-semibold text-primary hover:underline"
                                >
                                    <Tag className="size-4" aria-hidden />
                                    {primaryCategory.title}
                                </Link>
                            ) : null}
                        </div>
                    </header>

                    {post.image_url ? (
                        <div className="mb-8 overflow-hidden rounded-2xl bg-secondary/15">
                            <img
                                src={post.image_url}
                                alt=""
                                className="aspect-[2.2/1] w-full object-cover sm:aspect-[2.4/1]"
                                fetchPriority="high"
                                decoding="async"
                            />
                        </div>
                    ) : null}

                    {bodyHtml ? (
                        <BlogPostContent
                            html={bodyHtml}
                            className="site-rich-html--blog site-rich-html--article w-full max-w-none text-neutral-black/85"
                        />
                    ) : null}

                    {post.categories.length > 0 ? (
                        <div className="mt-10 flex flex-wrap gap-2 border-t border-secondary/50 pt-6">
                            {post.categories.map((cat) => (
                                <Link
                                    key={cat.id}
                                    href={`/blog/categories/${cat.slug}`}
                                    className="rounded-full border border-secondary/60 bg-surface-muted/50 px-3.5 py-1.5 text-xs font-bold text-neutral-black/70 transition hover:border-primary/30 hover:text-primary"
                                >
                                    {cat.title}
                                </Link>
                            ))}
                        </div>
                    ) : null}

                    <section className="mt-12 border-t border-secondary/50 pt-8">
                        <CourseCommentsSection
                            submitUrl={`${blogPostPath(post.id, post.slug)}/comments`}
                            comments={post.comments}
                            summary={post.comments_summary}
                            sectionId="post-comments"
                            title="نظرات"
                            showRating={false}
                            placeholder="نظر خود را درباره این مطلب بنویسید..."
                        />
                    </section>

                    <p className="mt-10">
                        <Link
                            href="/blog"
                            className="inline-flex items-center gap-2 text-sm font-bold text-primary hover:underline"
                        >
                            <ArrowRight className="size-4" aria-hidden />
                            بازگشت به {blog_list_label}
                        </Link>
                    </p>
                </div>
            </article>
        </AppLayout>
    );
}
