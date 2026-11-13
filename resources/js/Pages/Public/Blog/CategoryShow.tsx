import { BlogHero } from '@/Components/Blog/BlogHero';
import { BlogPostListing } from '@/Components/Blog/BlogPostListing';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { Link, router } from '@inertiajs/react';

type Subcategory = {
    id: number;
    title: string;
    slug: string;
    items_count: number;
    image_url?: string | null;
};

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

            <div className="min-h-full">
                <BlogHero
                    title={category.title}
                    breadcrumb={[
                        { label: 'خانه', href: '/' },
                        { label: blog_list_label, href: '/blog' },
                        { label: category.title },
                    ]}
                    eyebrow={`${posts.total.toLocaleString('fa-IR')} مقاله`}
                    descriptionHtml={category.description}
                    imageUrl={category.image_url}
                />

                <section className="mx-auto max-w-6xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-16">
                    {subcategories.length > 0 ? (
                        <div className="mb-7 flex flex-wrap gap-2" aria-label="زیردسته‌ها">
                            {subcategories.map((sub) => (
                                <Link
                                    key={sub.id}
                                    href={`/blog/categories/${sub.slug}`}
                                    className="inline-flex items-center gap-2 rounded-full border border-secondary/60 bg-surface-muted/40 px-3.5 py-2 text-sm font-semibold text-neutral-black/75 transition hover:border-primary/35 hover:text-primary"
                                >
                                    {sub.title}
                                    <span className="text-[11px] font-bold text-primary">
                                        {sub.items_count.toLocaleString('fa-IR')}
                                    </span>
                                </Link>
                            ))}
                        </div>
                    ) : null}

                    <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                        <p className="text-sm font-semibold text-neutral-black/55">مقالات این دسته</p>
                        <select
                            value={filters.sort}
                            onChange={(e) => {
                                router.get(
                                    basePath,
                                    { sort: e.target.value },
                                    { preserveState: true, preserveScroll: true },
                                );
                            }}
                            className="rounded-lg border border-secondary/60 bg-neutral-white px-3 py-2 text-sm font-semibold text-neutral-black outline-none focus:border-primary"
                        >
                            {sortOptions.map((opt) => (
                                <option key={opt.value} value={opt.value}>
                                    {opt.label}
                                </option>
                            ))}
                        </select>
                    </div>

                    <BlogPostListing
                        posts={posts}
                        emptyMessage="در این دسته هنوز مقاله‌ای منتشر نشده است."
                    />
                </section>
            </div>
        </AppLayout>
    );
}
