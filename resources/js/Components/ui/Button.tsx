import { cn } from '@/lib/utils';
import { ButtonHTMLAttributes } from 'react';

type Props = ButtonHTMLAttributes<HTMLButtonElement> & {
    variant?: 'primary' | 'secondary' | 'ghost';
};

export function Button({ className, variant = 'primary', ...props }: Props) {
    return (
        <button
            className={cn(
                'inline-flex items-center justify-center rounded-lg px-4 py-2 text-sm font-medium transition',
                variant === 'primary' && 'bg-indigo-600 text-white hover:bg-indigo-700',
                variant === 'secondary' && 'border border-zinc-300 bg-white hover:bg-zinc-50',
                variant === 'ghost' && 'text-zinc-700 hover:bg-zinc-100',
                className,
            )}
            {...props}
        />
    );
}
