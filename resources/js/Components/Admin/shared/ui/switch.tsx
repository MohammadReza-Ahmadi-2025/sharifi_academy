import * as React from 'react';
import { cn } from '@/lib/utils';

export interface SwitchProps {
    checked: boolean;
    onCheckedChange: (checked: boolean) => void;
    disabled?: boolean;
    id?: string;
    label?: string;
    description?: string;
    className?: string;
}

export function Switch({
    checked,
    onCheckedChange,
    disabled = false,
    id,
    label,
    description,
    className,
}: SwitchProps) {
    const generatedId = React.useId();
    const switchId = id ?? generatedId;

    return (
        <div className={cn('flex items-start justify-between gap-3', className)}>
            {(label || description) && (
                <div className="min-w-0 space-y-0.5">
                    {label && (
                        <label
                            htmlFor={switchId}
                            className={cn(
                                'block text-sm font-medium text-slate-700 dark:text-slate-300',
                                disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer',
                            )}
                        >
                            {label}
                        </label>
                    )}
                    {description && (
                        <p className="text-xs text-slate-500 dark:text-slate-400">{description}</p>
                    )}
                </div>
            )}
            <button
                id={switchId}
                type="button"
                role="switch"
                aria-checked={checked}
                disabled={disabled}
                onClick={() => !disabled && onCheckedChange(!checked)}
                className={cn(
                    'relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors',
                    'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 focus-visible:ring-offset-2',
                    'disabled:cursor-not-allowed disabled:opacity-50',
                    'dark:focus-visible:ring-slate-600 dark:focus-visible:ring-offset-slate-950',
                    checked ? 'bg-slate-900 dark:bg-slate-100' : 'bg-slate-200 dark:bg-slate-700',
                )}
            >
                <span
                    className={cn(
                        'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition',
                        'dark:bg-slate-900',
                        checked ? 'translate-x-5 rtl:-translate-x-5' : 'translate-x-0',
                    )}
                />
            </button>
        </div>
    );
}
