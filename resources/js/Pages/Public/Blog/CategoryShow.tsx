import { BlogPostListing } from '@/Components/Blog/BlogPostListing';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import AppLayout from '@/Layouts/AppLayout';
import { Link, router } from '@inertiajs/react';
import { FolderTree } from 'lucide-react';

type Subcategory = { id: number; title: string; slug: string; items_count: number };

type Props = {
    blog_list_label: string;
    category: {
        id: number;
        title: string;
        slug: string;
        description: string | null;
        image_url: string | null;
        seo: CatalogSeo;
    };
    posts: {
        data: import('@/Components/Blog/BlogPostListing').BlogPostCard[];
        current_page: number;
        last_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    subcategories: Subcategory[];
    filters: { sort: string };
    sortOptions: { value: string; label: string }[];
    structuredData: {
        breadcrumb: Record<string, unknown>;
        webPage: Record<string, unknown>;
        itemList: Record<string, unknown>;
    };
};

export default function BlogCategoryShow({
    blog_list_label,
    category,
    posts,
    subcategories,
    filters,
    sortOptions,
    structuredData,
}: Props) {
    const basePath = `/blog/categories/${category.slug}`;

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={category.seo.title} description={category.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={category.seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage, structuredData.itemList]}
            />

            <div className="home-page min-h-full">
                <section className="border-b border-secondary/50 bg-gradient-to-b from-primary/[0.05] to-neutral-white">
                    <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                        <SitePublicBreadcrumb
                            className="mb-5"
                            items={[
                                { label: 'خانه', href: '/' },
                                { label: blog_list_label, href: '/blog' },
                                { label: category.title },
                            ]}
                        />
                        <div className="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                            <div className="min-w-0 flex-1">
                                <p className="mb-2 text-sm font-medium text-primary">
                                    {posts.total.toLocaleString('fa-IR')} مقاله
                                </p>
                                <h1 className="text-2xl font-bold text-neutral-black md:text-3xl">{category.title}</h1>
                                {category.description?.trim() ? (
                                    <div className="mt-4 max-w-3xl text-sm leading-relaxed text-neutral-black/65 md:text-base">
                                        <SiteRichHtml html={category.description} />
                                    </div>
                                ) : null}
                            </div>
                            {category.image_url ? (
                                <div className="aspect-[16/10] w-full max-w-md shrink-0 overflow-hidden rounded-2xl border border-secondary/50 lg:w-80">
                                    <img src={category.image_url} alt="" className="size-full object-cover" />
                                </div>
                            ) : null}
                        </div>

                        {subcategories.length > 0 ? (
                            <div className="mt-6 flex flex-wrap gap-2">
                                {subcategories.map((sub) => (
                                    <Link
                                        key={sub.id}
                                        href={`/blog/categories/${sub.slug}`}
                                        className="inline-flex items-center gap-1.5 rounded-full border border-secondary/55 bg-neutral-white px-3 py-1.5 text-xs font-medium text-neutral-black/70 transition hover:border-primary/30 hover:text-primary"
                                    >
                                        <FolderTree className="size-3.5 text-primary/80" aria-hidden />
                                        {sub.title}
                                        <span className="text-neutral-black/40">({sub.items_count})</span>
                                    </Link>
                                ))}
                            </div>
                        ) : null}
                    </div>
                </section>

                <section className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                    <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <p className="text-sm text-neutral-black/55">مرتب‌سازی</p>
                        <select
                            value={filters.sort}
                            onChange={(e) => {
                                router.get(
                                    basePath,
                                    { sort: e.target.value },
                                    { preserveState: true, preserveScroll: true },
                                );
                            }}
                            className="rounded-lg border border-secondary/60 bg-neutral-white px-3 py-2 text-sm text-neutral-black"
                        >
                            {sortOptions.map((opt) => (
                                <option key={opt.value} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <BlogPostListing posts={posts} emptyMessage="در این دسته هنوز مقاله‌ای منتشر نشده است." />
                </section>
            </div>
        </AppLayout>
    );
}
