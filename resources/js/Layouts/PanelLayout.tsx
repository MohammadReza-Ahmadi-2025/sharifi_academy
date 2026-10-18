import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ChevronLeft, GraduationCap } from 'lucide-react';
import { PropsWithChildren } from 'react';

type NavLink = { href: string; label: string };

type Props = PropsWithChildren<{
    title: string;
    homeHref: string;
    links: NavLink[];
    /** زیرعنوان صفحه در هدر کنار عنوان */
    description?: string;
    /** داشبورد چند بخشی: plain — بقیه صفحات: card */
    contentVariant?: 'card' | 'plain';
}>;

function isNavActive(href: string, currentUrl: string): boolean {
    const path = currentUrl.split('?')[0];
    if (href === '/instructor') {
        return path === '/instructor' || path === '/instructor/';
    }

    return path === href || path.startsWith(`${href}/`);
}

export default function PanelLayout({
    title,
    homeHref,
    links,
    description,
    contentVariant = 'card',
    children,
}: Props) {
    const { app, auth, url } = usePage<PageProps>().props;
    const displayName = auth.account?.display_name ?? auth.user?.name ?? 'کاربر';

    return (
        <div className="min-h-screen bg-slate-50">
            <header className="border-b border-slate-200/80 bg-white shadow-sm">
                <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between">
                    <div className="flex items-start gap-3">
                        <div
                            className="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary/10 text-primary"
                            aria-hidden
                        >
                            <GraduationCap className="size-6" />
                        </div>
                        <div>
                            <Link href="/" className="text-xs font-medium text-slate-500 transition hover:text-primary">
                                {app.name}
                            </Link>
                            <h1 className="text-lg font-bold text-slate-900 md:text-xl">{title}</h1>
                            {description && (
                                <p className="mt-0.5 max-w-xl text-sm text-slate-600">{description}</p>
                            )}
                        </div>
                    </div>
                    <div className="flex flex-wrap items-center gap-3 text-sm">
                        <span className="rounded-full bg-slate-100 px-3 py-1 text-slate-700">{displayName}</span>
                        <Link
                            href={homeHref}
                            className="inline-flex items-center gap-1 font-medium text-primary hover:text-primary-hover"
                        >
                            <ChevronLeft className="size-4 rtl:rotate-180" aria-hidden />
                            بازگشت
                        </Link>
                    </div>
                </div>
            </header>

            <div className="mx-auto grid max-w-7xl gap-6 px-4 py-6 lg:grid-cols-[240px_1fr]">
                <aside className="h-fit rounded-2xl border border-slate-200/80 bg-white p-3 shadow-sm lg:sticky lg:top-6">
                    <p className="px-3 pb-2 pt-1 text-[11px] font-semibold uppercase tracking-wide text-slate-400">
                        منوی پنل
                    </p>
                    <nav className="flex flex-col gap-0.5">
                        {links.map((link) => {
                            const active = isNavActive(link.href, url);

                            return (
                                <Link
                                    key={link.href}
                                    href={link.href}
                                    className={cn(
                                        'rounded-xl px-3 py-2.5 text-sm font-medium transition',
                                        active
                                            ? 'bg-primary/10 text-primary shadow-[inset_0_0_0_1px_rgb(var(--color-primary-rgb)/0.15)]'
                                            : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900',
                                    )}
                                    aria-current={active ? 'page' : undefined}
                                >
                                    {link.label}
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                <main
                    className={cn(
                        'min-w-0',
                        contentVariant === 'card' &&
                            'rounded-2xl border border-slate-200/80 bg-white p-6 shadow-sm md:p-8',
                    )}
                >
                    {children}
                </main>
            </div>
        </div>
    );
}
