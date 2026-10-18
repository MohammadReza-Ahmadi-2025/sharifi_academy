import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import {
    CourseCommentsSection,
    type PublicCourseComment,
} from '@/Components/Courses/CourseCommentsSection';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { CalendarDays, UserRound } from 'lucide-react';

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

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={post.seo.title} description={post.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={post.seo}
                structuredData={[structuredData.breadcrumb, structuredData.blogPosting, structuredData.webPage]}
            />

            <article className="home-page min-h-full">
                <header className="border-b border-secondary/50 bg-gradient-to-b from-primary/[0.05] to-neutral-white">
                    <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                        <SitePublicBreadcrumb className="mb-5" items={breadcrumbItems} />
                        <h1 className="text-2xl font-bold leading-snug text-neutral-black md:text-3xl">{post.title}</h1>
                        <div className="mt-4 flex flex-wrap items-center gap-4 text-sm text-neutral-black/55">
                            {post.published_at_label ? (
                                <span className="inline-flex items-center gap-1.5">
                                    <CalendarDays className="size-4 text-primary/80" aria-hidden />
                                    {post.published_at_label}
                                </span>
                            ) : null}
                            {post.author_name ? (
                                <span className="inline-flex items-center gap-1.5">
                                    <UserRound className="size-4 text-primary/80" aria-hidden />
                                    {post.author_name}
                                </span>
                            ) : null}
                        </div>
                        {post.image_url ? (
                            <div className="mt-6 overflow-hidden rounded-2xl border border-secondary/50">
                                <img src={post.image_url} alt="" className="w-full object-cover" />
                            </div>
                        ) : null}
                    </div>
                </header>

                <div className="mx-auto w-full max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                    {bodyHtml ? (
                        <SiteRichHtml html={bodyHtml} className="site-rich-html--blog w-full max-w-none text-neutral-black/85" />
                    ) : null}

                    {post.categories.length > 0 ? (
                        <div className="mt-10 flex flex-wrap gap-2 border-t border-secondary/50 pt-6">
                            {post.categories.map((cat) => (
                                <Link
                                    key={cat.id}
                                    href={`/blog/categories/${cat.slug}`}
                                    className="rounded-full border border-primary/20 bg-primary/[0.06] px-3 py-1 text-xs font-medium text-primary transition hover:bg-primary/10"
                                >
                                    {cat.title}
                                </Link>
                            ))}
                        </div>
                    ) : null}

                    <section className="mt-12 rounded-2xl border border-secondary bg-surface-muted/50 p-6 sm:p-8">
                        <CourseCommentsSection
                            submitUrl={`/blog/${post.slug}/comments`}
                            comments={post.comments}
                            summary={post.comments_summary}
                            sectionId="post-comments"
                            title="نظرات"
                            showRating={false}
                            placeholder="نظر خود را درباره این مطلب بنویسید..."
                        />
                    </section>

                    <p className="mt-8">
                        <Link href="/blog" className="text-sm font-semibold text-primary hover:underline">
                            ← بازگشت به وبلاگ
                        </Link>
                    </p>
                </div>
            </article>
        </AppLayout>
    );
}
