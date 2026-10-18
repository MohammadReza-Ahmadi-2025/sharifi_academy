import { BlogPostListing } from '@/Components/Blog/BlogPostListing';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
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

            <div className="home-page min-h-full">
                <section className="border-b border-secondary/50 bg-gradient-to-b from-primary/[0.05] to-neutral-white">
                    <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                        <SitePublicBreadcrumb
                            className="mb-5"
                            items={[
                                { label: 'خانه', href: '/' },
                                { label: page.header_title },
                            ]}
                        />
                        <h1 className="text-2xl font-bold text-neutral-black md:text-3xl">{page.header_title}</h1>
                        {page.subheader ? (
                            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-neutral-black/60 md:text-base">
                                {page.subheader}
                            </p>
                        ) : null}
                    </div>
                </section>

                <section className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                    <BlogPostListing posts={posts} emptyMessage="هنوز مقاله‌ای منتشر نشده است." />
                </section>
            </div>
        </AppLayout>
    );
}
