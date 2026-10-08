import { Head } from '@inertiajs/react';

export type CatalogSeo = {
    title: string;
    description?: string | null;
    keywords?: string | null;
    canonical: string;
    og_image?: string | null;
    og_type?: string;
};

type Props = {
    seo: CatalogSeo;
    structuredData?: Record<string, unknown>[];
};

export function SiteCatalogSeoHead({ seo, structuredData = [] }: Props) {
    const ogTitle = seo.title;

    return (
        <Head>
            {seo.keywords ? <meta head-key="seo-keywords" name="keywords" content={seo.keywords} /> : null}
            <link head-key="seo-canonical" rel="canonical" href={seo.canonical} />
            <meta head-key="og-type" property="og:type" content={seo.og_type ?? 'website'} />
            <meta head-key="og-title" property="og:title" content={ogTitle} />
            {seo.description ? (
                <meta head-key="og-description" property="og:description" content={seo.description} />
            ) : null}
            <meta head-key="og-url" property="og:url" content={seo.canonical} />
            {seo.og_image ? <meta head-key="og-image" property="og:image" content={seo.og_image} /> : null}
            <meta head-key="twitter-card" name="twitter:card" content={seo.og_image ? 'summary_large_image' : 'summary'} />
            <meta head-key="twitter-title" name="twitter:title" content={ogTitle} />
            {seo.description ? (
                <meta head-key="twitter-description" name="twitter:description" content={seo.description} />
            ) : null}
            {seo.og_image ? <meta head-key="twitter-image" name="twitter:image" content={seo.og_image} /> : null}
            {structuredData.map((block, index) => (
                <script key={`ld-${index}`} head-key={`ld-${index}`} type="application/ld+json">
                    {JSON.stringify(block)}
                </script>
            ))}
        </Head>
    );
}
