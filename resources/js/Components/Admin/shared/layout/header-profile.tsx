import { ProfileThemeMenuItem } from '@/Components/Admin/shared/layout/profile-theme-menu-item';
import { mapAuthUserToProfile } from '@/Components/Admin/shared/lib/profile-user';
import { PopoverPanel } from '@/Components/Admin/shared/ui/popover-panel';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, ExternalLink, LogOut, UserRound } from 'lucide-react';
import { useState } from 'react';

export function HeaderProfile() {
    const { auth } = usePage<PageProps>().props;
    const [isOpen, setIsOpen] = useState(false);

    const profileUser = auth.user ? mapAuthUserToProfile(auth.user) : null;

    if (!profileUser) {
        return (
            <Link
                href="/login"
                className="rounded-lg border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 transition-colors hover:bg-slate-50 dark:border-slate-800 dark:text-slate-300 dark:hover:bg-slate-900"
            >
                ورود
            </Link>
        );
    }

    return (
        <PopoverPanel
            isOpen={isOpen}
            onOpenChange={setIsOpen}
            align="start"
            panelClassName="w-64"
            trigger={
                <button
                    type="button"
                    aria-label="منوی پروفایل"
                    aria-expanded={isOpen}
                    className={cn(
                        'flex items-center gap-3 rounded-lg border border-transparent px-1 py-1 transition-colors',
                        'hover:bg-slate-50 dark:hover:bg-slate-900',
                        isOpen && 'bg-slate-50 dark:bg-slate-900',
                    )}
                >
                    <div className="hidden text-right sm:block">
                        <p className="text-xs font-semibold text-slate-800 dark:text-slate-200">{profileUser.name}</p>
                        <p className="text-[10px] text-slate-500 dark:text-slate-400">{profileUser.roleLabel}</p>
                    </div>

                    {profileUser.avatarUrl ? (
                        <img
                            src={profileUser.avatarUrl}
                            alt={profileUser.avatarAlt ?? profileUser.name}
                            className="h-9 w-9 rounded-lg object-cover ring-1 ring-slate-200 dark:ring-slate-800"
                        />
                    ) : (
                        <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-xs font-bold text-slate-700 ring-1 ring-slate-200 dark:bg-slate-900 dark:text-slate-300 dark:ring-slate-800">
                            {profileUser.initials}
                        </div>
                    )}

                    <ChevronDown
                        className={cn(
                            'hidden h-4 w-4 text-slate-400 transition-transform sm:block',
                            isOpen && 'rotate-180',
                        )}
                    />
                </button>
            }
        >
            <div className="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">{profileUser.name}</p>
                <p className="mt-0.5 text-xs text-slate-500 dark:text-slate-400">
                    {profileUser.phone || profileUser.email}
                </p>
            </div>

            <div className="p-2">
                <Link
                    href="/admin/profile"
                    onClick={() => setIsOpen(false)}
                    className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <UserRound className="h-4 w-4 text-slate-400" />
                    پروفایل من
                </Link>

                <ProfileThemeMenuItem />

                <Link
                    href="/"
                    onClick={() => setIsOpen(false)}
                    className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-slate-700 transition-colors hover:bg-slate-50 dark:text-slate-200 dark:hover:bg-slate-800"
                >
                    <ExternalLink className="h-4 w-4 text-slate-400" />
                    مشاهده سایت
                </Link>
            </div>

            <div className="border-t border-slate-100 p-2 dark:border-slate-800">
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    onClick={() => setIsOpen(false)}
                    className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50 dark:text-rose-400 dark:hover:bg-rose-950/20"
                >
                    <LogOut className="h-4 w-4" />
                    خروج از حساب
                </Link>
            </div>
        </PopoverPanel>
    );
}
