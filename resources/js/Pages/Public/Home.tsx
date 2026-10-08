import { HomeDynamicSections } from '@/Components/Home/HomeDynamicSections';
import { HomeStoriesBar } from '@/Components/Home/HomeStoriesBar';
import type { StoryFeedGroup } from '@/Components/Home/stories-types';
import type { HomeSection } from '@/Components/Home/types';
import AppLayout from '@/Layouts/AppLayout';
import type { PageProps } from '@/types';
import { Head, usePage } from '@inertiajs/react';

type Props = {
    sections: HomeSection[];
    storyGroups: StoryFeedGroup[];
};

export default function Home({ sections, storyGroups }: Props) {
    const { auth } = usePage<PageProps>().props;

    return (
        <AppLayout fullWidth>
            <Head title="خانه" />
            <HomeStoriesBar initialGroups={storyGroups ?? []} isAuthenticated={auth.user !== null} />
            <HomeDynamicSections sections={sections} />
        </AppLayout>
    );
}
