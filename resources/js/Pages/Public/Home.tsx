import { HomeDynamicSections } from '@/Components/Home/HomeDynamicSections';
import { HomeStoriesBar } from '@/Components/Home/HomeStoriesBar';
import type { StoryFeedGroup } from '@/Components/Home/stories-types';
import type { HomeSection } from '@/Components/Home/types';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

type Props = {
    sections: HomeSection[];
    storyGroups: StoryFeedGroup[];
    seo: CatalogSeo;
    structuredData: {
        organization: Record<string, unknown>;
        webSite: Record<string, unknown>;
    };
};

export default function Home({ sections, storyGroups, seo, structuredData }: Props) {
    const { auth } = usePage<PageProps>().props;

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={seo}
                structuredData={[structuredData.organization, structuredData.webSite]}
            />
            <HomeStoriesBar initialGroups={storyGroups ?? []} isAuthenticated={auth.user !== null} />
            <div className="home-page min-h-full">
                <HomeDynamicSections sections={sections} />
            </div>
        </AppLayout>
    );
}
