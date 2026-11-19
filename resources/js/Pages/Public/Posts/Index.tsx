import { CommunityPostFeed } from '@/Components/Posts/CommunityPostFeed';
import { SiteCatalogSeoHead, type CatalogSeo } from '@/Components/Site/SiteCatalogSeoHead';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import AppLayout from '@/Layouts/AppLayout';
import { Link } from '@inertiajs/react';
import { Plus } from 'lucide-react';

type Props = {
    page: { header_title: string; subheader: string | null };
    seo: CatalogSeo;
    posts: {
        data: import('@/Components/Posts/CommunityPostFeed').CommunityPostCard[];
        current_page: number;
        last_page: number;
        total: number;
        links: { url: string | null; label: string; active: boolean }[];
    };
    create_post_url: string | null;
    can_create_post: boolean;
    needs_profile?: boolean;
};

export default function CommunityPostsIndex({
    page,
    seo,
    posts,
    create_post_url,
    can_create_post,
    needs_profile = false,
}: Props) {
    return (
        <AppLayout fullWidth mainClassName="bg-surface-muted">
            <SiteDefaultHead title={seo.title} description={seo.description ?? undefined} />
            <SiteCatalogSeoHead seo={seo} />

            <div className="mx-auto w-full max-w-xl px-4 py-8 sm:px-6 lg:py-10">
                <header className="mb-6 flex items-start justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-extrabold text-neutral-black">{page.header_title}</h1>
                        {page.subheader ? (
                            <p className="mt-1 text-sm text-neutral-black/55">{page.subheader}</p>
                        ) : null}
                    </div>
                    {create_post_url ? (
                        <Link
                            href={create_post_url}
                            className="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-primary px-3.5 py-2 text-sm font-semibold text-on-primary shadow-sm"
                        >
                            <Plus className="size-4" aria-hidden />
                            {needs_profile ? 'تکمیل پروفایل' : can_create_post ? 'پست جدید' : 'ورود / پست جدید'}
                        </Link>
                    ) : null}
                </header>

                <CommunityPostFeed posts={posts} />
            </div>
        </AppLayout>
    );
}
