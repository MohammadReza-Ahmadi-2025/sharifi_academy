import { StatCard } from '@/Components/Admin/dashboard/stat-card';

export type DashboardStat = {
    id: string;
    label: string;
    value: number;
};

type StatsGridProps = {
    stats: DashboardStat[];
};

export function StatsGrid({ stats }: StatsGridProps) {
    if (stats.length === 0) {
        return null;
    }

    return (
        <section className="grid grid-cols-1 gap-4 md:grid-cols-2 2xl:grid-cols-4">
            {stats.map((stat) => (
                <StatCard
                    key={stat.id}
                    title={stat.label}
                    value={stat.value.toLocaleString('fa-IR')}
                    description="آمار لحظه‌ای از پایگاه داده آکادمی"
                />
            ))}
        </section>
    );
}
