import { AdminInput, type AdminInputProps } from '@/Components/Admin/shared/ui/input';
import { cn } from '@/lib/utils';
import { Eye, EyeOff } from 'lucide-react';
import * as React from 'react';

export const PasswordInput = React.forwardRef<HTMLInputElement, AdminInputProps>(
    ({ className, disabled, ...props }, ref) => {
        const [visible, setVisible] = React.useState(false);

        return (
            <div className="relative">
                <AdminInput
                    {...props}
                    ref={ref}
                    type={visible ? 'text' : 'password'}
                    disabled={disabled}
                    className={cn('pe-3 ps-10', className)}
                />

                <button
                    type="button"
                    tabIndex={-1}
                    disabled={disabled}
                    aria-label={visible ? 'مخفی کردن رمز عبور' : 'نمایش رمز عبور'}
                    aria-pressed={visible}
                    onClick={() => setVisible((current) => !current)}
                    className="absolute inset-y-0 start-0 flex w-10 items-center justify-center rounded-s-md text-slate-400 transition-colors hover:text-slate-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-400 disabled:opacity-50 dark:text-slate-500 dark:hover:text-slate-200 dark:focus-visible:ring-slate-600"
                >
                    {visible ? <EyeOff className="h-4 w-4" aria-hidden="true" /> : <Eye className="h-4 w-4" aria-hidden="true" />}
                </button>
            </div>
        );
    },
);

PasswordInput.displayName = 'PasswordInput';
