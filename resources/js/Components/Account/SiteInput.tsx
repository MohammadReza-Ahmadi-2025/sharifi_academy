import { cn } from '@/lib/utils';
import * as React from 'react';

export type SiteInputProps = React.InputHTMLAttributes<HTMLInputElement>;

export const SiteInput = React.forwardRef<HTMLInputElement, SiteInputProps>(({ className, ...props }, ref) => (
    <input
        ref={ref}
        className={cn(
            'flex h-11 w-full rounded-xl border border-secondary bg-surface px-3 text-sm text-text outline-none transition-colors placeholder:text-neutral-400 focus:border-primary focus:ring-2 focus:ring-primary/20',
            className,
        )}
        {...props}
    />
));

SiteInput.displayName = 'SiteInput';
