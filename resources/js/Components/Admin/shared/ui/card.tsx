import { cn } from '@/lib/utils';
import { type HTMLAttributes } from 'react';

type CardProps = HTMLAttributes<HTMLDivElement>;

export function Card({ className, children, ...props }: CardProps) {
    return (
        <div
            className={cn(
                'rounded-xl border border-slate-200 bg-white p-6 shadow-sm transition-colors',
                'dark:border-slate-800 dark:bg-slate-900 dark:shadow-none',
                className,
            )}
            {...props}
        >
            {children}
        </div>
    );
}
