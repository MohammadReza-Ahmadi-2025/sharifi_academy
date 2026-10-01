import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import { ArrowRight } from 'lucide-react';
import type { ReactNode } from 'react';

interface PageHeaderProps {
    title: string;
    description?: string;
    backHref?: string;
    backLabel?: string;
    actions?: ReactNode;
    className?: string;
}

export function PageHeader({
    title,
    description,
    backHref,
    backLabel = 'بازگشت',
    actions,
    className,
}: PageHeaderProps) {
    return (
        <div className={cn('space-y-4', className)}>
            {backHref && (
                <Link
                    href={backHref}
                    className="inline-flex items-center gap-1.5 text-sm font-medium text-slate-500 transition-colors hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-100"
                >
                    <ArrowRight className="h-4 w-4" />
                    {backLabel}
                </Link>
            )}

            <div className="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h1 className="text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-100">{title}</h1>
                    {description && <p className="mt-1 text-sm text-slate-500 dark:text-slate-400">{description}</p>}
                </div>

                {actions}
            </div>
        </div>
    );
}
