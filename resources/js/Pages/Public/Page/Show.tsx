import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import AppLayout from '@/Layouts/AppLayout';

type Props = {
    page: {
        id: number;
        title: string;
        slug: string;
        content: string | null;
        seo: CatalogSeo;
    };
    structuredData: {
        breadcrumb: Record<string, unknown>;
        webPage: Record<string, unknown>;
    };
};

export default function PageShow({ page, structuredData }: Props) {
    const breadcrumbItems = [
        { label: 'خانه', href: '/' },
        { label: page.title },
    ];

    const bodyHtml = page.content?.trim() ?? '';

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={page.seo.title} description={page.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={page.seo}
                structuredData={[structuredData.breadcrumb, structuredData.webPage]}
            />

            <article className="home-page min-h-full">
                <header className="border-b border-secondary/50 bg-neutral-white">
                    <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                        <SitePublicBreadcrumb className="mb-5" items={breadcrumbItems} />
                        <h1 className="text-2xl font-bold leading-snug text-neutral-black md:text-3xl">
                            {page.title}
                        </h1>
                    </div>
                </header>

                <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 lg:px-8">
                    {bodyHtml ? (
                        <SiteRichHtml html={bodyHtml} className="site-rich-html--blog max-w-none" />
                    ) : (
                        <p className="text-sm text-neutral-black/55">محتوایی برای این صفحه ثبت نشده است.</p>
                    )}
                </div>
            </article>
        </AppLayout>
    );
}
