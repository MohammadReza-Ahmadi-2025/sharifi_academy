import { PopoverPanel } from '@/Components/Admin/shared/ui/popover-panel';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronDown, LayoutDashboard, LogOut, UserRound } from 'lucide-react';
import { useState } from 'react';

type Props = {
    className?: string;
    onNavigate?: () => void;
    /** استایل دکمه روی هدر رنگی سایت */
    variant?: 'header' | 'drawer';
};

export function SiteHeaderAccountMenu({ className, onNavigate, variant = 'header' }: Props) {
    const { auth } = usePage<PageProps>().props;
    const account = auth.account;
    const user = auth.user;
    const [isOpen, setIsOpen] = useState(false);

    if (!account || !user) {
        return null;
    }

    const close = () => {
        setIsOpen(false);
        onNavigate?.();
    };

    const initials = account.display_name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0])
        .join('');

    const triggerClass =
        variant === 'header'
            ? cn(
                  'flex max-w-[12rem] items-center gap-2 rounded-lg px-3 py-2 text-sm transition',
                  isOpen ? 'bg-primary-hover' : 'hover:bg-primary-hover/80',
              )
            : 'flex w-full items-center justify-between rounded-lg px-2 py-2.5 text-sm font-medium';

    return (
        <div className={className}>
            <PopoverPanel
                isOpen={isOpen}
                onOpenChange={setIsOpen}
                align="start"
                panelClassName="w-72 text-neutral-black"
                trigger={
                    <button
                        type="button"
                        aria-label="منوی حساب کاربری"
                        aria-expanded={isOpen}
                        className={triggerClass}
                    >
                        {user.avatar?.url ? (
                            <img
                                src={user.avatar.url}
                                alt={user.avatar.alt ?? account.display_name}
                                className="size-8 shrink-0 rounded-lg object-cover ring-1 ring-white/30"
                            />
                        ) : (
                            <span
                                className="flex size-8 shrink-0 items-center justify-center rounded-lg bg-neutral-white/15 text-xs font-bold"
                                aria-hidden
                            >
                                {initials || '؟'}
                            </span>
                        )}
                        <span className="min-w-0 truncate font-semibold">{account.display_name}</span>
                        <ChevronDown
                            className={cn('size-4 shrink-0 opacity-80 transition', isOpen && 'rotate-180')}
                        />
                    </button>
                }
            >
                <div className="border-b border-secondary px-4 py-3">
                    <p className="text-sm font-bold text-neutral-black">{account.display_name}</p>
                    <p className="mt-0.5 text-xs text-neutral-black/60">{account.role_label}</p>
                    {(user.mobile || user.email) && (
                        <p className="mt-1 text-xs text-neutral-black/50">{user.mobile || user.email}</p>
                    )}
                </div>

                <div className="p-2">
                    {account.panel && (
                        <Link
                            href={account.panel.url}
                            onClick={close}
                            className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-neutral-black transition hover:bg-surface-muted"
                        >
                            <LayoutDashboard className="size-4 text-primary" />
                            {account.panel.label}
                        </Link>
                    )}

                    <Link
                        href={account.profile_url}
                        onClick={close}
                        className="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm text-neutral-black transition hover:bg-surface-muted"
                    >
                        <UserRound className="size-4 text-neutral-black/45" />
                        پروفایل من
                    </Link>
                </div>

                <div className="border-t border-secondary p-2">
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        onClick={close}
                        className="flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium text-rose-600 transition hover:bg-rose-50"
                    >
                        <LogOut className="size-4" />
                        خروج از حساب
                    </Link>
                </div>
            </PopoverPanel>
        </div>
    );
}
