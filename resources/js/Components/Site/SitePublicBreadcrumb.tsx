import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

export type PublicBreadcrumbItem = {
    label: string;
    href?: string;
};

type Props = {
    items: PublicBreadcrumbItem[];
    className?: string;
};

export function SitePublicBreadcrumb({ items, className }: Props) {
    if (items.length === 0) {
        return null;
    }

    return (
        <nav className={cn('flex flex-wrap items-center gap-2 text-sm text-neutral-black/55', className)} aria-label="مسیر">
            {items.map((item, index) => {
                const isLast = index === items.length - 1;

                return (
                    <span key={`${item.label}-${index}`} className="inline-flex items-center gap-2">
                        {item.href && !isLast ? (
                            <Link href={item.href} className="transition hover:text-primary">
                                {item.label}
                            </Link>
                        ) : (
                            <span className={isLast ? 'font-medium text-neutral-black/75' : undefined}>{item.label}</span>
                        )}
                        {!isLast ? <span className="text-neutral-black/30" aria-hidden>/</span> : null}
                    </span>
                );
            })}
        </nav>
    );
}
