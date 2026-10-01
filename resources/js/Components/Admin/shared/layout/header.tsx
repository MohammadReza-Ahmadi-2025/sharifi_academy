import { getAdminBreadcrumbs } from '@/Components/Admin/shared/config/admin-breadcrumbs';
import { ThemeToggle } from '@/Components/Admin/shared/components/theme-toggle';
import { useSidebar } from '@/Components/Admin/shared/contexts/sidebar-context';
import { HeaderDateTime } from '@/Components/Admin/shared/layout/header-datetime';
import { HeaderProfile } from '@/Components/Admin/shared/layout/header-profile';
import { Breadcrumb } from '@/Components/Admin/shared/ui/breadcrumb';
import { AdminInput } from '@/Components/Admin/shared/ui/input';
import type { PageProps } from '@/types';
import { router, usePage } from '@inertiajs/react';
import { CornerDownLeft, Menu, Search } from 'lucide-react';
import { useEffect, useMemo, useRef, useState } from 'react';
import { getAdminNavIcon } from '@/Components/Admin/shared/config/admin-nav-icons';

export function Header() {
    const { adminPanel, url } = usePage<PageProps>().props;
    const pathname = url.replace(/^https?:\/\/[^/]+/, '');
    const navigation = adminPanel?.navigation ?? [];

    const [shortcutText, setShortcutText] = useState('Ctrl + K');
    const [query, setQuery] = useState('');
    const [isSuggestOpen, setIsSuggestOpen] = useState(false);

    const searchInputRef = useRef<HTMLInputElement | null>(null);
    const suggestBoxRef = useRef<HTMLDivElement | null>(null);

    const { toggleSidebar } = useSidebar();

    const breadcrumbs = useMemo(() => getAdminBreadcrumbs(pathname), [pathname]);

    useEffect(() => {
        const isMac = typeof window !== 'undefined' && navigator.userAgent.toLowerCase().includes('mac');

        setShortcutText(isMac ? '⌘K' : 'Ctrl + K');

        const handleKeyDown = (event: KeyboardEvent) => {
            const isK = event.key.toLowerCase() === 'k';
            const isSearchShortcut = isMac ? event.metaKey && isK : event.ctrlKey && isK;

            if (!isSearchShortcut) {
                return;
            }

            event.preventDefault();

            if (searchInputRef.current) {
                searchInputRef.current.focus();
                searchInputRef.current.select();
                setIsSuggestOpen(true);
            }
        };

        window.addEventListener('keydown', handleKeyDown);

        return () => {
            window.removeEventListener('keydown', handleKeyDown);
        };
    }, []);

    useEffect(() => {
        const handleClickOutside = (event: MouseEvent) => {
            if (suggestBoxRef.current && !suggestBoxRef.current.contains(event.target as Node)) {
                setIsSuggestOpen(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    const suggestedRoutes = useMemo(() => {
        const normalizedQuery = query.trim().toLowerCase();

        if (!normalizedQuery) {
            return navigation.slice(0, 5);
        }

        return navigation
            .filter(
                (item) =>
                    item.label.toLowerCase().includes(normalizedQuery) ||
                    item.href.toLowerCase().includes(normalizedQuery),
            )
            .slice(0, 5);
    }, [navigation, query]);

    const handleNavigate = (href: string) => {
        setIsSuggestOpen(false);
        setQuery('');
        router.visit(href);
    };

    const handleKeyAction = (event: React.KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter' && suggestedRoutes.length > 0) {
            event.preventDefault();
            handleNavigate(suggestedRoutes[0].href);
        }

        if (event.key === 'Escape') {
            setIsSuggestOpen(false);
        }
    };

    return (
        <header className="flex h-16 items-center justify-between border-b border-slate-200 bg-white px-6 dark:border-slate-800 dark:bg-slate-950">
            <div className="flex items-center gap-4">
                <button
                    type="button"
                    onClick={toggleSidebar}
                    className="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-slate-200 text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800"
                    aria-label="Toggle Sidebar"
                >
                    <Menu className="h-5 w-5" />
                </button>

                <div className="hidden min-w-0 sm:block">
                    <Breadcrumb items={breadcrumbs} />
                </div>

                <div className="min-w-0 sm:hidden">
                    <p className="truncate text-xs font-semibold text-slate-900 dark:text-slate-100">
                        {breadcrumbs[breadcrumbs.length - 1]?.label}
                    </p>
                </div>
            </div>

            <div className="flex items-center gap-3 sm:gap-4">
                <HeaderDateTime />

                <div ref={suggestBoxRef} className="relative hidden w-72 md:block">
                    <div className="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <Search className="h-4 w-4 text-slate-400 dark:text-slate-500" />
                    </div>

                    <AdminInput
                        ref={searchInputRef}
                        type="text"
                        value={query}
                        onChange={(event) => {
                            setQuery(event.target.value);
                            setIsSuggestOpen(true);
                        }}
                        onFocus={() => setIsSuggestOpen(true)}
                        onKeyDown={handleKeyAction}
                        placeholder="جستجو در مسیرها..."
                        className="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-12 pr-9 text-xs text-slate-900 outline-none transition-all placeholder:text-slate-400 focus:border-slate-400 focus:bg-white dark:border-slate-800 dark:bg-slate-900 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-slate-700 dark:focus:bg-slate-950"
                    />

                    <div className="absolute inset-y-0 left-0 flex items-center pl-3">
                        <kbd className="pointer-events-none inline-flex h-5 select-none items-center rounded border border-slate-200 bg-white px-1.5 font-sans text-[10px] font-medium text-slate-400 dark:border-slate-800 dark:bg-slate-950 dark:text-slate-500">
                            {shortcutText}
                        </kbd>
                    </div>

                    {isSuggestOpen && (
                        <div className="absolute left-0 right-0 top-[calc(100%+8px)] z-50 overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg dark:border-slate-800 dark:bg-slate-900">
                            <div className="border-b border-slate-100 px-3 py-2 text-[11px] text-slate-500 dark:border-slate-800 dark:text-slate-400">
                                مسیرهای پیشنهادی
                            </div>

                            <div className="max-h-72 overflow-y-auto p-2">
                                {suggestedRoutes.length > 0 ? (
                                    suggestedRoutes.map((item) => {
                                        const Icon = getAdminNavIcon(item);

                                        return (
                                            <button
                                                key={item.href}
                                                type="button"
                                                onClick={() => handleNavigate(item.href)}
                                                className="flex w-full items-center justify-between rounded-lg px-3 py-2 text-right transition-colors hover:bg-slate-50 dark:hover:bg-slate-800"
                                            >
                                                <div className="flex items-center gap-3">
                                                    <Icon className="h-4 w-4 text-slate-400 dark:text-slate-500" />

                                                    <div className="flex flex-col items-start">
                                                        <span className="text-xs font-medium text-slate-800 dark:text-slate-100">
                                                            {item.label}
                                                        </span>
                                                        <span className="text-[10px] text-slate-500 dark:text-slate-400">
                                                            {item.href}
                                                        </span>
                                                    </div>
                                                </div>

                                                <span className="inline-flex items-center gap-1 text-[10px] text-slate-400 dark:text-slate-500">
                                                    <CornerDownLeft className="h-3.5 w-3.5" />
                                                    Enter
                                                </span>
                                            </button>
                                        );
                                    })
                                ) : (
                                    <div className="px-3 py-4 text-center text-xs text-slate-500 dark:text-slate-400">
                                        موردی پیدا نشد
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </div>

                <ThemeToggle />

                <HeaderProfile />
            </div>
        </header>
    );
}
