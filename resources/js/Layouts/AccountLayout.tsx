import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type NavLink = {
    href: string;
    label: string;
    match?: (path: string) => boolean;
};

type Props = PropsWithChildren<{
    title: string;
    description?: string;
}>;

export default function AccountLayout({ title, description, children }: Props) {
    const { url, auth } = usePage<PageProps>().props;
    const pathname = url.replace(/^https?:\/\/[^/]+/, '');

    const panels = auth.account?.panels?.length
        ? auth.account.panels
        : auth.account?.panel
          ? [auth.account.panel]
          : [];

    const links: NavLink[] = [
        { href: '/profile', label: 'پروفایل من', match: (p) => p.startsWith('/profile') },
        ...panels.map((panel) => ({
            href: panel.url,
            label: panel.label.replace(/^ورود به\s+/, ''),
            match: (p: string) => {
                const base = panel.url.split('?')[0];
                if (base === '/admin') {
                    return p.startsWith('/admin');
                }
                if (base.startsWith('/instructor')) {
                    return p.startsWith('/instructor');
                }
                if (base.startsWith('/student')) {
                    return p.startsWith('/student');
                }

                return p === base || p.startsWith(`${base}/`);
            },
        })),
    ];

    return (
        <AppLayout>
            <div className="mb-8">
                <h1 className="text-2xl font-bold text-text">{title}</h1>
                {description ? <p className="mt-2 text-sm text-neutral-600">{description}</p> : null}
            </div>

            <div className="grid gap-8 lg:grid-cols-[minmax(0,240px)_1fr]">
                <aside className="h-fit rounded-2xl border border-secondary bg-surface p-3 shadow-sm">
                    <p className="px-3 py-2 text-xs font-semibold tracking-wide text-neutral-500">حساب کاربری</p>
                    <nav className="mt-1 flex flex-col gap-1">
                        {links.map((item) => {
                            const active = item.match ? item.match(pathname) : pathname === item.href;

                            return (
                                <Link
                                    key={item.href}
                                    href={item.href}
                                    className={`rounded-xl px-3 py-2.5 text-sm font-medium transition-colors ${
                                        active
                                            ? 'bg-primary text-on-primary'
                                            : 'text-text hover:bg-secondary/60'
                                    }`}
                                >
                                    {item.label}
                                </Link>
                            );
                        })}
                    </nav>
                </aside>

                <div className="min-w-0 space-y-6">{children}</div>
            </div>
        </AppLayout>
    );
}
