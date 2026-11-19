import { CommunityPostFeed, type CommunityPostCard } from '@/Components/Posts/CommunityPostFeed';
import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import AppLayout from '@/Layouts/AppLayout';
import type { InstructorResumeProfile } from '@/types/instructor-resume';
import { GraduationCap, MapPin, Newspaper } from 'lucide-react';
import { useMemo } from 'react';

type Profile = InstructorResumeProfile & {
    uuid: string;
    slug: string;
    is_instructor: boolean;
    show_resume: boolean;
    posts: CommunityPostCard[];
    posts_count: number;
};

type Props = {
    profile: Profile;
    seo: CatalogSeo;
    structuredData: {
        breadcrumb: Record<string, unknown>;
        person: Record<string, unknown>;
        profilePage: Record<string, unknown>;
    };
};

function initials(name: string): string {
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return '؟';
    }
    if (parts.length === 1) {
        return parts[0].slice(0, 1);
    }

    return `${parts[0].slice(0, 1)}${parts[1].slice(0, 1)}`;
}

export default function PublicUserShow({ profile, seo, structuredData }: Props) {
    const hasResume =
        profile.show_resume &&
        ((profile.skills?.length ?? 0) > 0 || profile.sections.length > 0);

    const feedPosts = useMemo(
        () => ({
            data: profile.posts,
            current_page: 1,
            last_page: 1,
            total: profile.posts_count,
            links: [] as { url: string | null; label: string; active: boolean }[],
        }),
        [profile.posts, profile.posts_count],
    );

    const avatarAlt = profile.avatar?.alt ?? profile.fullName;

    return (
        <AppLayout fullWidth mainClassName="bg-surface-muted">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead
                seo={seo}
                structuredData={[structuredData.breadcrumb, structuredData.person, structuredData.profilePage]}
            />

            <div className="relative overflow-hidden">
                <div
                    className="pointer-events-none absolute inset-x-0 top-0 h-[22rem] bg-[radial-gradient(ellipse_at_top,_rgb(var(--color-primary-rgb)/0.16),_transparent_58%),linear-gradient(180deg,_rgb(var(--color-primary-rgb)/0.07),_transparent_72%)]"
                    aria-hidden
                />
                <div
                    className="pointer-events-none absolute -start-16 top-10 size-56 rounded-full bg-primary/[0.07] blur-3xl"
                    aria-hidden
                />
                <div
                    className="pointer-events-none absolute -end-10 top-24 size-44 rounded-full bg-primary/[0.1] blur-3xl"
                    aria-hidden
                />

                <div className="relative mx-auto max-w-xl px-4 pb-12 pt-6 sm:px-6 lg:pt-8">
                    <SitePublicBreadcrumb
                        className="mb-8"
                        items={[{ label: 'خانه', href: '/' }, { label: profile.fullName }]}
                    />

                    <header className="animate-[category-hero-in_0.55s_ease-out_both]">
                        <div className="flex flex-col items-center text-center">
                            <div className="hero-float relative">
                                <div className="size-28 overflow-hidden rounded-[1.75rem] bg-white shadow-[0_18px_40px_-18px_rgb(var(--color-primary-rgb)/0.45)] ring-4 ring-white sm:size-32">
                                    {profile.avatar?.url ? (
                                        <img
                                            src={profile.avatar.url}
                                            alt={avatarAlt}
                                            className="size-full object-cover"
                                        />
                                    ) : (
                                        <span className="flex size-full items-center justify-center bg-gradient-to-br from-primary/15 to-primary/5 text-3xl font-black text-primary">
                                            {initials(profile.fullName)}
                                        </span>
                                    )}
                                </div>
                            </div>

                            <h1 className="mt-5 text-3xl font-black tracking-tight text-neutral-black sm:text-4xl">
                                {profile.fullName}
                            </h1>

                            {/* LinkedIn-style job title — same for every public profile */}
                            {profile.headline?.trim() ? (
                                <p className="mt-2 max-w-md text-lg font-semibold leading-snug text-primary sm:text-xl">
                                    {profile.headline}
                                </p>
                            ) : null}

                            <div className="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5 text-sm text-neutral-black/50">
                                {profile.location ? (
                                    <span className="inline-flex items-center gap-1">
                                        <MapPin className="size-3.5 text-primary/70" aria-hidden />
                                        {profile.location}
                                    </span>
                                ) : null}
                                {profile.is_instructor ? (
                                    <span className="inline-flex items-center gap-1 font-semibold text-primary/80">
                                        <GraduationCap className="size-3.5" aria-hidden />
                                        مدرس
                                    </span>
                                ) : null}
                                <span className="inline-flex items-center gap-1">
                                    <Newspaper className="size-3.5 text-primary/70" aria-hidden />
                                    {profile.posts_count.toLocaleString('fa-IR')} پست
                                </span>
                            </div>
                        </div>
                    </header>

                    {profile.bio?.trim() ? (
                        <section className="mt-8 animate-[category-hero-in_0.7s_ease-out_both]">
                            <h2 className="mb-3 text-sm font-extrabold tracking-wide text-neutral-black/45">
                                درباره
                            </h2>
                            <div className="rounded-[1.5rem] border border-secondary/40 bg-white/95 p-5 shadow-sm sm:p-6">
                                <SiteRichHtml
                                    html={profile.bio}
                                    className="text-sm leading-7 text-neutral-black/75 sm:text-[0.95rem]"
                                />
                            </div>
                        </section>
                    ) : null}

                    {hasResume ? (
                        <section className="mt-8">
                            <h2 className="mb-3 text-sm font-extrabold tracking-wide text-neutral-black/45">
                                تجربه و رزومه
                            </h2>
                            <div className="rounded-[1.75rem] border border-secondary/40 bg-white/95 p-5 shadow-sm backdrop-blur sm:p-7">
                                <InstructorResumeProfileView
                                    profile={{
                                        ...profile,
                                        sections: profile.show_resume ? profile.sections : [],
                                        skills: profile.show_resume ? profile.skills : [],
                                    }}
                                    variant="page"
                                    hideHeader
                                />
                            </div>
                        </section>
                    ) : null}

                    <section className="mt-8">
                        <div className="mb-4 flex items-end justify-between gap-3">
                            <h2 className="text-sm font-extrabold tracking-wide text-neutral-black/45">
                                فعالیت
                            </h2>
                            {profile.posts_count > 0 ? (
                                <span className="text-xs text-neutral-black/40">
                                    {profile.posts_count.toLocaleString('fa-IR')} پست
                                </span>
                            ) : null}
                        </div>
                        <CommunityPostFeed
                            posts={feedPosts}
                            emptyMessage="هنوز پست تأییدشده‌ای در این پروفایل نیست."
                            reloadOnly={['profile']}
                        />
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
