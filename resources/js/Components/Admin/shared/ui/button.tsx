import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import * as React from 'react';

type ButtonVariant = 'default' | 'outline' | 'ghost';

type AdminButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: ButtonVariant;
    href?: string;
};

const variantStyles: Record<ButtonVariant, string> = {
    default:
        'bg-slate-900 text-white hover:bg-slate-800 hover:text-white dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200 dark:hover:text-slate-900',
    outline:
        'border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900 dark:hover:text-slate-100',
    ghost:
        'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-300',
};

const baseStyles =
    'flex h-10 items-center justify-center rounded-md px-4 py-1 text-sm font-medium transition-colors no-underline whitespace-nowrap shrink-0';

export function AdminButton({ className, type = 'button', variant = 'default', href, ...props }: AdminButtonProps) {
    const classes = cn(baseStyles, variantStyles[variant], className);

    if (href) {
        return (
            <Link href={href} className={classes}>
                {props.children}
            </Link>
        );
    }

    return <button className={classes} {...props} type={type} />;
}
