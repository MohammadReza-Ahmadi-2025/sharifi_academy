import { SiteFooterContactCard } from '@/Components/Site/SiteFooterContactCard';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteTheme } from '@/Components/Site/SiteTheme';
import { SiteHeader } from '@/Components/SiteHeader';
import { siteSettingsFromPage } from '@/lib/site-settings';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type SiteMenuGroup = {
    id: number;
    title: string;
    url?: string | null;
    items: Array<{ id: number; title: string; url: string; target_blank: boolean }>;
};

type Props = PropsWithChildren<{
    fullWidth?: boolean;
}>;

export default function AppLayout({ children, fullWidth = false }: Props) {
    const pageProps = usePage<PageProps>().props;
    const { app, siteMenus } = pageProps;
    const settings = siteSettingsFromPage(pageProps);
    const footerMenus = (siteMenus?.footer ?? []) as SiteMenuGroup[];

    const tagline = settings.site_description?.trim() || 'آموزش تخصصی مهندسی';
    const hasFooterMenus = footerMenus.length > 0;

    return (
        <div className="flex min-h-screen flex-col bg-surface-muted">
            <SiteTheme />
            <SiteDefaultHead />
            <SiteHeader />

            <main
                className={`flex flex-1 flex-col items-stretch ${
                    fullWidth ? 'w-full' : 'mx-auto w-full max-w-6xl px-4 py-8'
                }`}
            >
                {children}
            </main>

            <footer className="mt-auto border-t border-secondary bg-primary text-on-primary">
                <div className="mx-auto max-w-6xl px-4 py-10">
                    <div className="flex flex-col gap-10 pb-10 lg:flex-row lg:items-end lg:justify-between">
                        {hasFooterMenus ? (
                            <div className="grid flex-1 gap-8 text-sm grid-cols-2 md:grid-cols-3">
                                {footerMenus.map((group) => (
                                    <div key={group.id}>
                                        {group.url ? (
                                            <Link
                                                href={group.url}
                                                className="mb-3 block font-semibold opacity-95 hover:underline"
                                            >
                                                {group.title}
                                            </Link>
                                        ) : (
                                            <p className="mb-3 font-semibold opacity-95">{group.title}</p>
                                        )}
                                        <ul className="space-y-2 opacity-85">
                                            {group.items.map((item) => (
                                                <li key={item.id}>
                                                    {item.target_blank ? (
                                                        <a
                                                            href={item.url}
                                                            target="_blank"
                                                            rel="noopener noreferrer"
                                                            className="hover:underline"
                                                        >
                                                            {item.title}
                                                        </a>
                                                    ) : (
                                                        <Link href={item.url} className="hover:underline">
                                                            {item.title}
                                                        </Link>
                                                    )}
                                                </li>
                                            ))}
                                        </ul>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="flex-1" />
                        )}

                        <SiteFooterContactCard />
                    </div>

                    <p className="border-t border-white/10 pt-6 text-center text-sm opacity-90">
                        {app.name} — {tagline}
                    </p>
                </div>
            </footer>
        </div>
    );
}
