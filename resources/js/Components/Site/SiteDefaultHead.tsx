import { siteSettingsFromPage } from '@/lib/site-settings';
import { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';

type Props = {
    /** عنوان صفحه — در titlebar بدون نام سایت (نام سایت فقط صفحهٔ اصلی) */
    title?: string;
    description?: string;
};

export function SiteDefaultHead({ title, description }: Props) {
    const page = usePage<PageProps>();
    const settings = siteSettingsFromPage(page.props);
    const appName = page.props.app.name;
    const canonical = page.props.url;

    const metaTitle = settings.seo_meta_title || appName;
    const metaDescription =
        description ?? settings.seo_meta_description ?? settings.site_description ?? undefined;
    const ogImage = settings.seo_og_image ?? settings.site_logo ?? undefined;
    const keywords = settings.seo_meta_keywords;

    const gaId = settings.google_analytics_id?.trim();
    const gscCode = settings.google_search_console?.trim();

    return (
        <Head title={title}>
            <meta head-key="description" name="description" content={metaDescription ?? metaTitle} />
            {keywords ? <meta head-key="keywords" name="keywords" content={keywords} /> : null}
            <meta head-key="og:type" property="og:type" content="website" />
            <meta head-key="og:title" property="og:title" content={title?.trim() || metaTitle} />
            {metaDescription ? (
                <meta head-key="og:description" property="og:description" content={metaDescription} />
            ) : null}
            {ogImage ? <meta head-key="og:image" property="og:image" content={ogImage} /> : null}
            <meta head-key="og:url" property="og:url" content={canonical} />
            <meta head-key="theme-color" name="theme-color" content={settings.site_color ?? '#00489e'} />
            {settings.site_icon ? <link head-key="icon" rel="icon" href={settings.site_icon} /> : null}
            {gscCode ? <meta head-key="google-site-verification" name="google-site-verification" content={gscCode} /> : null}
            {gaId ? (
                <>
                    <script head-key="ga-loader" async src={`https://www.googletagmanager.com/gtag/js?id=${gaId}`} />
                    <script head-key="ga-config">
                        {`window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','${gaId}');`}
                    </script>
                </>
            ) : null}
        </Head>
    );
}
