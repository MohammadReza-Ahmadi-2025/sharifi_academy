import { studentPanelLinks, type StudentPanelLink } from '@/lib/student-panel-links';
import { cn } from '@/lib/utils';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { ArrowUpLeft, BookOpen, Home } from 'lucide-react';
import { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
    title: string;
    description?: string;
    contentVariant?: 'panel' | 'plain';
    links?: StudentPanelLink[];
}>;

function isNavActive(href: string, currentUrl: string): boolean {
    const path = currentUrl.split('?')[0];
    if (href === '/student/dashboard') {
        return path === '/student/dashboard';
    }
    if (href === '/profile') {
        return path === '/profile' || path.startsWith('/profile/');
    }

    return path === href || path.startsWith(`${href}/`);
}

export default function StudentLayout({
    title,
    description,
    contentVariant = 'panel',
    links = studentPanelLinks,
    children,
}: Props) {
    const { app, auth, url } = usePage<PageProps>().props;
    const displayName = auth.account?.display_name ?? auth.user?.name ?? 'دانشجو';
    const currentPath = url.split('?')[0];
    const otherPanels = (auth.account?.panels ?? []).filter((panel) => {
        const panelPath = panel.url.split('?')[0];
        if (panelPath === '/student/dashboard' || panelPath.startsWith('/student')) {
            return !currentPath.startsWith('/student');
        }
        if (panelPath === '/instructor' || panelPath.startsWith('/instructor')) {
            return !currentPath.startsWith('/instructor');
        }
        if (panelPath === '/admin' || panelPath.startsWith('/admin')) {
            return !currentPath.startsWith('/admin');
        }

        return currentPath !== panelPath && !currentPath.startsWith(`${panelPath}/`);
    });

    return (
        <div className="student-shell">
            <div className="mx-auto grid min-h-screen max-w-[1440px] lg:grid-cols-[272px_1fr]">
                <aside className="student-shell__sidebar relative flex flex-col lg:min-h-screen">
                    <div className="flex items-center gap-3 border-b border-white/10 px-5 py-5">
                        <div className="student-shell__brand-mark flex size-11 items-center justify-center rounded-2xl">
                            <BookOpen className="size-6 text-sky-100" aria-hidden />
                        </div>
                        <div className="min-w-0">
                            <p className="truncate text-[11px] font-medium tracking-[0.14em] text-sky-200/80 uppercase">
                                Student Hub
                            </p>
                            <Link href="/" className="block truncate text-sm font-semibold text-white hover:text-sky-100">
                                {app.name}
                            </Link>
                        </div>
                    </div>

                    <div className="px-4 py-4">
                        <div className="rounded-2xl border border-white/10 bg-white/5 px-3.5 py-3">
                            <p className="text-[11px] text-sky-100/70">حساب یادگیری</p>
                            <p className="mt-0.5 truncate text-sm font-semibold text-white">{displayName}</p>
                        </div>
                    </div>

                    <nav className="flex flex-1 flex-col gap-1 px-3 pb-4" aria-label="منوی پنل دانشجو">
                        {links.map((link) => {
                            const active = isNavActive(link.href, url);
                            const Icon = link.icon;

                            return (
                                <Link
                                    key={link.href}
                                    href={link.href}
                                    className={cn(
                                        'student-shell__nav-link flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium',
                                        active && 'is-active',
                                    )}
                                    aria-current={active ? 'page' : undefined}
                                >
                                    <span
                                        className={cn(
                                            'flex size-9 items-center justify-center rounded-lg',
                                            active ? 'bg-white/15 text-white' : 'bg-white/5 text-sky-100/80',
                                        )}
                                    >
                                        <Icon className="size-4" aria-hidden />
                                    </span>
                                    <span className="min-w-0">
                                        <span className="block truncate">{link.label}</span>
                                        {link.hint ? (
                                            <span className="block truncate text-[11px] font-normal opacity-70">
                                                {link.hint}
                                            </span>
                                        ) : null}
                                    </span>
                                </Link>
                            );
                        })}
                    </nav>

                    <div className="mt-auto space-y-2 border-t border-white/10 p-4">
                        {otherPanels.length > 0 ? (
                            <div className="space-y-1">
                                <p className="px-1 text-[11px] font-medium tracking-wide text-sky-100/55 uppercase">
                                    پنل‌های دیگر
                                </p>
                                {otherPanels.map((panel) => (
                                    <Link
                                        key={panel.url}
                                        href={panel.url}
                                        className="student-shell__nav-link block rounded-xl px-3 py-2 text-sm"
                                    >
                                        {panel.label}
                                    </Link>
                                ))}
                            </div>
                        ) : null}
                        <Link
                            href="/"
                            className="student-shell__nav-link inline-flex w-full items-center justify-center gap-2 rounded-xl px-3 py-2.5 text-sm font-medium"
                        >
                            <Home className="size-4" aria-hidden />
                            بازگشت به سایت
                        </Link>
                    </div>
                </aside>

                <div className="flex min-w-0 flex-col">
                    <header className="border-b border-slate-200/80 bg-white/80 px-4 py-4 backdrop-blur md:px-8">
                        <div className="flex flex-wrap items-end justify-between gap-3">
                            <div>
                                <p className="text-[11px] font-semibold tracking-[0.16em] text-primary/80 uppercase">
                                    فضای دانشجو
                                </p>
                                <h1 className="mt-1 text-xl font-bold tracking-tight text-slate-900 md:text-2xl">
                                    {title}
                                </h1>
                                {description ? (
                                    <p className="mt-1 max-w-2xl text-sm leading-relaxed text-slate-600">
                                        {description}
                                    </p>
                                ) : null}
                            </div>
                            {!isNavActive('/student/dashboard', url) ? (
                                <Link
                                    href="/student/dashboard"
                                    className="inline-flex items-center gap-1.5 rounded-xl border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition hover:border-primary/30 hover:text-primary"
                                >
                                    <ArrowUpLeft className="size-3.5" aria-hidden />
                                    داشبورد
                                </Link>
                            ) : null}
                        </div>
                    </header>

                    <main className="flex-1 px-4 py-6 md:px-8 md:py-8">
                        <div
                            className={cn(
                                'min-w-0',
                                contentVariant === 'panel' && 'student-shell__main-panel rounded-3xl p-5 md:p-8',
                            )}
                        >
                            {children}
                        </div>
                    </main>
                </div>
            </div>
        </div>
    );
}
