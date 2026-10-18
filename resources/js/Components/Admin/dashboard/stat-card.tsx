import { Card } from '@/Components/Admin/shared/ui/card';
import { cn } from '@/lib/utils';

export type StatCardProps = {
    title: string;
    value: string;
    description: string;
    trend?: string | null;
    compact?: boolean;
    className?: string;
};

export function StatCard({ title, value, description, trend, compact = false, className }: StatCardProps) {
    const trendPositive = trend != null && !trend.startsWith('-') && trend !== '۰٪';

    if (compact) {
        return (
            <div
                className={cn(
                    'flex h-full flex-col justify-between rounded-xl border border-slate-200/90 bg-slate-50/50 p-3.5 transition-colors hover:border-primary/25 hover:bg-white dark:border-slate-800 dark:bg-slate-950/40 dark:hover:border-primary/30 dark:hover:bg-slate-900',
                    className,
                )}
            >
                <p className="text-xs font-medium text-slate-500 dark:text-slate-400">{title}</p>
                <p className="mt-2 text-xl font-bold tabular-nums tracking-tight text-slate-900 dark:text-slate-50">
                    {value}
                </p>
                <p className="mt-1.5 line-clamp-1 text-[11px] text-slate-400 dark:text-slate-500">{description}</p>
            </div>
        );
    }

    return (
        <Card className={className}>
            <div className="flex items-start justify-between gap-4">
                <div className="space-y-1">
                    <p className="text-sm font-medium text-slate-500 dark:text-slate-400">{title}</p>
                    <h3 className="text-2xl font-semibold tracking-tight text-slate-900 dark:text-slate-100">{value}</h3>
                </div>

                {trend ? (
                    <span
                        className={`inline-flex items-center rounded-md px-2 py-1 text-xs font-medium ${
                            trendPositive
                                ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400'
                                : 'bg-rose-50 text-rose-700 dark:bg-rose-500/10 dark:text-rose-400'
                        }`}
                    >
                        {trend}
                    </span>
                ) : null}
            </div>

            <p className="mt-4 text-sm text-slate-500 dark:text-slate-400">{description}</p>
        </Card>
    );
}
