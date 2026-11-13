import { BlogHero } from '@/Components/Blog/BlogHero';
import { BlogPostListing } from '@/Components/Blog/BlogPostListing';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';

type Paginator = {
    data: import('@/Components/Blog/BlogPostListing').BlogPostCard[];
    current_page: number;
    last_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
};

type Props = {
    page: { header_title: string; subheader: string | null };
    seo: CatalogSeo;
    posts: Paginator;
    structuredData: {
        breadcrumb: Record<string, unknown>;
        webPage: Record<string, unknown>;
        itemList: Record<string, unknown>;
    };
};

export default function BlogIndex({ page, seo, posts, structuredData }: Props) {
    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage, structuredData.itemList]}
            />

            <div className="min-h-full">
                <BlogHero
                    title={page.header_title}
                    breadcrumb={[
                        { label: 'خانه', href: '/' },
                        { label: page.header_title },
                    ]}
                    eyebrow={`${posts.total.toLocaleString('fa-IR')} مقاله`}
                    subheader={page.subheader}
                />

                <section className="mx-auto max-w-6xl px-4 pb-14 sm:px-6 lg:px-8 lg:pb-16">
                    <BlogPostListing posts={posts} emptyMessage="هنوز مقاله‌ای منتشر نشده است." />
                </section>
            </div>
        </AppLayout>
    );
}
