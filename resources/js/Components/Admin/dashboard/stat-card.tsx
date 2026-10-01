import { Card } from '@/Components/Admin/shared/ui/card';

export type StatCardProps = {
    title: string;
    value: string;
    description: string;
    trend?: string | null;
};

export function StatCard({ title, value, description, trend }: StatCardProps) {
    const trendPositive = trend != null && !trend.startsWith('-') && trend !== '۰٪';

    return (
        <Card>
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
