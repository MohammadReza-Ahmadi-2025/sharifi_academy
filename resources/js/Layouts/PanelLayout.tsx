import { PageProps } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

type Props = PropsWithChildren<{
    title: string;
    homeHref: string;
    links: { href: string; label: string }[];
}>;

export default function PanelLayout({ title, homeHref, links, children }: Props) {
    const { app } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-zinc-100">
            <div className="border-b border-zinc-200 bg-white">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-4">
                    <div>
                        <Link href="/" className="text-sm text-zinc-500 hover:text-indigo-600">
                            {app.name}
                        </Link>
                        <h1 className="text-xl font-semibold">{title}</h1>
                    </div>
                    <Link href={homeHref} className="text-sm text-indigo-600 hover:underline">
                        بازگشت
                    </Link>
                </div>
            </div>
            <div className="mx-auto grid max-w-6xl gap-6 px-4 py-6 md:grid-cols-[220px_1fr]">
                <aside className="rounded-xl border border-zinc-200 bg-white p-4">
                    <nav className="flex flex-col gap-2 text-sm">
                        {links.map((link) => (
                            <Link key={link.href} href={link.href} className="rounded-lg px-3 py-2 hover:bg-zinc-50">
                                {link.label}
                            </Link>
                        ))}
                    </nav>
                </aside>
                <section className="rounded-xl border border-zinc-200 bg-white p-6">{children}</section>
            </div>
        </div>
    );
}
