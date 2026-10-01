import { cn } from '@/lib/utils';

type ChartTooltipEntry = {
    dataKey?: string | number;
    name?: string;
    value?: number | string;
    color?: string;
};

type ChartTooltipProps = {
    active?: boolean;
    payload?: ChartTooltipEntry[];
    label?: string | number;
    formatter?: (value: number) => string;
};

export function ChartTooltip({ active, payload, label, formatter }: ChartTooltipProps) {
    if (!active || !payload?.length) {
        return null;
    }

    return (
        <div className="min-w-45 rounded-xl border border-slate-200 bg-white px-3 py-2 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div className="mb-2 text-xs font-medium text-slate-500 dark:text-slate-400">{label}</div>

            <div className="space-y-1.5">
                {payload.map((entry) => (
                    <div key={entry.dataKey} className="flex items-center justify-between gap-3">
                        <div className="flex items-center gap-2">
                            <span className="h-2.5 w-2.5 rounded-full" style={{ backgroundColor: entry.color }} />
                            <span className="text-xs text-slate-600 dark:text-slate-300">{entry.name}</span>
                        </div>

                        <span className={cn('text-xs font-medium text-slate-900 dark:text-slate-100')}>
                            {formatter && typeof entry.value === 'number' ? formatter(entry.value) : entry.value}
                        </span>
                    </div>
                ))}
            </div>
        </div>
    );
}
