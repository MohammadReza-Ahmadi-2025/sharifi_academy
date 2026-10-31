import { ProductCatalogGrid, type ProductCatalogCard } from '@/Components/Catalog/ProductCatalogGrid';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';

type Props = {
    brand: {
        id: number;
        title: string;
        slug: string;
        description?: string | null;
        website?: string | null;
        seo: CatalogSeo;
    };
    products: ProductCatalogCard[];
    structuredData: {
        breadcrumb: Record<string, unknown>;
        brand: Record<string, unknown>;
        webPage: Record<string, unknown>;
        itemList: Record<string, unknown>;
    };
};

export default function BrandShow({ brand, products, structuredData }: Props) {
    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={brand.seo.title} description={brand.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={brand.seo}
                structuredData={[
                    structuredData.breadcrumb,
                    structuredData.brand,
                    structuredData.webPage,
                    structuredData.itemList,
                ]}
            />

            <div className="home-page min-h-full">
                <section className="border-b border-secondary/50 bg-gradient-to-b from-primary/[0.05] to-neutral-white">
                    <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                        <SitePublicBreadcrumb
                            className="mb-5"
                            items={[{ label: 'خانه', href: '/' }, { label: brand.title }]}
                        />
                        <h1 className="text-2xl font-bold text-neutral-black md:text-3xl">{brand.title}</h1>
                        {brand.description ? (
                            <p className="mt-2 max-w-2xl text-sm leading-relaxed text-neutral-black/60 md:text-base">
                                {brand.description}
                            </p>
                        ) : null}
                        {brand.website ? (
                            <a
                                href={brand.website}
                                target="_blank"
                                rel="noreferrer"
                                className="mt-3 inline-block text-sm font-semibold text-primary hover:underline"
                            >
                                وب‌سایت برند
                            </a>
                        ) : null}
                    </div>
                </section>

                <section className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 md:py-10 lg:px-8">
                    <h2 className="mb-5 text-lg font-bold text-neutral-black">محصولات</h2>
                    <ProductCatalogGrid products={products} variant="category" />
                </section>
            </div>
        </AppLayout>
    );
}
