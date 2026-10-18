import { MegaMenuPanel } from '@/Components/MegaMenuPanel';
import { NavMenuLink } from '@/Components/NavMenuLink';
import { SiteHeaderAccountMenu } from '@/Components/Site/SiteHeaderAccountMenu';
import { SiteMobileTabBar } from '@/Components/Site/SiteMobileTabBar';
import { SiteHeaderNotificationButton } from '@/Components/Site/SiteHeaderNotificationButton';
import { SiteHeaderSearch } from '@/Components/Site/SiteHeaderSearch';
import { siteSettingsFromPage } from '@/lib/site-settings';
import { NavItem, PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
// import { ShoppingCart } from 'lucide-react'; // سبد خرید — بعداً فعال شود
import { useCallback, useEffect, useMemo, useRef, useState } from 'react';

function isGuestAuthNavItem(item: NavItem): boolean {
    const url = item.url.trim();
    if (url === '/login' || url === '/register') {
        return true;
    }

    return url.startsWith('/login?') || url.startsWith('/register?');
}

function hasMegaContent(item: NavItem): boolean {
    return item.type === 'mega' && Boolean(item.mega && item.mega.columns.length > 0);
}

const NAV_LINK_CLASS = 'rounded-lg px-3 py-2 hover:bg-primary-hover/80';

const HEADER_AUTH_CTA_CLASS =
    'shrink-0 rounded-full bg-neutral-white px-4 py-2 text-sm font-semibold text-primary shadow-sm ring-1 ring-black/5 transition hover:bg-neutral-white/90';

export function SiteHeader() {
    const pageProps = usePage<PageProps>().props;
    const { app, navigation, auth } = pageProps;
    const settings = siteSettingsFromPage(pageProps);
    const [activeMegaId, setActiveMegaId] = useState<string | null>(null);
    const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const openMega = useCallback((id: string) => {
        if (closeTimer.current) {
            clearTimeout(closeTimer.current);
            closeTimer.current = null;
        }
        setActiveMegaId(id);
    }, []);

    const scheduleCloseMega = useCallback(() => {
        if (closeTimer.current) {
            clearTimeout(closeTimer.current);
        }
        closeTimer.current = setTimeout(() => setActiveMegaId(null), 120);
    }, []);

    useEffect(() => {
        return () => {
            if (closeTimer.current) {
                clearTimeout(closeTimer.current);
            }
        };
    }, []);

    const headerAuthCta = useMemo(() => {
        const fromItems = navigation.items.find(isGuestAuthNavItem);
        if (fromItems) {
            return { label: fromItems.label, url: fromItems.url };
        }

        return navigation.cta;
    }, [navigation.items, navigation.cta]);

    const headerNavItems = useMemo(
        () => navigation.items.filter((item) => !isGuestAuthNavItem(item)),
        [navigation.items],
    );

    const activeMegaItem = headerNavItems.find((item) => item.id === activeMegaId && hasMegaContent(item));

    const logoImage = (
        <span className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-neutral-white">
            <img
                src={settings.site_logo ?? settings.site_icon ?? '/favicon.ico'}
                alt={app.name}
                className="size-9 object-contain"
            />
        </span>
    );

    const logoMobile = (
        <Link href="/" className="flex min-w-0 items-center justify-center font-bold">
            {logoImage}
        </Link>
    );

    const logoDesktop = (
        <Link href="/" className="flex min-w-0 items-center justify-center gap-2 font-bold">
            {logoImage}
            <span className="truncate">{app.name}</span>
        </Link>
    );

    return (
        <header
            className="sticky top-0 z-50 border-b border-white/15 bg-primary text-on-primary shadow-sm"
            onMouseLeave={scheduleCloseMega}
        >
            {/* موبایل RTL: جستجو راست | لوگو وسط | اعلان چپ */}
            <div className="mx-auto grid w-full max-w-7xl grid-cols-3 items-center gap-2 px-4 py-3 lg:hidden">
                <SiteHeaderSearch variant="icon" className="justify-self-start" />

                <div className="min-w-0 justify-self-center">{logoMobile}</div>

                <SiteHeaderNotificationButton className="justify-self-end" />
            </div>

            {/* دسکتاپ: لوگو | منو | جستجو | حساب | ورود/ثبت‌نام (چپ) */}
            <div className="mx-auto hidden w-full max-w-7xl items-center gap-3 px-4 py-3 lg:flex">
                {logoDesktop}

                <nav
                    className="flex min-w-0 flex-1 flex-wrap items-center gap-1 text-sm"
                    aria-label="منوی اصلی"
                >
                        {headerNavItems.map((item) => {
                            if (item.type === 'mega' && hasMegaContent(item)) {
                                const isOpen = activeMegaId === item.id;

                                return (
                                    <div key={item.id} className="relative" onMouseEnter={() => openMega(item.id)}>
                                        <button
                                            type="button"
                                            className={`flex items-center gap-1 rounded-lg px-3 py-2 transition ${
                                                isOpen ? 'bg-primary-hover' : 'hover:bg-primary-hover/80'
                                            }`}
                                            aria-expanded={isOpen}
                                            aria-haspopup="true"
                                            onFocus={() => openMega(item.id)}
                                        >
                                            {item.label}
                                            <ChevronDown className={`size-4 transition ${isOpen ? 'rotate-180' : ''}`} />
                                        </button>
                                    </div>
                                );
                            }

                            return (
                                <div key={item.id} className="px-1">
                                    <NavMenuLink url={item.url} label={item.label} className={NAV_LINK_CLASS} />
                                </div>
                            );
                        })}
                </nav>

                <SiteHeaderSearch className="w-32 shrink-0 lg:w-36 xl:w-44" />

                <SiteHeaderNotificationButton />

                {auth.account && <SiteHeaderAccountMenu className="shrink-0" />}

                {headerAuthCta && (
                    <Link href={headerAuthCta.url} className={HEADER_AUTH_CTA_CLASS}>
                        {headerAuthCta.label}
                    </Link>
                )}
            </div>

            {activeMegaItem?.mega && (
                <div
                    className="absolute inset-x-0 top-full hidden overflow-hidden border-t border-secondary bg-neutral-white text-neutral-black shadow-xl lg:block"
                    onMouseEnter={() => openMega(activeMegaItem.id)}
                >
                    <div className="mx-auto max-w-7xl">
                        <MegaMenuPanel mega={activeMegaItem.mega} />
                    </div>
                </div>
            )}

            <SiteMobileTabBar />
        </header>
    );
}
