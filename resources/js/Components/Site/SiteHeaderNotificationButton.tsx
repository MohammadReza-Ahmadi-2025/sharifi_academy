import { Dialog } from '@/Components/Admin/shared/ui/dialog';
import { cn } from '@/lib/utils';
import type { PageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/react';
import { Bell, Inbox } from 'lucide-react';
import { useState } from 'react';

type Props = {
    className?: string;
    showBadge?: boolean;
};

export function SiteHeaderNotificationButton({ className, showBadge = true }: Props) {
    const { auth } = usePage<PageProps>().props;
    const notifications = auth.notifications;
    const [open, setOpen] = useState(false);

    const unreadCount = notifications?.unread_count ?? 0;
    const items = notifications?.items ?? [];
    const isLoggedIn = auth.user !== null;

    return (
        <>
            <button
                type="button"
                className={cn(
                    'relative shrink-0 rounded-lg p-2 text-on-primary transition hover:bg-primary-hover',
                    className,
                )}
                aria-label="اعلان‌ها"
                aria-haspopup="dialog"
                aria-expanded={open}
                onClick={() => setOpen(true)}
            >
                <Bell className="size-6" aria-hidden />
                {showBadge && isLoggedIn && unreadCount > 0 && (
                    <span
                        className="absolute top-1 flex min-w-[1.125rem] items-center justify-center rounded-full bg-amber-400 px-1 text-[10px] font-bold text-neutral-black ring-2 ring-primary end-1.5"
                        aria-hidden
                    >
                        {unreadCount > 9 ? '۹+' : unreadCount}
                    </span>
                )}
            </button>

            <Dialog
                isOpen={open}
                onClose={() => setOpen(false)}
                title="اعلان‌ها"
                panelClassName="max-h-[min(32rem,85vh)] w-full max-w-md"
                contentClassName="p-0"
            >
                {!isLoggedIn ? (
                    <div className="space-y-4 px-6 py-8 text-center">
                        <p className="text-sm text-slate-600">برای مشاهده اعلان‌ها وارد حساب کاربری شوید.</p>
                        <Link
                            href="/login"
                            className="inline-flex rounded-full bg-primary px-5 py-2.5 text-sm font-semibold text-on-primary transition hover:opacity-90"
                            onClick={() => setOpen(false)}
                        >
                            ورود / ثبت‌نام
                        </Link>
                    </div>
                ) : items.length === 0 ? (
                    <div className="flex flex-col items-center gap-3 px-6 py-12 text-center text-slate-500">
                        <Inbox className="size-12 text-slate-300" aria-hidden />
                        <p className="text-sm font-medium text-slate-700">هنوز اعلانی ندارید</p>
                        <p className="text-xs text-slate-500">وقتی اعلان جدیدی برای شما ثبت شود، اینجا نمایش داده می‌شود.</p>
                    </div>
                ) : (
                    <>
                        {unreadCount > 0 && (
                            <div className="border-b border-secondary/70 px-5 py-2 text-end">
                                <button
                                    type="button"
                                    className="text-xs font-medium text-primary hover:underline"
                                    onClick={() =>
                                        router.post('/account/notifications/read-all', {}, { preserveScroll: true })
                                    }
                                >
                                    علامت‌گذاری همه به‌عنوان خوانده‌شده
                                </button>
                            </div>
                        )}
                        <ul className="max-h-[min(24rem,60vh)] divide-y divide-secondary/70 overflow-y-auto overscroll-contain">
                            {items.map((item) => (
                                <li
                                    key={item.id}
                                    className={cn(
                                        'px-5 py-4 text-start transition',
                                        !item.read && 'bg-primary/5',
                                    )}
                                >
                                    <button
                                        type="button"
                                        className="w-full text-start"
                                        onClick={() => {
                                            if (!item.read) {
                                                router.post(
                                                    `/account/notifications/${item.id}/read`,
                                                    {},
                                                    { preserveScroll: true },
                                                );
                                            }
                                            if (item.url) {
                                                setOpen(false);
                                                router.visit(item.url);
                                            }
                                        }}
                                    >
                                        <div className="flex items-start gap-2">
                                            {!item.read && (
                                                <span
                                                    className="mt-1.5 size-2 shrink-0 rounded-full bg-primary"
                                                    aria-label="خوانده نشده"
                                                />
                                            )}
                                            <div className="min-w-0 flex-1">
                                                <p className="text-sm font-semibold text-neutral-black">{item.title}</p>
                                                <p className="mt-1 text-sm leading-relaxed text-slate-600">
                                                    {item.message}
                                                </p>
                                                {item.created_at && (
                                                    <p className="mt-2 text-xs text-slate-400">{item.created_at}</p>
                                                )}
                                            </div>
                                        </div>
                                    </button>
                                </li>
                            ))}
                        </ul>
                    </>
                )}
            </Dialog>
        </>
    );
}
