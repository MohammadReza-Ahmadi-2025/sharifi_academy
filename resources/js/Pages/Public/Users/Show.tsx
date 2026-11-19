import { CommunityPostFeed, type CommunityPostCard } from '@/Components/Posts/CommunityPostFeed';
import { InstructorResumeProfileView } from '@/Components/Profile/InstructorResumeProfileView';
import { ProfileQrCode } from '@/Components/Profile/ProfileQrCode';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SitePublicBreadcrumb } from '@/Components/Site/SitePublicBreadcrumb';
import { SiteRichHtml } from '@/Components/Site/SiteRichHtml';
import AppLayout from '@/Layouts/AppLayout';
import { cn } from '@/lib/utils';
import type { InstructorResumeProfile } from '@/types/instructor-resume';
import { BriefcaseBusiness, GraduationCap, MapPin, Newspaper, UserRound } from 'lucide-react';
import { useMemo, useState } from 'react';

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

type ProfileTabId = 'posts' | 'resume' | 'about';

type ProfileTab = {
    id: ProfileTabId;
    label: string;
    count?: number;
    icon: typeof Newspaper;
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
    const hasBio = Boolean(profile.bio?.trim());
    const hasResume =
        profile.show_resume &&
        ((profile.skills?.length ?? 0) > 0 || profile.sections.length > 0);

    const tabs = useMemo<ProfileTab[]>(() => {
        const items: ProfileTab[] = [
            {
                id: 'posts',
                label: 'پست‌ها',
                count: profile.posts_count,
                icon: Newspaper,
            },
        ];

        if (hasResume) {
            items.push({
                id: 'resume',
                label: 'رزومه',
                icon: BriefcaseBusiness,
            });
        }

        if (hasBio) {
            items.push({
                id: 'about',
                label: 'درباره',
                icon: UserRound,
            });
        }

        return items;
    }, [hasBio, hasResume, profile.posts_count]);

    const [activeTab, setActiveTab] = useState<ProfileTabId>(() => {
        if (profile.posts_count > 0) {
            return 'posts';
        }
        if (hasResume) {
            return 'resume';
        }
        if (hasBio) {
            return 'about';
        }

        return 'posts';
    });

    const currentTab = tabs.some((tab) => tab.id === activeTab) ? activeTab : tabs[0]?.id ?? 'posts';

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
    const profileUrl = seo.canonical || profile.profile_url || '';
    const showTabs = tabs.length > 1;

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
                        <div className="overflow-hidden rounded-[1.75rem] border border-white/70 bg-white/80 p-5 shadow-[0_20px_50px_-28px_rgb(var(--color-primary-rgb)/0.45)] backdrop-blur-sm sm:p-7">
                            <div className="flex flex-col items-center gap-5 sm:flex-row sm:items-start sm:gap-6">
                                <div className="hero-float relative shrink-0">
                                    <div className="size-28 overflow-hidden rounded-[1.5rem] bg-white shadow-[0_18px_40px_-18px_rgb(var(--color-primary-rgb)/0.45)] ring-4 ring-white sm:size-32">
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

                                <div className="min-w-0 flex-1 text-center sm:pt-1 sm:text-start">
                                    <h1 className="text-3xl font-black tracking-tight text-neutral-black sm:text-4xl">
                                        {profile.fullName}
                                    </h1>

                                    {profile.headline?.trim() ? (
                                        <p className="mt-2 text-lg font-semibold leading-snug text-primary sm:text-xl">
                                            {profile.headline}
                                        </p>
                                    ) : null}

                                    <div className="mt-3 flex flex-wrap items-center justify-center gap-x-3 gap-y-1.5 text-sm text-neutral-black/50 sm:justify-start">
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

                                {profileUrl ? (
                                    <div className="shrink-0 sm:ms-auto">
                                        <ProfileQrCode
                                            url={profileUrl}
                                            shareTitle={profile.fullName}
                                            fileName={`profile-${profile.uuid || profile.slug}-qr.png`}
                                            compact
                                        />
                                    </div>
                                ) : null}
                            </div>
                        </div>
                    </header>

                    <section className="mt-6 animate-[category-hero-in_0.7s_ease-out_both]">
                        {showTabs ? (
                            <div
                                role="tablist"
                                aria-label="بخش‌های پروفایل"
                                className="sticky top-2 z-20 mb-5 grid gap-1 rounded-2xl border border-secondary/40 bg-white/90 p-1.5 shadow-sm backdrop-blur-md"
                                style={{ gridTemplateColumns: `repeat(${tabs.length}, minmax(0, 1fr))` }}
                            >
                                {tabs.map((tab) => {
                                    const Icon = tab.icon;
                                    const selected = currentTab === tab.id;

                                    return (
                                        <button
                                            key={tab.id}
                                            type="button"
                                            role="tab"
                                            aria-selected={selected}
                                            id={`profile-tab-${tab.id}`}
                                            aria-controls={`profile-panel-${tab.id}`}
                                            onClick={() => setActiveTab(tab.id)}
                                            className={cn(
                                                'inline-flex items-center justify-center gap-1.5 rounded-xl px-3 py-2.5 text-sm font-bold transition',
                                                selected
                                                    ? 'bg-primary text-on-primary shadow-sm'
                                                    : 'text-neutral-black/55 hover:bg-primary/5 hover:text-primary',
                                            )}
                                        >
                                            <Icon className="size-4 shrink-0" aria-hidden />
                                            <span>{tab.label}</span>
                                            {typeof tab.count === 'number' ? (
                                                <span
                                                    className={cn(
                                                        'rounded-full px-1.5 py-0.5 text-[10px] font-extrabold leading-none',
                                                        selected
                                                            ? 'bg-white/20 text-on-primary'
                                                            : 'bg-neutral-black/5 text-neutral-black/45',
                                                    )}
                                                >
                                                    {tab.count.toLocaleString('fa-IR')}
                                                </span>
                                            ) : null}
                                        </button>
                                    );
                                })}
                            </div>
                        ) : null}

                        <div
                            role="tabpanel"
                            id={`profile-panel-${currentTab}`}
                            aria-labelledby={`profile-tab-${currentTab}`}
                            className="animate-[category-hero-in_0.35s_ease-out_both]"
                            key={currentTab}
                        >
                            {currentTab === 'about' && hasBio ? (
                                <div className="rounded-[1.5rem] border border-secondary/40 bg-white/95 p-5 shadow-sm sm:p-6">
                                    <SiteRichHtml
                                        html={profile.bio}
                                        className="text-sm leading-7 text-neutral-black/75 sm:text-[0.95rem]"
                                    />
                                </div>
                            ) : null}

                            {currentTab === 'resume' && hasResume ? (
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
                            ) : null}

                            {currentTab === 'posts' ? (
                                <CommunityPostFeed
                                    posts={feedPosts}
                                    emptyMessage="هنوز پست تأییدشده‌ای در این پروفایل نیست."
                                    reloadOnly={['profile']}
                                />
                            ) : null}
                        </div>
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
