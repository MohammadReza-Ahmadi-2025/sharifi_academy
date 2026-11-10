import { SiteFooterContactCard } from '@/Components/Site/SiteFooterContactCard';
import { SiteDefaultHead } from '@/Components/Site/SiteDefaultHead';
import { SiteTheme } from '@/Components/Site/SiteTheme';
import { SiteHeader } from '@/Components/SiteHeader';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { PropsWithChildren } from 'react';

type SiteMenuGroup = {
    id: number;
    title: string;
    url?: string | null;
    items: Array<{ id: number; title: string; url: string; target_blank: boolean }>;
};

type Props = PropsWithChildren<{
    fullWidth?: boolean;
    mainClassName?: string;
}>;

export default function AppLayout({ children, fullWidth = false, mainClassName }: Props) {
    const pageProps = usePage<PageProps>().props;
    const { app, siteMenus } = pageProps;
    const footerMenus = (siteMenus?.footer ?? []) as SiteMenuGroup[];

    const hasFooterMenus = footerMenus.length > 0;

    return (
        <div className="flex min-h-screen flex-col bg-surface-muted">
            <SiteTheme />
            <SiteDefaultHead />
            <SiteHeader />

            <main
                className={cn(
                    'flex flex-1 flex-col items-stretch',
                    fullWidth ? 'w-full' : 'mx-auto w-full max-w-7xl px-4 py-8',
                    mainClassName,
                )}
            >
                {children}
            </main>

            <footer className="mt-auto border-t border-secondary bg-primary text-on-primary pb-[calc(6.5rem+env(safe-area-inset-bottom,0px))] lg:pb-0">
                <div className="mx-auto max-w-7xl px-4 py-10 lg:pb-10">
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
                        تمامی حقوق برای {app.name} محفوظ است.
                    </p>
                </div>
            </footer>
        </div>
    );
}
