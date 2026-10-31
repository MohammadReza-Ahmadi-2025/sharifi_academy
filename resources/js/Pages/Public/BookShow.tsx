import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';

type ProductPayload = {
    id: number;
    title: string;
    slug: string;
    short_description?: string | null;
    description?: string | null;
    sku?: string | null;
    brand?: { id: number; title: string; slug: string; url: string } | null;
    categories: { id: number; title: string; slug: string }[];
    price?: number | null;
    compare_price?: number | null;
    image_url?: string | null;
    in_stock: boolean;
    seo: CatalogSeo;
};

type Props = {
    product: ProductPayload;
    structuredData: {
        breadcrumb: Record<string, unknown>;
        product: Record<string, unknown>;
        webPage: Record<string, unknown>;
    };
};

export default function BookShow({ product, structuredData }: Props) {
    const primaryCategory = product.categories[0];

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={product.seo.title} description={product.seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={product.seo}
                structuredData={[structuredData.breadcrumb, structuredData.product, structuredData.webPage]}
            />

            <div className="border-b border-secondary/50 bg-neutral-white">
                <div className="mx-auto max-w-[88rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                    <SitePublicBreadcrumb
                        className="mb-6"
                        items={[
                            { label: 'خانه', href: '/' },
                            ...(primaryCategory
                                ? [{ label: primaryCategory.title, href: `/categories/${primaryCategory.slug}` }]
                                : []),
                            { label: product.title },
                        ]}
                    />

                    <div className="grid items-start gap-8 lg:grid-cols-2 lg:gap-12">
                        <div className="aspect-[4/3] overflow-hidden rounded-3xl border border-secondary/70 bg-primary/5">
                            <img
                                src={product.image_url?.trim() || '/banner.png'}
                                alt={product.title}
                                className="size-full object-cover"
                            />
                        </div>

                        <div>
                            {product.brand ? (
                                <Link
                                    href={product.brand.url}
                                    className="text-sm font-semibold text-primary hover:underline"
                                >
                                    {product.brand.title}
                                </Link>
                            ) : null}

                            <h1 className="mt-2 text-3xl font-extrabold text-neutral-black sm:text-4xl">
                                {product.title}
                            </h1>

                            {product.short_description ? (
                                <p className="mt-4 text-base leading-relaxed text-neutral-black/75">
                                    {product.short_description}
                                </p>
                            ) : null}

                            <div className="mt-6 flex flex-wrap items-end gap-3">
                                {product.price != null ? (
                                    <p className="text-2xl font-extrabold text-primary">
                                        {product.price.toLocaleString('fa-IR')} ریال
                                    </p>
                                ) : (
                                    <p className="text-sm font-medium text-amber-800">قیمت به‌زودی</p>
                                )}
                                {product.compare_price != null && product.price != null ? (
                                    <p className="text-sm text-slate-400 line-through">
                                        {product.compare_price.toLocaleString('fa-IR')} ریال
                                    </p>
                                ) : null}
                            </div>

                            <p className="mt-3 text-xs font-medium text-slate-500">
                                {product.in_stock ? 'موجود' : 'ناموجود'}
                                {product.sku ? ` · SKU ${product.sku}` : ''}
                            </p>

                            {product.categories.length > 0 ? (
                                <div className="mt-5 flex flex-wrap gap-2">
                                    {product.categories.map((category) => (
                                        <Link
                                            key={category.id}
                                            href={`/categories/${category.slug}`}
                                            className="rounded-full bg-surface-muted px-3 py-1 text-xs font-semibold text-neutral-black/75 hover:bg-primary/10 hover:text-primary"
                                        >
                                            {category.title}
                                        </Link>
                                    ))}
                                </div>
                            ) : null}
                        </div>
                    </div>

                    {product.description?.trim() ? (
                        <section className="prose prose-slate mt-10 max-w-none" aria-label="توضیحات محصول">
                            <div dangerouslySetInnerHTML={{ __html: product.description }} />
                        </section>
                    ) : null}
                </div>
            </div>
        </AppLayout>
    );
}
