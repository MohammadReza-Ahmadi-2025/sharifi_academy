import { useSystemHealth, type SystemHealthState } from '@/Components/Admin/shared/hooks/use-system-health';
import { cn } from '@/lib/utils';

const statusConfig = {
    loading: {
        label: 'در حال بررسی...',
        description: 'اتصال به سرور',
        badge: 'بررسی',
        dot: 'bg-slate-400 animate-pulse dark:bg-slate-500',
        badgeBg: 'bg-slate-100 text-slate-600 dark:bg-slate-800 dark:text-slate-400',
    },
    online: {
        label: 'در حال سرویس‌دهی',
        description: 'سامانه در دسترس است',
        badge: 'فعال',
        dot: 'bg-emerald-500 dark:bg-emerald-400',
        badgeBg: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
    },
    offline: {
        label: 'عدم دسترسی',
        description: 'سرور پاسخ نمی‌دهد',
        badge: 'قطع',
        dot: 'bg-rose-500 dark:bg-rose-400',
        badgeBg: 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400',
    },
} as const satisfies Record<
    SystemHealthState,
    {
        label: string;
        description: string;
        badge: string;
        dot: string;
        badgeBg: string;
    }
>;

type SystemHealthStatusProps = {
    variant?: 'card' | 'inline';
    className?: string;
};

export function SystemHealthStatus({ variant = 'card', className }: SystemHealthStatusProps) {
    const { state } = useSystemHealth();
    const config = statusConfig[state];

    if (variant === 'inline') {
        return (
            <div
                className={cn(
                    'flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400',
                    className,
                )}
            >
                <span className={cn('h-1.5 w-1.5 rounded-full', config.dot)} />
                <span>{config.label}</span>
            </div>
        );
    }

    return (
        <div
            className={cn(
                'rounded-lg border border-slate-200 bg-white px-3 py-3 dark:border-slate-800 dark:bg-slate-900',
                className,
            )}
        >
            <div className="flex items-center justify-between gap-3">
                <div>
                    <p className="text-xs font-medium text-slate-500 dark:text-slate-400">وضعیت سامانه</p>
                    <p className="mt-1 text-xs font-medium text-slate-800 dark:text-slate-200">{config.label}</p>
                    <p className="mt-0.5 text-[10px] text-slate-400 dark:text-slate-500">{config.description}</p>
                </div>

                <span
                    className={cn(
                        'inline-flex shrink-0 items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-medium',
                        config.badgeBg,
                    )}
                >
                    <span className={cn('h-1.5 w-1.5 rounded-full', config.dot)} />
                    {config.badge}
                </span>
            </div>
        </div>
    );
}
