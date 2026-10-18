import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import AppLayout from '@/Layouts/AppLayout';
import type { InstructorResumeProfile } from '@/types/instructor-resume';

type Props = {
    profile: InstructorResumeProfile;
};

export default function InstructorResumeShow({ profile }: Props) {
    const title = `رزومه ${profile.fullName}`;
    const description = profile.headline?.trim() || profile.bio?.slice(0, 160) || undefined;

    return (
        <AppLayout fullWidth mainClassName="bg-neutral-white">
            <SiteDefaultHead title={title} description={description} />
            <div className="home-page min-h-full">
                <div className="border-b border-secondary/50 bg-neutral-white">
                    <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 md:py-10">
                        <SitePublicBreadcrumb
                            className="mb-6"
                            items={[
                                { label: 'خانه', href: '/' },
                                { label: 'اساتید', href: '/' },
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
