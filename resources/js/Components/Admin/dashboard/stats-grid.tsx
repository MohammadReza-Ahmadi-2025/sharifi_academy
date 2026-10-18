import { StatCard } from '@/Components/Admin/dashboard/stat-card';
import { Card } from '@/Components/Admin/shared/ui/card';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/react';

export type DashboardStat = {
    id: string;
    label: string;
    value: number;
    description: string;
    href?: string | null;
};

export type DashboardStatGroup = {
    id: string;
    title: string;
    stats: DashboardStat[];
};

const groupAccent: Record<string, string> = {
    people: 'border-t-sky-500',
    academy: 'border-t-emerald-500',
    content: 'border-t-violet-500',
    traffic: 'border-t-amber-500',
    moderation: 'border-t-orange-500',
};

type StatsGridProps = {
    groups: DashboardStatGroup[];
};

export function StatsGrid({ groups }: StatsGridProps) {
    if (groups.length === 0) {
        return null;
    }

    return (
        <div className="grid gap-4 md:grid-cols-2">
            {groups.map((group) => {
                const cols = group.stats.length >= 5 ? 'sm:grid-cols-3' : 'sm:grid-cols-2';

                return (
                    <Card
                        key={group.id}
                        className={cn(
                            'overflow-hidden border-t-[3px] p-0 shadow-sm',
                            groupAccent[group.id] ?? 'border-t-slate-300',
                            group.id === 'content' && 'md:col-span-2',
                        )}
                    >
                        <div className="border-b border-slate-100 px-4 py-3 dark:border-slate-800">
                            <h2 className="text-sm font-semibold text-slate-800 dark:text-slate-100">{group.title}</h2>
                        </div>
                        <div className={cn('grid grid-cols-1 gap-2.5 p-4', cols)}>
                            {group.stats.map((stat) => {
                                const tile = (
                                    <StatCard
                                        compact
                                        title={stat.label}
                                        value={stat.value.toLocaleString('fa-IR')}
                                        description={stat.description}
                                    />
                                );

                                if (stat.href) {
                                    return (
                                        <Link key={stat.id} href={stat.href} className="block h-full min-h-0">
                                            {tile}
                                        </Link>
                                    );
                                }

                                return <div key={stat.id} className="h-full">{tile}</div>;
                            })}
                        </div>
                    </Card>
                );
            })}
        </div>
    );
}
