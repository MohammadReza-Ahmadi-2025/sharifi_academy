import { cn } from '@/lib/utils';
import type { SelectHTMLAttributes } from 'react';

export type AdminFilterSelectOption = {
    value: string;
    label: string;
};

type Props = Omit<SelectHTMLAttributes<HTMLSelectElement>, 'children'> & {
    options: AdminFilterSelectOption[];
    placeholder?: string;
};

export function AdminFilterSelect({ options, placeholder, className, value, ...props }: Props) {
    return (
        <select
            {...props}
            value={value}
            className={cn(
                'flex h-10 min-w-[9rem] rounded-md border border-slate-300 bg-white px-3 text-sm text-slate-800 dark:border-slate-700 dark:bg-slate-950 dark:text-slate-100',
                className,
            )}
        >
            {placeholder ? <option value="">{placeholder}</option> : null}
            {options.map((option) => (
                <option key={option.value} value={option.value}>
                    {option.label}
                </option>
            ))}
        </select>
    );
}
