import { cn } from '@/lib/utils';
import * as React from 'react';

export type AdminInputProps = React.InputHTMLAttributes<HTMLInputElement>;

const AdminInput = React.forwardRef<HTMLInputElement, AdminInputProps>(({ className, type, ...props }, ref) => {
    return (
        <input
            type={type}
            className={cn(
                'flex h-10 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none transition-colors placeholder:text-slate-400 focus:border-slate-900',
                'dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-slate-200',
                className,
            )}
            ref={ref}
            {...props}
        />
    );
});

AdminInput.displayName = 'AdminInput';

export { AdminInput };
