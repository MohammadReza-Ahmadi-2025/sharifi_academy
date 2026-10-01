import type { BreadcrumbItem } from '@/Components/Admin/shared/config/admin-breadcrumbs';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ChevronLeft } from 'lucide-react';

interface BreadcrumbProps {
    items: BreadcrumbItem[];
    className?: string;
}

export function Breadcrumb({ items, className }: BreadcrumbProps) {
    return (
        <nav aria-label="مسیر صفحه" className={cn('flex min-w-0 items-center gap-1.5', className)}>
            {items.map((item, index) => {
                const isLast = index === items.length - 1;

                return (
                    <div key={`${item.label}-${index}`} className="flex min-w-0 items-center gap-1.5">
                        {index > 0 && (
                            <ChevronLeft
                                className="h-3.5 w-3.5 shrink-0 text-slate-300 dark:text-slate-600"
                                aria-hidden="true"
                            />
                        )}

                        {item.href && !isLast ? (
                            <Link
                                href={item.href}
                                className="truncate text-xs font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                            >
                                {item.label}
                            </Link>
                        ) : (
                            <span
                                className={cn(
                                    'truncate text-xs font-semibold',
                                    isLast
                                        ? 'text-slate-900 dark:text-slate-100'
                                        : 'text-slate-500 dark:text-slate-400',
                                )}
                                aria-current={isLast ? 'page' : undefined}
                            >
                                {item.label}
                            </span>
                        )}
                    </div>
                );
            })}
        </nav>
    );
}
