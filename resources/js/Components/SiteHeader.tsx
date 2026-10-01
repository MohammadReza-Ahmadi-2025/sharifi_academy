import { MegaMenuPanel } from '@/Components/MegaMenuPanel';
import { NavMenuLink } from '@/Components/NavMenuLink';
import { NavItem, PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, Menu, ShoppingCart, X } from 'lucide-react';
import { useCallback, useEffect, useRef, useState } from 'react';

function hasMegaContent(item: NavItem): boolean {
    return item.type === 'mega' && Boolean(item.mega && item.mega.columns.length > 0);
}

/** مدت بستن دراور — باید با duration کلاس‌های transition یکی باشد */
const MOBILE_DRAWER_MS = 650;
const MOBILE_DRAWER_ENTER_DELAY_MS = 40;

const NAV_LINK_CLASS = 'rounded-lg px-3 py-2 hover:bg-primary-hover/80';
const AUTH_LINK_CLASS =
    'rounded-full bg-neutral-white px-4 py-2 font-semibold text-primary shadow-sm transition hover:bg-secondary';
const AUTH_LINK_MOBILE_CLASS =
    'block rounded-full bg-neutral-white py-2.5 text-center font-semibold text-primary shadow-sm transition hover:bg-secondary';

function isAuthNavItem(item: NavItem): boolean {
    return item.id === 'auth';
}

export function SiteHeader() {
    const { app, navigation } = usePage<PageProps>().props;
    const [mobileMounted, setMobileMounted] = useState(false);
    const [mobileEntered, setMobileEntered] = useState(false);
    const [mobileExpanded, setMobileExpanded] = useState<string | null>(null);
    const [activeMegaId, setActiveMegaId] = useState<string | null>(null);
    const closeTimer = useRef<ReturnType<typeof setTimeout> | null>(null);
    const mobileCloseTimer = useRef<ReturnType<typeof setTimeout> | null>(null);

    const closeMobile = useCallback(() => {
        setMobileExpanded(null);
        setMobileEntered(false);
        if (mobileCloseTimer.current) {
            clearTimeout(mobileCloseTimer.current);
        }
        mobileCloseTimer.current = setTimeout(() => {
            setMobileMounted(false);
            setMobileEntered(false);
            mobileCloseTimer.current = null;
        }, MOBILE_DRAWER_MS);
    }, []);

    const openMobile = useCallback(() => {
        if (mobileCloseTimer.current) {
            clearTimeout(mobileCloseTimer.current);
            mobileCloseTimer.current = null;
        }
        if (!mobileMounted) {
            setMobileMounted(true);
        } else {
            setMobileEntered(true);
        }
    }, [mobileMounted]);

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
        if (!mobileMounted) {
            setMobileEntered(false);
            return;
        }

        setMobileEntered(false);
        const timer = window.setTimeout(() => setMobileEntered(true), MOBILE_DRAWER_ENTER_DELAY_MS);

        return () => clearTimeout(timer);
    }, [mobileMounted]);

    useEffect(() => {
        if (!mobileMounted) {
            return;
        }

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                closeMobile();
            }
        };

        window.addEventListener('keydown', onKeyDown);

        return () => {
            document.body.style.overflow = previousOverflow;
            window.removeEventListener('keydown', onKeyDown);
        };
    }, [mobileMounted, closeMobile]);

    useEffect(() => {
        return () => {
            if (closeTimer.current) {
                clearTimeout(closeTimer.current);
            }
            if (mobileCloseTimer.current) {
                clearTimeout(mobileCloseTimer.current);
            }
        };
    }, []);

    const activeMegaItem = navigation.items.find((item) => item.id === activeMegaId && hasMegaContent(item));

    const logo = (
        <Link
            href="/"
            className="flex min-w-0 items-center justify-center gap-2 font-bold"
            onClick={closeMobile}
        >
            <span className="flex size-9 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-neutral-white">
                <img src="/favicon.ico" alt="" className="size-9" />
            </span>
            <span className="truncate">{app.name}</span>
        </Link>
    );

    return (
        <header
            className="relative sticky top-0 z-50 border-b border-white/15 bg-primary text-on-primary shadow-sm"
            onMouseLeave={scheduleCloseMega}
        >
            {/* موبایل: منو راست | لوگو وسط | سبد چپ */}
            <div className="mx-auto grid w-full max-w-6xl grid-cols-3 items-center gap-2 px-4 py-3 lg:hidden">
                <button
                    type="button"
                    className="justify-self-start rounded-lg p-2 hover:bg-primary-hover"
                    onClick={openMobile}
                    aria-expanded={mobileMounted}
                    aria-haspopup="dialog"
                    aria-label="باز کردن منو"
                >
                    <Menu className="size-6" />
                </button>

                <div className="min-w-0 justify-self-center">{logo}</div>

                <Link
                    href="/#books"
                    className="justify-self-end rounded-lg p-2 hover:bg-primary-hover"
                    aria-label="سبد خرید"
                    onClick={closeMobile}
                >
                    <ShoppingCart className="size-6" />
                </Link>
            </div>

            {/* دسکتاپ */}
            <div className="mx-auto hidden w-full max-w-6xl items-center justify-between gap-4 px-4 py-3 lg:flex">
                {logo}

                <nav className="flex items-center gap-1 text-sm" aria-label="منوی اصلی">
                    {navigation.items.map((item) => {
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
                            <div key={item.id} className={isAuthNavItem(item) ? 'ms-2 px-1' : 'px-1'}>
                                <NavMenuLink
                                    url={item.url}
                                    label={item.label}
                                    className={isAuthNavItem(item) ? AUTH_LINK_CLASS : NAV_LINK_CLASS}
                                />
                            </div>
                        );
                    })}
                    {navigation.cta && (
                        <Link
                            href={navigation.cta.url}
                            className="ms-2 rounded-full bg-neutral-white px-4 py-2 font-semibold text-primary transition hover:bg-secondary"
                        >
                            {navigation.cta.label}
                        </Link>
                    )}
                </nav>
            </div>

            {activeMegaItem?.mega && (
                <div
                    className="absolute inset-x-0 top-full hidden overflow-hidden border-t border-secondary bg-neutral-white text-neutral-black shadow-xl lg:block"
                    onMouseEnter={() => openMega(activeMegaItem.id)}
                >
                    <div className="mx-auto max-w-6xl">
                        <MegaMenuPanel mega={activeMegaItem.mega} />
                    </div>
                </div>
            )}

            {mobileMounted && (
                <>
                    <button
                        type="button"
                        className={`fixed inset-0 z-[60] bg-neutral-black/45 transition-opacity duration-[650ms] ease-[cubic-bezier(0.4,0,0.2,1)] lg:hidden ${
                            mobileEntered ? 'opacity-100' : 'opacity-0'
                        }`}
                        aria-label="بستن منو"
                        onClick={closeMobile}
                    />

                    <aside
                        className={`fixed inset-y-0 right-0 z-[70] flex w-[min(88vw,20rem)] flex-col bg-primary text-on-primary shadow-2xl transition-transform duration-[650ms] ease-[cubic-bezier(0.22,1,0.36,1)] will-change-transform lg:hidden ${
                            mobileEntered ? 'translate-x-0' : 'translate-x-full'
                        }`}
                        role="dialog"
                        aria-modal="true"
                        aria-label="منوی موبایل"
                    >
                        <div className="flex shrink-0 items-center justify-between border-b border-white/15 px-4 py-3">
                            <span className="text-sm font-semibold">منو</span>
                            <button
                                type="button"
                                className="rounded-lg p-2 hover:bg-primary-hover"
                                onClick={closeMobile}
                                aria-label="بستن منو"
                            >
                                <X className="size-6" />
                            </button>
                        </div>

                        <nav className="flex min-h-0 flex-1 flex-col overflow-hidden">
                            <ul className="min-h-0 flex-1 space-y-1 overflow-y-auto overscroll-contain px-4 py-3 text-sm">
                                {navigation.items.map((item) => {
                                    if (item.type === 'mega' && hasMegaContent(item)) {
                                        const expanded = mobileExpanded === item.id;

                                        return (
                                            <li key={item.id} className="border-b border-white/10 pb-2">
                                                <button
                                                    type="button"
                                                    className="flex w-full items-center justify-between py-2.5 font-medium"
                                                    onClick={() => setMobileExpanded(expanded ? null : item.id)}
                                                    aria-expanded={expanded}
                                                >
                                                    {item.label}
                                                    <ChevronDown
                                                        className={`size-5 shrink-0 transition ${expanded ? 'rotate-180' : ''}`}
                                                    />
                                                </button>
                                                {expanded && item.mega && (
                                                    <div className="mb-3 mt-1">
                                                        <MegaMenuPanel mega={item.mega} compact onNavigate={closeMobile} />
                                                    </div>
                                                )}
                                            </li>
                                        );
                                    }

                                    if (isAuthNavItem(item)) {
                                        return (
                                            <li key={item.id} className="mt-4 border-t border-white/15 pt-4">
                                                <NavMenuLink
                                                    url={item.url}
                                                    label={item.label}
                                                    className={AUTH_LINK_MOBILE_CLASS}
                                                    onNavigate={closeMobile}
                                                />
                                            </li>
                                        );
                                    }

                                    return (
                                        <li key={item.id} className="border-b border-white/10 py-2.5">
                                            <NavMenuLink url={item.url} label={item.label} onNavigate={closeMobile} />
                                        </li>
                                    );
                                })}
                            </ul>

                            {navigation.cta && (
                                <div className="shrink-0 border-t border-white/15 px-4 py-3">
                                    <Link
                                        href={navigation.cta.url}
                                        className="block rounded-full bg-neutral-white px-4 py-2.5 text-center font-semibold text-primary"
                                        onClick={closeMobile}
                                    >
                                        {navigation.cta.label}
                                    </Link>
                                </div>
                            )}
                        </nav>
                    </aside>
                </>
            )}
        </header>
    );
}
