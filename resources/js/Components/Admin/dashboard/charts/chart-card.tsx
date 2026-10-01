import { Card } from '@/Components/Admin/shared/ui/card';
import { cn } from '@/lib/utils';
import type { ReactNode } from 'react';

type ChartCardProps = {
    title: string;
    description?: string;
    action?: ReactNode;
    footer?: ReactNode;
    children: ReactNode;
    className?: string;
    contentClassName?: string;
};

export function ChartCard({
    title,
    description,
    action,
    footer,
    children,
    className,
    contentClassName,
}: ChartCardProps) {
    return (
        <Card className={cn(className)}>
            <div className="flex items-start justify-between gap-4 border-b border-slate-200 p-5 dark:border-slate-800">
                <div className="space-y-1">
                    <h3 className="text-sm font-semibold text-slate-900 dark:text-slate-100">{title}</h3>
                    {description ? <p className="text-xs text-slate-500 dark:text-slate-400">{description}</p> : null}
                </div>
                {action ? <div className="shrink-0">{action}</div> : null}
            </div>

            <div className={cn('p-5', contentClassName)}>{children}</div>

            {footer ? <div className="border-t border-slate-200 px-5 py-4 dark:border-slate-800">{footer}</div> : null}
        </Card>
    );
}
