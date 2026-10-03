import { cn } from '@/lib/utils';
import { useToast } from '@/Components/shared/contexts/toast-context';
import type { ToastVariant } from '@/types/toast';
import { AlertTriangle, CheckCircle2, Info, X, XCircle } from 'lucide-react';

const variantStyles: Record<
    ToastVariant,
    {
        container: string;
        iconWrap: string;
        icon: string;
        progress: string;
        Icon: typeof CheckCircle2;
    }
> = {
    success: {
        container:
            'border-emerald-200/80 bg-white/95 dark:border-emerald-500/25 dark:bg-slate-900/95',
        iconWrap: 'bg-emerald-50 dark:bg-emerald-500/15',
        icon: 'text-emerald-600 dark:text-emerald-400',
        progress: 'bg-emerald-500/80',
        Icon: CheckCircle2,
    },
    error: {
        container:
            'border-rose-200/80 bg-white/95 dark:border-rose-500/25 dark:bg-slate-900/95',
        iconWrap: 'bg-rose-50 dark:bg-rose-500/15',
        icon: 'text-rose-600 dark:text-rose-400',
        progress: 'bg-rose-500/80',
        Icon: XCircle,
    },
    info: {
        container: 'border-sky-200/80 bg-white/95 dark:border-sky-500/25 dark:bg-slate-900/95',
        iconWrap: 'bg-sky-50 dark:bg-sky-500/15',
        icon: 'text-sky-600 dark:text-sky-400',
        progress: 'bg-sky-500/80',
        Icon: Info,
    },
    warning: {
        container:
            'border-amber-200/80 bg-white/95 dark:border-amber-500/25 dark:bg-slate-900/95',
        iconWrap: 'bg-amber-50 dark:bg-amber-500/15',
        icon: 'text-amber-600 dark:text-amber-400',
        progress: 'bg-amber-500/80',
        Icon: AlertTriangle,
    },
};

export function Toaster() {
    const { toasts, dismiss } = useToast();

    return (
        <div
            aria-live="polite"
            aria-atomic="true"
            className="pointer-events-none fixed inset-x-0 top-[12vh] z-[110] flex justify-center px-4 sm:top-[14vh]"
        >
            <div className="flex w-full max-w-md flex-col gap-3">
                {toasts.map((toast) => {
                    const styles = variantStyles[toast.variant];
                    const Icon = styles.Icon;

                    return (
                        <div
                            key={toast.id}
                            role="status"
                            className={cn(
                                'pointer-events-auto overflow-hidden rounded-2xl border shadow-[0_18px_50px_-20px_rgba(15,23,42,0.45)] backdrop-blur-xl dark:shadow-[0_18px_50px_-18px_rgba(0,0,0,0.65)]',
                                styles.container,
                                toast.exiting ? 'toast-exit' : 'toast-enter',
                            )}
                        >
                            <div className="flex items-start gap-3 px-4 py-3.5">
                                <div
                                    className={cn(
                                        'toast-content flex h-9 w-9 shrink-0 items-center justify-center rounded-xl',
                                        styles.iconWrap,
                                    )}
                                >
                                    <Icon className={cn('h-5 w-5', styles.icon)} />
                                </div>

                                <div className="toast-content min-w-0 flex-1 pt-0.5">
                                    <p className="text-sm font-semibold tracking-tight text-slate-900 dark:text-slate-50">
                                        {toast.title}
                                    </p>
                                    {toast.description ? (
                                        <p className="mt-1 whitespace-pre-line text-[13px] leading-6 text-slate-600 dark:text-slate-300">
                                            {toast.description}
                                        </p>
                                    ) : null}
                                </div>

                                <button
                                    type="button"
                                    onClick={() => dismiss(toast.id)}
                                    aria-label="بستن اعلان"
                                    className="toast-content rounded-lg p-1.5 text-slate-400 transition-colors hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                                >
                                    <X className="h-4 w-4" />
                                </button>
                            </div>

                            <div className="h-0.5 w-full bg-slate-100/80 dark:bg-slate-800/80">
                                <div className={cn('toast-progress h-full origin-right', styles.progress)} />
                            </div>
                        </div>
                    );
                })}
            </div>
        </div>
    );
}
