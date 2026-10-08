import { cn } from '@/lib/utils';
import type { FormEvent, ReactNode } from 'react';

type AdminFormPageProps = {
    onSubmit: (event: FormEvent<HTMLFormElement>) => void;
    children: ReactNode;
    className?: string;
};

/** فرم تمام‌عرض ادمین با فاصله‌گذاری یکسان */
export function AdminFormPage({ onSubmit, children, className }: AdminFormPageProps) {
    return (
        <form onSubmit={onSubmit} className={cn('mt-6 flex w-full min-w-0 flex-col gap-6', className)}>
            {children}
        </form>
    );
}

type AdminFormActionsProps = {
    children: ReactNode;
    className?: string;
};

/** نوار عملیات چسبیده به پایین (ذخیره / انصراف) */
export function AdminFormActions({ children, className }: AdminFormActionsProps) {
    return (
        <div
            className={cn(
                'sticky bottom-0 z-10 -mx-6 flex flex-wrap items-center gap-3 border-t border-slate-200 bg-gray-50/95 px-6 py-4 backdrop-blur dark:border-slate-800 dark:bg-slate-950/95',
                className,
            )}
        >
            {children}
        </div>
    );
}
