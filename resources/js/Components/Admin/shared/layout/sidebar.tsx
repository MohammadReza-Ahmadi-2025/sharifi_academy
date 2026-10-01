import { getAdminNavIcon } from '@/Components/Admin/shared/config/admin-nav-icons';
import { useSidebar } from '@/Components/Admin/shared/contexts/sidebar-context';
import { isNavigationItemActive } from '@/Components/Admin/shared/lib/admin-navigation';
import { SidebarSystemStatus } from '@/Components/Admin/shared/layout/sidebar-system-status';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';

export function Sidebar() {
    const { app, adminPanel, url } = usePage<PageProps>().props;
    const pathname = url.replace(/^https?:\/\/[^/]+/, '');
    const { isCollapsed, isMobileOpen, closeMobileSidebar } = useSidebar();

    const navigation = adminPanel?.navigation ?? [];
    const visibleHrefs = navigation.map((item) => item.href);

    return (
        <>
            {isMobileOpen && (
                <div
                    className="fixed inset-0 z-40 bg-slate-900/40 backdrop-blur-sm transition-opacity lg:hidden"
                    onClick={closeMobileSidebar}
                />
            )}

            <aside
                className={cn(
                    'fixed inset-y-0 right-0 z-50 flex h-screen flex-col overflow-hidden border-l border-slate-200 bg-slate-50 transition-all duration-300 dark:border-slate-800 dark:bg-slate-950 lg:sticky lg:top-0',
                    isMobileOpen ? 'w-72 translate-x-0' : 'translate-x-full lg:translate-x-0',
                    isCollapsed ? 'lg:w-20' : 'lg:w-72',
                )}
            >
                <div className="shrink-0 border-b border-slate-200 px-4 py-5 dark:border-slate-800">
                    <div className="flex items-center gap-3">
                        <div className="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-slate-900 text-sm font-bold text-white dark:bg-slate-100 dark:text-slate-950">
                            {app.name.charAt(0)}
                        </div>

                        <div
                            className={cn(
                                'min-w-0 transition-opacity duration-200',
                                isCollapsed ? 'lg:hidden lg:opacity-0' : 'opacity-100',
                            )}
                        >
                            <h2 className="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">
                                پنل مدیریت
                            </h2>
                            <p className="mt-1 text-[10px] text-slate-500 dark:text-slate-400">{app.name}</p>
                        </div>
                    </div>
                </div>

                <div className="min-h-0 flex-1 overflow-y-auto px-4 py-5">
                    <div className={cn('mb-4 px-2', isCollapsed ? 'lg:hidden' : '')}>
                        <p className="text-[10px] font-semibold tracking-[0.12em] text-slate-400 dark:text-slate-500">
                            ناوبری سامانه
                        </p>
                    </div>

                    <nav className="space-y-1">
                        {navigation.length === 0 ? (
                            <p className="px-2 text-xs text-slate-500 dark:text-slate-400">
                                دسترسی فعالی برای نمایش منو وجود ندارد.
                            </p>
                        ) : null}

                        {navigation.map((item) => {
                            const isActive = isNavigationItemActive(pathname, item.href, visibleHrefs);
                            const Icon = getAdminNavIcon(item);

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    onClick={closeMobileSidebar}
                                    className={cn(
                                        'group relative flex items-center justify-between rounded-lg px-3 py-2.5 text-sm transition-all duration-150',
                                        isActive
                                            ? 'bg-white text-slate-900 shadow-sm ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-50 dark:ring-slate-800'
                                            : 'text-slate-600 hover:bg-white hover:text-slate-900 dark:text-slate-400 dark:hover:bg-slate-900 dark:hover:text-slate-100',
                                        isCollapsed ? 'lg:h-10 lg:justify-center lg:px-0' : '',
                                    )}
                                    title={isCollapsed ? item.label : undefined}
                                >
                                    <span
                                        className={cn(
                                            'absolute right-0 top-1/2 h-6 w-1 -translate-y-1/2 rounded-l-full transition-colors',
                                            isActive ? 'bg-slate-900 dark:bg-slate-100' : 'bg-transparent',
                                        )}
                                    />

                                    <div className="flex min-w-0 items-center gap-3">
                                        <Icon
                                            className={cn(
                                                'h-4 w-4 shrink-0 transition-colors',
                                                isActive
                                                    ? 'text-slate-900 dark:text-slate-100'
                                                    : 'text-slate-400 group-hover:text-slate-600 dark:text-slate-500 dark:group-hover:text-slate-300',
                                            )}
                                        />

                                        <span
                                            className={cn(
                                                'truncate font-medium text-slate-500 transition-opacity duration-200 dark:text-slate-400',
                                                isCollapsed ? 'lg:hidden' : '',
                                            )}
                                        >
                                            {item.label}
                                        </span>
                                    </div>

                                    {isActive && !isCollapsed ? (
                                        <span className="mr-3 text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                            جاری
                                        </span>
                                    ) : null}
                                </Link>
                            );
                        })}
                    </nav>
                </div>

                <div
                    className={cn(
                        'shrink-0 border-t border-slate-200 px-4 py-4 dark:border-slate-800',
                        isCollapsed ? 'lg:hidden' : '',
                    )}
                >
                    <SidebarSystemStatus />
                </div>
            </aside>
        </>
    );
}
