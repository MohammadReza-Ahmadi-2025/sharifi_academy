import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import type { LucideIcon } from 'lucide-react';
import { ChevronLeft } from 'lucide-react';
import type { ReactNode } from 'react';

type Props = {
    id?: string;
    title?: string;
    viewAllHref?: string | null;
    icon: LucideIcon;
    children: ReactNode;
};

export function HomeSectionShell({ id, title, viewAllHref, icon: Icon, children }: Props) {
    const showHeader = Boolean(title?.trim()) || viewAllHref;

    return (
        <section id={id} className="scroll-mt-24">
            <div className="mx-auto max-w-7xl px-4 py-9 md:py-11">
                {showHeader && (
                    <header className="mb-6 flex flex-col gap-4 sm:mb-7 sm:flex-row sm:items-center sm:justify-between">
                        <div className="flex min-w-0 items-center gap-3">
                            <span
                                className="mt-0.5 flex size-10 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-primary/12 to-primary/[0.04] text-primary ring-1 ring-primary/10"
                                aria-hidden
                            >
                                <Icon className="size-[1.15rem]" strokeWidth={2.1} />
                            </span>
                            <div className="min-w-0 border-r-[3px] border-primary/85 pr-3.5">
                                {title?.trim() ? (
                                    <h2 className="text-lg font-bold leading-snug tracking-tight text-neutral-black md:text-xl">
                                        {title}
                                    </h2>
                                ) : null}
                            </div>
                        </div>
                        {viewAllHref ? (
                            <Link
                                href={viewAllHref}
                                className={cn(
                                    'group inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-primary transition',
                                    'hover:text-primary-hover',
                                )}
                            >
                                <span className="border-b border-primary/25 pb-0.5 transition group-hover:border-primary/50">
                                    مشاهده همه
                                </span>
                                <ChevronLeft
                                    className="size-4 transition group-hover:-translate-x-0.5"
                                    aria-hidden
                                />
                            </Link>
                        ) : null}
                    </header>
                )}
                {children}
            </div>
        </section>
    );
}
