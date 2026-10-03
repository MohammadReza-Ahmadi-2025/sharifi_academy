import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';
import * as React from 'react';

type ButtonVariant = 'default' | 'outline' | 'ghost' | 'secondary';

type AdminButtonProps = React.ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: ButtonVariant;
    size?: 'default' | 'sm';
    href?: string;
};

const variantStyles: Record<ButtonVariant, string> = {
    default:
        'bg-slate-900 text-white hover:bg-slate-800 hover:text-white dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-200 dark:hover:text-slate-900',
    outline:
        'border border-slate-200 bg-white text-slate-900 hover:bg-slate-50 hover:text-slate-900 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100 dark:hover:bg-slate-900 dark:hover:text-slate-100',
    ghost:
        'bg-transparent text-slate-600 hover:bg-slate-100 hover:text-slate-700 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-300',
    secondary:
        'border border-slate-200 bg-slate-50 text-slate-900 hover:bg-slate-100 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-100 dark:hover:bg-slate-800',
};

const sizeStyles = {
    default: 'h-10 px-4 py-1 text-sm',
    sm: 'h-8 px-3 text-xs',
};

const baseStyles =
    'inline-flex items-center justify-center rounded-md font-medium transition-colors no-underline whitespace-nowrap shrink-0';

export function AdminButton({
    className,
    type = 'button',
    variant = 'default',
    size = 'default',
    href,
    ...props
}: AdminButtonProps) {
    const classes = cn(baseStyles, sizeStyles[size], variantStyles[variant], className);

    if (href) {
        return (
            <Link href={href} className={classes}>
                {props.children}
            </Link>
        );
    }

    return <button className={classes} {...props} type={type} />;
}
