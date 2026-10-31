import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';
import type { InstructorResumeProfile } from '@/types/instructor-resume';

type Props = {
    profile: InstructorResumeProfile;
    seo: CatalogSeo;
    structuredData: {
        breadcrumb: Record<string, unknown>;
        person: Record<string, unknown>;
        profilePage: Record<string, unknown>;
    };
};

export default function InstructorResumeShow({ profile, seo, structuredData }: Props) {
    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={seo}
                structuredData={[structuredData.breadcrumb, structuredData.person, structuredData.profilePage]}
            />
            <div className="home-page min-h-full">
                <div className="border-b border-secondary/50 bg-neutral-white">
                    <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 md:py-10">
                        <SitePublicBreadcrumb
                            className="mb-6"
                            items={[
                                { label: 'خانه', href: '/' },
                                { label: profile.fullName },
                            ]}
                        />
                        <InstructorResumeProfileView profile={profile} variant="page" />
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
